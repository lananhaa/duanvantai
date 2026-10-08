<?php
require_once 'config/database.php';

class AccountModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '', $status = '', $roleId = 0) {
        $query = "SELECT t.MaTaiKhoan, t.MaVaiTro, t.TenDangNhap, t.TrangThai, t.NgayTao,
                         r.TenVaiTro, kh.HoTen AS TenKhachHang, nv.HoTen AS TenNhanVien,
                         tx.HoTen AS TenTaiXe
                  FROM TaiKhoan t
                  INNER JOIN VaiTro r ON r.MaVaiTro = t.MaVaiTro
                  LEFT JOIN KhachHang kh ON kh.MaTaiKhoan = t.MaTaiKhoan
                  LEFT JOIN NhanVien nv ON nv.MaTaiKhoan = t.MaTaiKhoan
                  LEFT JOIN TaiXe tx ON tx.MaTaiKhoan = t.MaTaiKhoan
                  WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (t.TenDangNhap LIKE :keyword OR kh.HoTen LIKE :keyword OR nv.HoTen LIKE :keyword OR tx.HoTen LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $query .= ' AND t.TrangThai = :status';
            $params[':status'] = $status;
        }
        if ($roleId > 0) {
            $query .= ' AND t.MaVaiTro = :role_id';
            $params[':role_id'] = $roleId;
        }
        $query .= ' ORDER BY t.MaTaiKhoan DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoles() {
        return $this->conn->query('SELECT MaVaiTro, TenVaiTro FROM VaiTro ORDER BY MaVaiTro')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOwnProfile($accountId, $roleId) {
        $accountStmt = $this->conn->prepare('SELECT MaTaiKhoan, MaVaiTro, TenDangNhap, NgayTao FROM TaiKhoan WHERE MaTaiKhoan = :id AND TrangThai = \'Hoat dong\'');
        $accountStmt->execute([':id' => (int)$accountId]);
        $account = $accountStmt->fetch(PDO::FETCH_ASSOC);
        if (!$account || (int)$account['MaVaiTro'] !== (int)$roleId) return null;

        $profile = [];
        if ((int)$roleId === 1) {
            $query = 'SELECT HoTen AS name, SoDienThoai AS phone, Email AS email, DiaChi AS address FROM KhachHang WHERE MaTaiKhoan = :id';
        } elseif ((int)$roleId === 2) {
            $query = 'SELECT HoTen AS name, SoDienThoai AS phone, Email AS email, ChucVu AS position FROM NhanVien WHERE MaTaiKhoan = :id';
        } elseif ((int)$roleId === 3) {
            $query = 'SELECT HoTen AS name, SoDienThoai AS phone, DiaChi AS address, KhuVucHienTai AS area, SoBangLai AS license FROM TaiXe WHERE MaTaiKhoan = :id';
        } else {
            return ['account' => $account, 'profile' => $profile];
        }
        $profileStmt = $this->conn->prepare($query);
        $profileStmt->execute([':id' => (int)$accountId]);
        $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);
        return $profile ? ['account' => $account, 'profile' => $profile] : null;
    }

    public function updateOwnProfile($accountId, $roleId, $data) {
        if ($this->usernameExists($data['username'], $accountId)) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng.'];
        }

        try {
            $this->conn->beginTransaction();
            $accountStmt = $this->conn->prepare('SELECT MaVaiTro, MatKhau FROM TaiKhoan WHERE MaTaiKhoan = :id AND TrangThai = \'Hoat dong\' FOR UPDATE');
            $accountStmt->execute([':id' => (int)$accountId]);
            $account = $accountStmt->fetch(PDO::FETCH_ASSOC);
            if (!$account || (int)$account['MaVaiTro'] !== (int)$roleId) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không thể xác minh hồ sơ tài khoản hiện tại.'];
            }

            if ($data['new_password'] !== '') {
                $storedPassword = (string)$account['MatKhau'];
                $validCurrentPassword = password_verify($data['current_password'], $storedPassword)
                    || hash_equals($storedPassword, (string)$data['current_password']);
                if (!$validCurrentPassword) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác.'];
                }
            }

            $accountQuery = 'UPDATE TaiKhoan SET TenDangNhap = :username';
            $accountData = [':username' => $data['username'], ':id' => (int)$accountId];
            if ($data['new_password'] !== '') {
                $accountQuery .= ', MatKhau = :password';
                $accountData[':password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
            }
            $this->conn->prepare($accountQuery . ' WHERE MaTaiKhoan = :id')->execute($accountData);

            if ((int)$roleId === 1) {
                $profileQuery = 'UPDATE KhachHang SET HoTen = :name, SoDienThoai = :phone, Email = :email, DiaChi = :address WHERE MaTaiKhoan = :id';
                $profileData = [':name' => $data['name'], ':phone' => $data['phone'], ':email' => $data['email'], ':address' => $data['address'], ':id' => (int)$accountId];
            } elseif ((int)$roleId === 2) {
                $profileQuery = 'UPDATE NhanVien SET HoTen = :name, SoDienThoai = :phone, Email = :email WHERE MaTaiKhoan = :id';
                $profileData = [':name' => $data['name'], ':phone' => $data['phone'], ':email' => $data['email'], ':id' => (int)$accountId];
            } elseif ((int)$roleId === 3) {
                $profileQuery = 'UPDATE TaiXe SET HoTen = :name, SoDienThoai = :phone, DiaChi = :address, KhuVucHienTai = :area, SoBangLai = :license WHERE MaTaiKhoan = :id';
                $profileData = [':name' => $data['name'], ':phone' => $data['phone'], ':address' => $data['address'], ':area' => $data['area'], ':license' => $data['license'], ':id' => (int)$accountId];
            } else {
                $profileQuery = '';
                $profileData = [];
            }
            if ($profileQuery !== '') {
                $this->conn->prepare($profileQuery)->execute($profileData);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã cập nhật hồ sơ cá nhân.'];
        } catch (PDOException $exception) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể cập nhật hồ sơ. Tên đăng nhập có thể đã được sử dụng.'];
        }
    }

    private function usernameExists($username, $accountId) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM TaiKhoan WHERE TenDangNhap = :username AND MaTaiKhoan <> :id');
        $stmt->execute([':username' => $username, ':id' => (int)$accountId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function update($id, $username, $password, $status, $actorId) {
        if (!in_array($status, ['Hoat dong', 'Da khoa'], true)) {
            return ['success' => false, 'message' => 'Trạng thái tài khoản không hợp lệ.'];
        }
        if ($this->usernameExists($username, $id)) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã được sử dụng.'];
        }

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare('SELECT MaVaiTro, TrangThai FROM TaiKhoan WHERE MaTaiKhoan = :id FOR UPDATE');
            $stmt->execute([':id' => (int)$id]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$account) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy tài khoản.'];
            }
            if ((int)$actorId === (int)$id && $status !== 'Hoat dong') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Bạn không thể khóa tài khoản đang đăng nhập.'];
            }
            if ($status === 'Hoat dong' && (int)$account['MaVaiTro'] === 2) {
                $employeeStatus = $this->conn->prepare('SELECT TrangThai FROM NhanVien WHERE MaTaiKhoan = :id');
                $employeeStatus->execute([':id' => (int)$id]);
                if ($employeeStatus->fetchColumn() === 'Nghi viec') {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Nhân viên đang nghỉ việc; hãy cập nhật hồ sơ nhân viên trước khi mở tài khoản.'];
                }
            }
            if ($status === 'Hoat dong' && (int)$account['MaVaiTro'] === 3) {
                $driverStatus = $this->conn->prepare('SELECT TrangThai FROM TaiXe WHERE MaTaiKhoan = :id');
                $driverStatus->execute([':id' => (int)$id]);
                if ($driverStatus->fetchColumn() === 'Nghi') {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Tài xế đang nghỉ; hãy cập nhật hồ sơ tài xế trước khi mở tài khoản.'];
                }
            }
            if ((int)$account['MaVaiTro'] === 4 && $status !== 'Hoat dong') {
                $admins = $this->conn->query("SELECT COUNT(*) FROM TaiKhoan WHERE MaVaiTro = 4 AND TrangThai = 'Hoat dong'")->fetchColumn();
                if ((int)$admins <= 1) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Không thể khóa tài khoản quản trị viên đang hoạt động cuối cùng.'];
                }
            }
            if (in_array((int)$account['MaVaiTro'], [2, 3], true) && $status !== 'Hoat dong') {
                $activeJobs = $this->conn->prepare(
                    "SELECT COUNT(*) FROM PhanCong pc
                     INNER JOIN DonHang d ON d.MaDonHang = pc.MaDonHang
                     LEFT JOIN TaiXe tx ON tx.MaTaiXe = pc.MaTaiXe
                     LEFT JOIN NhanVien nv ON nv.MaNhanVien = pc.MaNhanVien
                     WHERE (tx.MaTaiKhoan = :driver_account OR nv.MaTaiKhoan = :employee_account)
                       AND d.TrangThai NOT IN ('Hoan tat', 'Da huy', 'Hoan hang')"
                );
                $activeJobs->execute([':driver_account' => (int)$id, ':employee_account' => (int)$id]);
                if ((int)$activeJobs->fetchColumn() > 0) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Tài khoản còn gắn với đơn đang xử lý; hãy điều phối lại trước khi khóa.'];
                }
            }

            $query = 'UPDATE TaiKhoan SET TenDangNhap = :username, TrangThai = :status';
            $params = [':username' => $username, ':status' => $status, ':id' => (int)$id];
            if ($password !== '') {
                $query .= ', MatKhau = :password';
                $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $query .= ' WHERE MaTaiKhoan = :id';
            $this->conn->prepare($query)->execute($params);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật tài khoản thành công.'];
        } catch (PDOException $exception) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể cập nhật tài khoản.'];
        }
    }
}
?>