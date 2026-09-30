<?php
require_once 'models/thongke_model.php';

class StatisticController {
    public function index() {
        $role = (int)($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [2, 4], true)) {
            echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>⛔ Từ chối truy cập</h2><p>Bạn không có quyền xem thống kê.</p><a href="index.php?page=trangchu">← Quay lại</a></div>';
            exit;
        }

        $model    = new StatisticModel();
        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01')); // Mặc định đầu tháng hiện tại
        $dateTo   = trim($_GET['date_to']   ?? date('Y-m-d'));  // Mặc định hôm nay
        $type     = trim($_GET['type'] ?? 'delivery');           // 'delivery' | 'revenue'

        $summary       = $model->getSummaryStats($dateFrom, $dateTo);
        $dailyStats    = $model->getDailyStats($dateFrom, $dateTo);
        $driverStats   = $model->getDriverStats($dateFrom, $dateTo);
        $routeStats    = $model->getRouteStats($dateFrom, $dateTo);
        $codStats      = $model->getCodStats($dateFrom, $dateTo);
        $topCustomers  = $model->getTopCustomers(5, $dateFrom, $dateTo);

        require_once 'views/thongke.php';
    }
}
?>
