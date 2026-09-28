<?php
require_once 'config/database.php';
require_once 'models/UserModel.php';

class AuthController {
    public function login() {
        // Nếu đã đăng nhập thì chuyển hướng về trang chủ
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php?page=dashboard");
            exit;
        }

        $error = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $database = new Database();
            $db = $database->getConnection();
            $user = new UserModel($db);

            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!empty($username) && !empty($password)) {
                $loginResult = $user->login($username, $password);

                if ($loginResult === true) {
                    // Đăng nhập thành công, lưu session
                    $_SESSION['user_id'] = $user->MaTaiKhoan;
                    $_SESSION['role_id'] = $user->MaVaiTro;
                    $_SESSION['username'] = $user->TenDangNhap;

                    header("Location: index.php?page=dashboard");
                    exit;
                } else {
                    $error = $loginResult; // Chuỗi lỗi từ Model
                }
            } else {
                $error = "Vui lòng nhập đầy đủ thông tin.";
            }
        }

        // Hiển thị view login
        require_once 'views/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?page=login");
        exit;
    }
}
?>
