<?php
require_once 'config/database.php';

class DriverModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '', $status = '') {
        $query = "SELECT tx.MaTaiXe, tx.HoTen, tx.SoDienThoai, tx.SoBangLai, tx.KhuVucHienTai,
                         tx.ThoiGianCapNhatViTri, tx.TrangThai, tx.DiaChi, tx.NgayTao,
                         t.TenDangNhap,
                         pt.BienSo, pt.LoaiPhuongTien, pt.TaiTrong, pt.TrangThai AS TrangThaiXe,
                         (SELECT COUNT(*) FROM PhanCong pc WHERE pc.MaTaiXe = tx.MaTaiXe
                          AND pc.TrangThai NOT IN ('Hoan thanh','Da huy','Hoan hang','Hoan tat')) AS SoDonDangNhan
                  FROM TaiXe tx
                  INNER JOIN TaiKhoan t ON tx.MaTaiKhoan = t.MaTaiKhoan
                  LEFT JOIN PhuongTien pt ON tx.MaPhuongTien = pt.MaPhuongTien
                  WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (tx.HoTen LIKE :kw OR tx.SoDienThoai LIKE :kw OR tx.SoBangLai LIKE :kw
                         OR tx.KhuVucHienTai LIKE :kw OR tx.DiaChi LIKE :kw OR t.TenDangNhap LIKE :kw OR pt.BienSo LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $query .= " AND tx.TrangThai = :status";
            $params[':status'] = $status;
        }
        $query .= " ORDER BY tx.MaTaiXe DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare(
            "SELECT tx.*, t.TenDangNhap, pt.BienSo, pt.LoaiPhuongTien, pt.TaiTrong
             FROM TaiXe tx
             INNER JOIN TaiKhoan t ON tx.MaTaiKhoan = t.MaTaiKhoan
             LEFT JOIN PhuongTien pt ON tx.MaPhuongTien = pt.MaPhuongTien
             WHERE tx.MaTaiXe = :id"
        );
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVehicleOptions() {
        return $this->conn->query("SELECT MaPhuongTien, BienSo, LoaiPhuongTien, TaiTrong FROM PhuongTien WHERE TrangThai != 'Bao tri' ORDER BY BienSo")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    private function usernameExists($username, $accountId = null) {
        $query = "SELECT MaTaiKhoan FROM TaiKhoan WHERE TenDangNhap = :username";
        if ($accountId !== null) $query .= " AND MaTaiKhoan != :account_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':username', $username);
        if ($accountId !== null) $stmt->bindValue(':account_id', (int)$accountId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    public function create($data) {
        if ($this->usernameExists($data['username'])) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.'];
        }
        try {
            $this->conn->beginTransaction();
            $acc = $this->conn->prepare("INSERT INTO TaiKhoan (MaVaiTro, TenDangNhap, MatKhau, TrangThai) VALUES (3, :u, :p, 'Hoat dong')");
            $acc->execute([':u' => $data['username'], ':p' => password_hash($data['password'], PASSWORD_DEFAULT)]);
            $accountId = $this->conn->lastInsertId();

            $tx = $this->conn->prepare(
                "INSERT INTO TaiXe (MaTaiKhoan, MaPhuongTien, HoTen, SoDienThoai, SoBangLai, KhuVucHienTai, DiaChi, TrangThai)
                 VALUES (:acc, :pt, :name, :phone, :bang, :kvuc, :addr, 'San sang')"
            );
            $tx->execute([
                ':acc'   => $accountId,
                ':pt'    => $data['vehicle_id'] ?: null,
                ':name'  => $data['name'],
                ':phone' => $data['phone'],
                ':bang'  => $data['license'],
                ':kvuc'  => $data['area'],
                ':addr'  => $data['address'],
            ]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Thêm tài xế thành công.'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể thêm tài xế. Kiểm tra lại dữ liệu.'];
        }
    }

    public function update($id, $data) {
        $driver = $this->getById($id);
        if (!$driver) return ['success' => false, 'message' => 'Không tìm thấy tài xế.'];
        $activeAssignments = $this->conn->prepare("SELECT COUNT(*) FROM PhanCong
            WHERE MaTaiXe = :id AND TrangThai NOT IN ('Hoan thanh', 'Da huy', 'Hoan hang', 'Hoan tat')");
        $activeAssignments->execute([':id' => (int)$id]);
        if ((int)$activeAssignments->fetchColumn() > 0 && $data['status'] !== 'Dang giao') {
            return ['success' => false, 'message' => 'Tài xế đang phụ trách đơn hàng; chỉ có thể đổi trạng thái sau khi hoàn tất hoặc hủy phân công.'];
        }
        if ($this->usernameExists($data['username'], $driver['MaTaiKhoan'])) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.'];
        }
        try {
            $this->conn->beginTransaction();
            $accSql = "UPDATE TaiKhoan SET TenDangNhap = :u" . ($data['password'] !== '' ? ", MatKhau = :p" : '') . " WHERE MaTaiKhoan = :aid";
            $accData = [':u' => $data['username'], ':aid' => $driver['MaTaiKhoan']];
            if ($data['password'] !== '') $accData[':p'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $this->conn->prepare($accSql)->execute($accData);

            $this->conn->prepare(
                "UPDATE TaiXe SET MaPhuongTien = :pt, HoTen = :name, SoDienThoai = :phone, SoBangLai = :bang,
                 KhuVucHienTai = :kvuc, DiaChi = :addr, TrangThai = :status WHERE MaTaiXe = :id"
            )->execute([
                ':pt'     => $data['vehicle_id'] ?: null,
                ':name'   => $data['name'],
                ':phone'  => $data['phone'],
                ':bang'   => $data['license'],
                ':kvuc'   => $data['area'],
                ':addr'   => $data['address'],
                ':status' => $data['status'],
                ':id'     => (int)$id,
            ]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật tài xế thành công.'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể cập nhật tài xế.'];
        }
    }

    public function delete($id) {
        $driver = $this->getById($id);
        if (!$driver) return ['success' => false, 'message' => 'Không tìm thấy tài xế.'];

        $assignmentCount = $this->conn->prepare("SELECT COUNT(*) FROM PhanCong WHERE MaTaiXe = :id");
        $assignmentCount->execute([':id' => (int)$id]);
        if ((int)$assignmentCount->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Không thể xóa tài xế vì đang có đơn hàng hoặc phân công đang hoạt động.'];
        }

        try {
            $this->conn->beginTransaction();
            $this->conn->prepare("DELETE FROM TaiXe WHERE MaTaiXe = :id")->execute([':id' => (int)$id]);
            $this->conn->prepare("DELETE FROM TaiKhoan WHERE MaTaiKhoan = :id")->execute([':id' => $driver['MaTaiKhoan']]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Xóa tài xế thành công.'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể xóa tài xế đang có dữ liệu liên quan.'];
        }
    }
}
?>
