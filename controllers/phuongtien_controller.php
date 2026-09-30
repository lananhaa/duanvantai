<?php
require_once 'models/phuongtien_model.php';

class VehicleController {
    public function index() {
        $role = (int)($_SESSION['role_id'] ?? 0);
        if ($role !== 4) {
            echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>⛔ Từ chối truy cập</h2><p>Chỉ Quản trị viên mới có quyền quản lý phương tiện.</p><a href="index.php?page=trangchu">← Quay lại</a></div>';
            exit;
        }

        $model = new VehicleModel();
        $message = '';
        $messageType = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'bien_so'   => trim($_POST['bien_so'] ?? ''),
                'loai'      => trim($_POST['loai'] ?? ''),
                'tai_trong' => (float)($_POST['tai_trong'] ?? 0),
                'status'    => trim($_POST['status'] ?? 'San sang'),
                'mo_ta'     => trim($_POST['mo_ta'] ?? ''),
            ];
            $id = (int)($_POST['id'] ?? 0);
            if ($data['bien_so'] === '') {
                $message = 'Vui lòng nhập biển số xe.';
                $messageType = 'error';
            } else {
                $result = $id > 0 ? $model->update($id, $data) : $model->create($data);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
            if ($messageType === 'success') {
                header('Location: index.php?page=phuongtien');
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
        $vehicles = $model->getAll($keyword, $status);

        require_once 'views/phuongtien.php';
    }
}
?>
