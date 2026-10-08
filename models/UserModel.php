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
            
            $storedPassword = (string)$row['MatKhau'];
            $passwordMatches = password_verify($password, $storedPassword)
                || hash_equals($storedPassword, (string)$password);
            if ($passwordMatches) {
                if ($row['TrangThai'] === 'Hoat dong') {
                    if (password_get_info($storedPassword)['algo'] === null) {
                        $upgrade = $this->conn->prepare('UPDATE TaiKhoan SET MatKhau = :password WHERE MaTaiKhoan = :id');
                        $upgrade->execute([
                            ':password' => password_hash($password, PASSWORD_DEFAULT),
                            ':id' => (int)$row['MaTaiKhoan'],
                        ]);
                    }
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
