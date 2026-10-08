<?php
require_once 'models/nhanvien_model.php';

class EmployeeController {
    public function index() {
        if ((int)($_SESSION['role_id'] ?? 0) !== 4) {
            http_response_code(403);
            echo 'Bạn không có quyền quản lý nhân viên.';
            exit;
        }

        $model = new EmployeeModel();
        $message = '';
        $messageType = 'success';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['action'] ?? '') === 'delete') {
                $result = $model->delete((int)($_POST['id'] ?? 0), (int)$_SESSION['user_id']);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                if ($result['success']) {
                    header('Location: index.php?page=nhanvien&message=' . urlencode($message));
                    exit;
                }
            } else {
                $data = [
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'name' => trim($_POST['name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'position' => trim($_POST['position'] ?? ''),
                'status' => trim($_POST['status'] ?? 'Dang lam viec'),
            ];
                $id = (int)($_POST['id'] ?? 0);
                if ($data['name'] === '' || $data['username'] === '' || ($id === 0 && strlen($data['password']) < 8)) {
                    $result = ['success' => false, 'message' => 'Vui lòng nhập họ tên, tên đăng nhập và mật khẩu mới tối thiểu 8 ký tự.'];
                } elseif ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $result = ['success' => false, 'message' => 'Email không đúng định dạng.'];
                } elseif ($data['password'] !== '' && strlen($data['password']) < 8) {
                    $result = ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 8 ký tự.'];
                } else {
                    $result = $model->save($id, $data);
                }
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
                if ($result['success']) {
                    header('Location: index.php?page=nhanvien&message=' . urlencode($message));
                    exit;
                }
            }
        }

        if (isset($_GET['message'])) $message = trim($_GET['message']);
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $employees = $model->getAll($keyword, $status);
        require 'views/nhanvien.php';
    }
}
?>