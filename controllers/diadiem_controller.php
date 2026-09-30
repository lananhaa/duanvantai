<?php
require_once 'models/diadiem_model.php';

class LocationController {
    public function index() {
        $role = (int)($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [2, 4], true)) {
            echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>⛔ Từ chối truy cập</h2><p>Bạn không có quyền quản lý điểm nhận/giao.</p><a href="index.php?page=trangchu">← Quay lại</a></div>';
            exit;
        }

        $model = new LocationModel();
        $message = '';
        $messageType = 'success';
        $tab = $_GET['tab'] ?? 'pickups'; // 'pickups' | 'deliveries'

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['form_type'] ?? 'pickup';
            $id = (int)($_POST['id'] ?? 0);

            if ($type === 'pickup') {
                $data = [
                    'dia_chi'     => trim($_POST['dia_chi'] ?? ''),
                    'khu_vuc'     => trim($_POST['khu_vuc'] ?? ''),
                    'tinh_thanh'  => trim($_POST['tinh_thanh'] ?? ''),
                    'quan_huyen'  => trim($_POST['quan_huyen'] ?? ''),
                    'phuong_xa'   => trim($_POST['phuong_xa'] ?? ''),
                    'sdt'         => trim($_POST['sdt'] ?? ''),
                    'ghi_chu'     => trim($_POST['ghi_chu'] ?? ''),
                ];
                if ($data['dia_chi'] === '') {
                    $message = 'Vui lòng nhập địa chỉ điểm nhận.';
                    $messageType = 'error';
                } else {
                    $result = $id > 0 ? $model->updatePickup($id, $data) : $model->createPickup($data);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'error';
                }
                if ($messageType === 'success') { header('Location: index.php?page=diadiem&tab=pickups'); exit; }

            } elseif ($type === 'delivery') {
                $data = [
                    'ten_nguoi_nhan' => trim($_POST['ten_nguoi_nhan'] ?? ''),
                    'sdt'            => trim($_POST['sdt'] ?? ''),
                    'dia_chi'        => trim($_POST['dia_chi'] ?? ''),
                    'khu_vuc'        => trim($_POST['khu_vuc'] ?? ''),
                    'tinh_thanh'     => trim($_POST['tinh_thanh'] ?? ''),
                    'quan_huyen'     => trim($_POST['quan_huyen'] ?? ''),
                    'phuong_xa'      => trim($_POST['phuong_xa'] ?? ''),
                    'ghi_chu'        => trim($_POST['ghi_chu'] ?? ''),
                ];
                if ($data['ten_nguoi_nhan'] === '' || $data['dia_chi'] === '') {
                    $message = 'Vui lòng nhập tên người nhận và địa chỉ.';
                    $messageType = 'error';
                } else {
                    $result = $id > 0 ? $model->updateDelivery($id, $data) : $model->createDelivery($data);
                    $message = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'error';
                }
                if ($messageType === 'success') { header('Location: index.php?page=diadiem&tab=deliveries'); exit; }
                $tab = 'deliveries';
            }
        }

        if (isset($_GET['action'], $_GET['id'])) {
            $id = (int)$_GET['id'];
            if ($_GET['action'] === 'delete_pickup') {
                $result = $model->deletePickup($id);
                $tab = 'pickups';
            } elseif ($_GET['action'] === 'delete_delivery') {
                $result = $model->deleteDelivery($id);
                $tab = 'deliveries';
            }
            if (isset($result)) {
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
        }

        $keyword    = trim($_GET['keyword'] ?? '');
        $pickups    = $model->getAllPickups($keyword);
        $deliveries = $model->getAllDeliveries($keyword);

        require_once 'views/diadiem.php';
    }
}
?>
