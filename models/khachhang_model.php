<?php
require_once 'config/database.php';

class CustomerModel {
    private $conn;
    private $table_name = "KhachHang";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '') {
        $query = "SELECT k.MaKhachHang, k.HoTen, k.SoDienThoai, k.Email, k.DiaChi, k.NgayTao, t.TenDangNhap 
                  FROM " . $this->table_name . " k
                  LEFT JOIN TaiKhoan t ON k.MaTaiKhoan = t.MaTaiKhoan";

        if (!empty($keyword)) {
            $query .= " WHERE k.HoTen LIKE :keyword OR k.SoDienThoai LIKE :keyword OR k.Email LIKE :keyword OR k.DiaChi LIKE :keyword OR t.TenDangNhap LIKE :keyword";
        }
        $query .= " ORDER BY k.MaKhachHang DESC";
        
        $stmt = $this->conn->prepare($query);

        if (!empty($keyword)) {
            $keyword = "%{$keyword}%";
            $stmt->bindParam(":keyword", $keyword);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT k.MaKhachHang, k.MaTaiKhoan, k.HoTen, k.SoDienThoai, k.Email, k.DiaChi, t.TenDangNhap
                  FROM " . $this->table_name . " k
                  INNER JOIN TaiKhoan t ON k.MaTaiKhoan = t.MaTaiKhoan
                  WHERE k.MaKhachHang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int) $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function usernameExists($username, $accountId = null) {
        $query = "SELECT MaTaiKhoan FROM TaiKhoan WHERE TenDangNhap = :username";
        if ($accountId !== null) {
            $query .= " AND MaTaiKhoan != :account_id";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':username', $username);
        if ($accountId !== null) {
            $stmt->bindValue(':account_id', (int) $accountId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function create($data) {
        if ($this->usernameExists($data['username'])) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.'];
        }

        try {
            $this->conn->beginTransaction();

            $account = $this->conn->prepare("INSERT INTO TaiKhoan (MaVaiTro, TenDangNhap, MatKhau, TrangThai) VALUES (1, :username, :password, 'Hoat dong')");
            $account->execute([
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT)
            ]);

            $customer = $this->conn->prepare("INSERT INTO KhachHang (MaTaiKhoan, HoTen, SoDienThoai, Email, DiaChi) VALUES (:account_id, :name, :phone, :email, :address)");
            $customer->execute([
                ':account_id' => $this->conn->lastInsertId(),
                ':name' => $data['name'],
                ':phone' => $data['phone'],
                ':email' => $data['email'],
                ':address' => $data['address']
            ]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Thêm khách hàng thành công.'];
        } catch (PDOException $exception) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể thêm khách hàng. Vui lòng kiểm tra dữ liệu.'];
        }
    }

    public function update($id, $data) {
        $customer = $this->getById($id);
        if (!$customer) {
            return ['success' => false, 'message' => 'Không tìm thấy khách hàng.'];
        }
        if ($this->usernameExists($data['username'], $customer['MaTaiKhoan'])) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại.'];
        }

        try {
            $this->conn->beginTransaction();
            $account = $this->conn->prepare("UPDATE TaiKhoan SET TenDangNhap = :username" . ($data['password'] !== '' ? ", MatKhau = :password" : '') . " WHERE MaTaiKhoan = :account_id");
            $accountData = [':username' => $data['username'], ':account_id' => $customer['MaTaiKhoan']];
            if ($data['password'] !== '') {
                $accountData[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            $account->execute($accountData);

            $customerStatement = $this->conn->prepare("UPDATE KhachHang SET HoTen = :name, SoDienThoai = :phone, Email = :email, DiaChi = :address WHERE MaKhachHang = :id");
            $customerStatement->execute([
                ':name' => $data['name'],
                ':phone' => $data['phone'],
                ':email' => $data['email'],
                ':address' => $data['address'],
                ':id' => (int) $id
            ]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật khách hàng thành công.'];
        } catch (PDOException $exception) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể cập nhật khách hàng. Vui lòng kiểm tra dữ liệu.'];
        }
    }

    public function delete($id) {
        $customer = $this->getById($id);
        if (!$customer) {
            return ['success' => false, 'message' => 'Không tìm thấy khách hàng.'];
        }

        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("DELETE FROM KhachHang WHERE MaKhachHang = :id");
            $stmt->execute([':id' => (int) $id]);
            $account = $this->conn->prepare("DELETE FROM TaiKhoan WHERE MaTaiKhoan = :id");
            $account->execute([':id' => (int) $customer['MaTaiKhoan']]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Xóa khách hàng thành công.'];
        } catch (PDOException $exception) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể xóa khách hàng đang có đơn hàng hoặc dữ liệu liên quan.'];
        }
    }
}
?>
