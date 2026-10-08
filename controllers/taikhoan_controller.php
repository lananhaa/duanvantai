<?php
require_once 'models/taikhoan_model.php';

class AccountController {
    public function index() {
        if ((int)($_SESSION['role_id'] ?? 0) !== 4) {
            http_response_code(403);
            echo 'Bạn không có quyền quản lý tài khoản.';
            exit;
        }

        $model = new AccountModel();
        $message = '';
        $messageType = 'success';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $status = trim($_POST['status'] ?? '');
            if ($id <= 0 || $username === '' || ($password !== '' && strlen($password) < 8)) {
                $result = ['success' => false, 'message' => 'Tên đăng nhập bắt buộc; mật khẩu mới phải có ít nhất 8 ký tự.'];
            } else {
                $result = $model->update($id, $username, $password, $status, (int)$_SESSION['user_id']);
            }
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            if ($result['success']) {
                header('Location: index.php?page=taikhoan&message=' . urlencode($message));
                exit;
            }
        }

        if (isset($_GET['message'])) $message = trim($_GET['message']);
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $roleId = (int)($_GET['role_id'] ?? 0);
        $accounts = $model->getAll($keyword, $status, $roleId);
        $roles = $model->getRoles();
        require 'views/taikhoan.php';
    }
}
?>