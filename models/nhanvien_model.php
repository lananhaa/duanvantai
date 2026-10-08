<?php
require_once 'config/database.php';

class EmployeeModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '', $status = '') {
        $query = "SELECT nv.MaNhanVien, nv.MaTaiKhoan, nv.HoTen, nv.SoDienThoai,
                         nv.Email, nv.ChucVu, nv.TrangThai, t.TenDangNhap, t.TrangThai AS TrangThaiTaiKhoan
                  FROM NhanVien nv INNER JOIN TaiKhoan t ON t.MaTaiKhoan = nv.MaTaiKhoan
                  WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= ' AND (nv.HoTen LIKE :keyword OR nv.SoDienThoai LIKE :keyword OR nv.Email LIKE :keyword OR nv.ChucVu LIKE :keyword OR t.TenDangNhap LIKE :keyword)';
            $params[':keyword'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $query .= ' AND nv.TrangThai = :status';
            $params[':status'] = $status;
        }
        $query .= ' ORDER BY nv.MaNhanVien DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getById($id) {
        $stmt = $this->conn->prepare('SELECT nv.MaTaiKhoan, nv.TrangThai, t.TenDangNhap FROM NhanVien nv INNER JOIN TaiKhoan t ON t.MaTaiKhoan = nv.MaTaiKhoan WHERE nv.MaNhanVien = :id');
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function usernameExists($username, $accountId = 0) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM TaiKhoan WHERE TenDangNhap = :username AND MaTaiKhoan <> :id');
        $stmt->execute([':username' => $username, ':id' => (int)$accountId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function save($id, $data) {
        $employee = $id > 0 ? $this->getById($id) : null;
        if ($id > 0 && !$employee) {
            return ['success' => false, 'message' => 'Không tìm thấy nhân viên.'];
        }
        if ($this->usernameExists($data['username'], $employee['MaTaiKhoan'] ?? 0)) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng.'];
        }
        if (!in_array($data['status'], ['Dang lam viec', 'Nghi viec'], true)) {
            return ['success' => false, 'message' => 'Trạng thái nhân viên không hợp lệ.'];
        }

        try {
            $this->conn->beginTransaction();
            if ($id > 0 && $data['status'] === 'Nghi viec') {
                $activeJobs = $this->conn->prepare(
                    "SELECT COUNT(*) FROM PhanCong pc
                     INNER JOIN DonHang d ON d.MaDonHang = pc.MaDonHang
                     WHERE pc.MaNhanVien = :employee_id
                       AND d.TrangThai NOT IN ('Hoan tat', 'Da huy', 'Hoan hang')"
                );
                $activeJobs->execute([':employee_id' => (int)$id]);
                if ((int)$activeJobs->fetchColumn() > 0) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Nhân viên còn đơn đang xử lý; hãy bàn giao trước khi chuyển nghỉ việc.'];
                }
            }
            if ($id === 0) {
                $accountStatus = $data['status'] === 'Dang lam viec' ? 'Hoat dong' : 'Da khoa';
                $account = $this->conn->prepare('INSERT INTO TaiKhoan (MaVaiTro, TenDangNhap, MatKhau, TrangThai) VALUES (2, :username, :password, :status)');
                $account->execute([
                    ':username' => $data['username'],
                    ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                    ':status' => $accountStatus,
                ]);
                $accountId = (int)$this->conn->lastInsertId();
                $employeeStmt = $this->conn->prepare('INSERT INTO NhanVien (MaTaiKhoan, HoTen, SoDienThoai, Email, ChucVu, TrangThai) VALUES (:account_id, :name, :phone, :email, :position, :status)');
                $employeeStmt->execute([
                    ':account_id' => $accountId, ':name' => $data['name'], ':phone' => $data['phone'],
                    ':email' => $data['email'], ':position' => $data['position'], ':status' => $data['status'],
                ]);
            } else {
                $accountQuery = 'UPDATE TaiKhoan SET TenDangNhap = :username';
                $accountData = [':username' => $data['username'], ':id' => (int)$employee['MaTaiKhoan']];
                if ($data['password'] !== '') {
                    $accountQuery .= ', MatKhau = :password';
                    $accountData[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                }
                $accountQuery .= ', TrangThai = :account_status';
                $accountData[':account_status'] = $data['status'] === 'Dang lam viec' ? 'Hoat dong' : 'Da khoa';
                $this->conn->prepare($accountQuery . ' WHERE MaTaiKhoan = :id')->execute($accountData);
                $employeeStmt = $this->conn->prepare('UPDATE NhanVien SET HoTen = :name, SoDienThoai = :phone, Email = :email, ChucVu = :position, TrangThai = :status WHERE MaNhanVien = :id');
                $employeeStmt->execute([
                    ':name' => $data['name'], ':phone' => $data['phone'], ':email' => $data['email'],
                    ':position' => $data['position'], ':status' => $data['status'], ':id' => (int)$id,
                ]);
            }
            $this->conn->commit();
            return ['success' => true, 'message' => $id > 0 ? 'Cập nhật nhân viên thành công.' : 'Thêm nhân viên thành công.'];
        } catch (PDOException $exception) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể lưu nhân viên. Kiểm tra lại dữ liệu.'];
        }
    }

