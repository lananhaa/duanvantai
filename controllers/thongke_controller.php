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
        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo   = trim($_GET['date_to'] ?? date('Y-m-d'));
        $type     = in_array($_GET['type'] ?? 'delivery', ['delivery', 'revenue'], true)
            ? $_GET['type'] ?? 'delivery'
            : 'delivery';

        foreach (['dateFrom', 'dateTo'] as $dateField) {
            $date = DateTime::createFromFormat('!Y-m-d', $$dateField);
            if (!$date || $date->format('Y-m-d') !== $$dateField) {
                $$dateField = $dateField === 'dateFrom' ? date('Y-m-01') : date('Y-m-d');
            }
        }
        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $summary       = $model->getSummaryStats($dateFrom, $dateTo);
        $revenueSummary = $model->getRevenueSummaryStats($dateFrom, $dateTo);
        $dailyStats    = $model->getDailyStats($dateFrom, $dateTo);
        $revenueDailyStats = $model->getRevenueDailyStats($dateFrom, $dateTo);
        $driverStats   = $model->getDriverStats($dateFrom, $dateTo);
        $routeStats    = $model->getRouteStats($dateFrom, $dateTo);
        $codStats      = $model->getCodStats($dateFrom, $dateTo);
        $topCustomers  = $model->getTopCustomers(5, $dateFrom, $dateTo);

        require_once 'views/thongke.php';
    }
}
?>
