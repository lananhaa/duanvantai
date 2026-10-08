<?php
require_once 'models/taixe_model.php';

class DriverController {
    public function index() {
        $role = (int)($_SESSION['role_id'] ?? 0);
        if ($role !== 4) {
            echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>⛔ Từ chối truy cập</h2><p>Chỉ Quản trị viên mới có quyền quản lý tài xế.</p><a href="index.php?page=trangchu">← Quay lại</a></div>';
            exit;
        }

        $model = new DriverModel();
        $message = '';
        $messageType = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username'   => trim($_POST['username'] ?? ''),
                'password'   => $_POST['password'] ?? '',
                'name'       => trim($_POST['name'] ?? ''),
                'phone'      => trim($_POST['phone'] ?? ''),
                'license'    => trim($_POST['license'] ?? ''),
                'area'       => trim($_POST['area'] ?? ''),
                'address'    => trim($_POST['address'] ?? ''),
                'status'     => trim($_POST['status'] ?? 'San sang'),
            ];
            $id = (int)($_POST['id'] ?? 0);
            if ($data['name'] === '' || $data['username'] === '' || ($id === 0 && $data['password'] === '')) {
                $message = 'Vui lòng nhập tên, tên đăng nhập và mật khẩu khi thêm mới.';
                $messageType = 'error';
            } else {
                $result = $id > 0 ? $model->update($id, $data) : $model->create($data);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
            if ($messageType === 'success') {
                header('Location: index.php?page=taixe');
                exit;
            }
        }

        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $result = $model->delete((int)$_GET['id']);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }

        $keyword  = trim($_GET['keyword'] ?? '');
        $status   = trim($_GET['status'] ?? '');
        $drivers  = $model->getAll($keyword, $status);

        require_once 'views/taixe.php';
    }
}
?>
