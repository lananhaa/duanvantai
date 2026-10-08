<?php
require_once 'models/phuongtien_model.php';

class VehicleController {
    public function index() {
        $role = (int)($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [3, 4], true)) {
            echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>⛔ Từ chối truy cập</h2><p>Bạn không có quyền quản lý phương tiện.</p><a href="index.php?page=trangchu">← Quay lại</a></div>';
            exit;
        }

        $model = new VehicleModel();
        $driverId = $role === 3 ? $model->getDriverIdByAccount($_SESSION['user_id'] ?? 0) : null;
        if ($role === 3 && $driverId === null) {
            echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>Không tìm thấy hồ sơ tài xế</h2><p>Tài khoản này chưa được liên kết với hồ sơ tài xế.</p><a href="index.php?page=trangchu">← Quay lại</a></div>';
            exit;
        }
        $message = '';
        $messageType = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ownerDriverId = $driverId;
            if ($role === 4) {
                $ownerDriverId = (int)($_POST['driver_id'] ?? 0) ?: null;
            }
            $data = [
                'bien_so'   => trim($_POST['bien_so'] ?? ''),
                'loai'      => trim($_POST['loai'] ?? ''),
                'tai_trong' => (float)($_POST['tai_trong'] ?? 0),
                'status'    => trim($_POST['status'] ?? $model->getDefaultStatus($ownerDriverId)),
                'mo_ta'     => trim($_POST['mo_ta'] ?? ''),
            ];
            $id = (int)($_POST['id'] ?? 0);
            if ($data['bien_so'] === '' || !in_array($data['status'], ['San sang', 'Du phong', 'Dang chay', 'Bao tri'], true)) {
                $message = $data['bien_so'] === '' ? 'Vui lòng nhập biển số xe.' : 'Trạng thái phương tiện không hợp lệ.';
                $messageType = 'error';
            } else {
                $result = $id > 0
                    ? $model->update($id, $data, $driverId, $ownerDriverId)
                    : $model->create($data, $ownerDriverId);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
            if ($messageType === 'success') {
                header('Location: index.php?page=phuongtien');
                exit;
            }
        }

        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $result = $model->delete((int)$_GET['id'], $driverId);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }

        $keyword  = trim($_GET['keyword'] ?? '');
        $status   = trim($_GET['status'] ?? '');
        $vehicles = $model->getAll($keyword, $status, $driverId);
        $drivers = $role === 4 ? $model->getDriverOptions() : [];
        $defaultVehicleStatus = $model->getDefaultStatus($driverId);

        require_once 'views/phuongtien.php';
    }
}
?>
