<?php
require_once 'models/thuho_model.php';

class CodController {
    public function index() {
        $role = (int)($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [2, 4], true)) {
            echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>⛔ Từ chối truy cập</h2><p>Bạn không có quyền quản lý COD.</p><a href="index.php?page=trangchu">← Quay lại</a></div>';
            exit;
        }

        $model = new CodModel();
        $message = '';
        $messageType = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codId  = (int)($_POST['cod_id'] ?? 0);
            $status = trim($_POST['cod_status'] ?? '');
            $note   = trim($_POST['cod_note'] ?? '');

            if ($codId <= 0 || $status === '') {
                $message = 'Vui lòng chọn COD và trạng thái hợp lệ.';
                $messageType = 'error';
            } else {
                $result = $model->updateStatus($codId, $status, $note, $_SESSION['user_id'] ?? 0);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
            if ($messageType === 'success') {
                header('Location: index.php?page=thuho');
                exit;
            }
        }

        $keyword   = trim($_GET['keyword'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $dateFrom  = trim($_GET['date_from'] ?? '');
        $dateTo    = trim($_GET['date_to'] ?? '');
        $cods      = $model->getAll($keyword, $status, $dateFrom, $dateTo);
        $summary   = $model->getSummary();

        require_once 'views/thuho.php';
    }
}
?>
