<?php
require_once 'models/tuyengiao_model.php';

class PhivanchuyenControler {
    public function index() {
        $role = (int)($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [2, 4], true)) {
            echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>⛔ Từ chối truy cập</h2><p>Bạn không có quyền quản lý phí vận chuyển.</p><a href="index.php?page=trangchu">← Quay lại</a></div>';
            exit;
        }

        $model = new RouteModel();
        $message = '';
        $messageType = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $data = [
                'ten'         => trim($_POST['ten'] ?? ''),
                'khu_vuc_di'  => trim($_POST['khu_vuc_di'] ?? ''),
                'khu_vuc_den' => trim($_POST['khu_vuc_den'] ?? ''),
                'kl_tu'       => (float)($_POST['kl_tu'] ?? 0),
                'kl_den'      => (float)($_POST['kl_den'] ?? 0),
                'phi_co_ban'  => (float)($_POST['phi_co_ban'] ?? 0),
                'phi_vuot'    => (float)($_POST['phi_vuot'] ?? 0),
                'status'      => trim($_POST['status'] ?? 'Dang ap dung'),
            ];

            if ($data['ten'] === '' || $data['khu_vuc_di'] === '' || $data['khu_vuc_den'] === '' || $data['phi_co_ban'] <= 0) {
                $message = 'Vui lòng nhập đầy đủ thông tin mức phí.';
                $messageType = 'error';
            } else {
                $result = $id > 0 ? $model->updateFee($id, $data) : $model->createFee($data);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
        }

        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $id = (int)$_GET['id'];
            $result = $model->deleteFee($id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $status  = trim($_GET['status'] ?? '');
        $fees    = $model->getAllFees($keyword, $status);

        require_once 'views/phivanchuyen.php';
    }
}