    public function delete($id, $actorAccountId) {
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('SELECT MaTaiKhoan FROM NhanVien WHERE MaNhanVien = :id FOR UPDATE');
            $stmt->execute([':id' => (int)$id]);
            $accountId = $stmt->fetchColumn();
            if (!$accountId) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy nhân viên.'];
            }
            if ((int)$accountId === (int)$actorAccountId) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không thể xóa hồ sơ đang gắn với tài khoản hiện tại.'];
            }

            $activeJobs = $this->conn->prepare(
                "SELECT COUNT(*) FROM PhanCong pc
                 INNER JOIN DonHang d ON d.MaDonHang = pc.MaDonHang
                 WHERE pc.MaNhanVien = :employee_id
                   AND d.TrangThai NOT IN ('Hoan tat', 'Da huy', 'Hoan hang')"
            );
            $activeJobs->execute([':employee_id' => (int)$id]);
            if ((int)$activeJobs->fetchColumn() > 0) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Nhân viên còn đơn đang xử lý; hãy bàn giao trước khi xóa.'];
            }

            $references = $this->conn->prepare(
                'SELECT (SELECT COUNT(*) FROM PhanCong WHERE MaNhanVien = :employee_id) +
                        (SELECT COUNT(*) FROM LichSuTrangThai WHERE MaTaiKhoan = :account_id)'
            );
            $references->execute([':employee_id' => (int)$id, ':account_id' => (int)$accountId]);
            if ((int)$references->fetchColumn() > 0) {
                $this->conn->prepare("UPDATE NhanVien SET TrangThai = 'Nghi viec' WHERE MaNhanVien = :id")
                    ->execute([':id' => (int)$id]);
                $this->conn->prepare("UPDATE TaiKhoan SET TrangThai = 'Da khoa' WHERE MaTaiKhoan = :id")
                    ->execute([':id' => (int)$accountId]);
                $this->conn->commit();
                return ['success' => true, 'message' => 'Nhân viên có dữ liệu lịch sử nên đã được chuyển nghỉ việc và khóa tài khoản để bảo toàn hồ sơ.'];
            }

            $this->conn->prepare('DELETE FROM NhanVien WHERE MaNhanVien = :id')->execute([':id' => (int)$id]);
            $this->conn->prepare('DELETE FROM TaiKhoan WHERE MaTaiKhoan = :id')->execute([':id' => (int)$accountId]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã xóa nhân viên và tài khoản chưa phát sinh nghiệp vụ.'];
        } catch (PDOException $exception) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể xóa nhân viên do dữ liệu liên quan.'];
        }
    }
}
?>