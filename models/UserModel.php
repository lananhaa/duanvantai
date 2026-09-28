<?php
class UserModel {
    private $conn;
    private $table_name = "TaiKhoan";

    public $MaTaiKhoan;
    public $MaVaiTro;
    public $TenDangNhap;
    public $MatKhau;
    public $TrangThai;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($username, $password) {
        // Query kiểm tra tài khoản
        $query = "SELECT MaTaiKhoan, MaVaiTro, TenDangNhap, MatKhau, TrangThai 
                  FROM " . $this->table_name . " 
                  WHERE TenDangNhap = :username LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Trong thực tế nên dùng password_hash và password_verify
            // Ở đây vì dữ liệu mẫu trong db.sql là password plain-text '123456'
            if ($password === $row['MatKhau']) {
                if ($row['TrangThai'] === 'Hoat dong') {
                    $this->MaTaiKhoan = $row['MaTaiKhoan'];
                    $this->MaVaiTro = $row['MaVaiTro'];
                    $this->TenDangNhap = $row['TenDangNhap'];
                    return true;
                } else {
                    return "Tài khoản của bạn đã bị khóa hoặc ngừng hoạt động.";
                }
            } else {
                return "Mật khẩu không chính xác.";
            }
        }
        return "Tên đăng nhập không tồn tại.";
    }
}
?>
