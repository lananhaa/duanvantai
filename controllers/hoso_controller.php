<?php
require_once 'models/taikhoan_model.php';

class ProfileController {
    public function index() {
        $accountId = (int)($_SESSION['user_id'] ?? 0);
        $roleId = (int)($_SESSION['role_id'] ?? 0);
        if ($accountId <= 0 || !in_array($roleId, [1, 2, 3, 4], true)) {
            http_response_code(403);
            echo 'Không thể xác định hồ sơ tài khoản.';
            exit;
        }

        $model = new AccountModel();
        $message = trim($_GET['message'] ?? '');
        $messageType = 'success';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'name' => trim($_POST['name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'area' => trim($_POST['area'] ?? ''),
                'license' => trim($_POST['license'] ?? ''),
                'current_password' => $_POST['current_password'] ?? '',
                'new_password' => $_POST['new_password'] ?? '',
            ];
            $confirmation = $_POST['confirm_password'] ?? '';

            if ($data['username'] === '' || (in_array($roleId, [1, 2, 3], true) && $data['name'] === '')) {
                $message = 'Vui lòng nhập tên đăng nhập và họ tên.';
                $messageType = 'error';
            } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $message = 'Email không đúng định dạng.';
                $messageType = 'error';
            } elseif ($data['new_password'] !== '' && strlen($data['new_password']) < 8) {
                $message = 'Mật khẩu mới phải có ít nhất 8 ký tự.';
                $messageType = 'error';
            } elseif ($data['new_password'] !== '' && $data['new_password'] !== $confirmation) {
                $message = 'Mật khẩu mới và xác nhận không khớp.';
                $messageType = 'error';
            } elseif ($data['new_password'] !== '' && $data['current_password'] === '') {
                $message = 'Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu.';
                $messageType = 'error';
            } else {
                $result = $model->updateOwnProfile($accountId, $roleId, $data);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                if ($result['success']) {
                    $_SESSION['username'] = $data['username'];
                    header('Location: index.php?page=hoso&message=' . urlencode($message));
                    exit;
                }
            }
        }

        $profileData = $model->getOwnProfile($accountId, $roleId);
        if (!$profileData) {
            http_response_code(403);
            echo 'Tài khoản không hoạt động hoặc hồ sơ cá nhân không tồn tại.';
            exit;
        }
        $account = $profileData['account'];
        $profile = $profileData['profile'];
        require 'views/hoso.php';
    }
}
?>