<?php
require_once 'models/DriverOrderModel.php';

class DriverOrderController {
    public function index() {
        $role = (int) ($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [1, 2, 3, 4], true)) {
            http_response_code(403);
            echo 'Bạn không có quyền theo dõi đơn hàng.';
            exit;
        }
        $model = new DriverOrderModel();
        $accountId = (int) ($_SESSION['user_id'] ?? 0);
        $isAdmin = $role === 4;
        $driverId = $role === 3 ? $model->getDriverIdByAccount($accountId) : null;
        if ($role === 3 && !$driverId) {
            http_response_code(403);
            echo 'Tài khoản chưa được liên kết với hồ sơ tài xế.';
            exit;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $action = $_POST['action'] ?? '';
            if (($role === 3 || $isAdmin) && $action === 'status') {
                $result = $model->updateOrderStatus(
                    $_POST['order_id'] ?? 0,
                    $driverId,
                    $_SESSION['user_id'],
                    trim($_POST['status'] ?? ''),
                    trim($_POST['note'] ?? '')
                );
            } elseif (($role === 3 || $isAdmin) && $action === 'cod') {
                $result = $model->updateCod(
                    $_POST['order_id'] ?? 0,
                    $driverId,
                    trim($_POST['cod_status'] ?? ''),
                    trim($_POST['cod_note'] ?? '')
                );
            } elseif ($role === 3 && $action === 'location') {
                $location = trim($_POST['location'] ?? '');
                $result = $location === ''
                    ? ['success' => false, 'message' => 'Vui lòng nhập khu vực hiện tại.']
                    : $model->updateLocation($driverId, $location);
            } elseif ($role === 1 && $action === 'cancel') {
                $result = $model->cancelCustomerOrder(
                    $_POST['order_id'] ?? 0,
                    $accountId,
                    trim($_POST['reason'] ?? '')
                );
            } else {
                $result = ['success' => false, 'message' => 'Thao tác không hợp lệ.'];
            }

            $_SESSION['tracking_flash'] = $result;
            $keyword = trim($_POST['keyword'] ?? '');
            header('Location: index.php?page=tracking' . ($keyword !== '' ? '&keyword=' . urlencode($keyword) : ''));
            exit;
        }

        $flash = $_SESSION['tracking_flash'] ?? null;
        unset($_SESSION['tracking_flash']);
        $keyword = trim($_GET['keyword'] ?? '');
        $orders = $role === 3
            ? $model->getAssignedOrders($driverId, $keyword)
            : $model->getOrdersForTracking($keyword, $role === 1 ? $accountId : null);
        $historyByOrder = [];
        foreach ($orders as $order) {
            $historyByOrder[$order['MaDonHang']] = $model->getOrderHistory($order['MaDonHang'], $role === 3 ? $driverId : null);
        }
        if (isset($_GET['view'])) {
            $viewId = (int) $_GET['view'];
            $detailOrders = $role === 3
                ? $model->getAssignedOrders($driverId)
                : $model->getOrdersForTracking('', $role === 1 ? $accountId : null);
            $detailOrder = null;
            foreach ($detailOrders as $order) {
                if ((int) $order['MaDonHang'] === $viewId) {
                    $detailOrder = $order;
                    break;
                }
            }
            if (!$detailOrder) {
                http_response_code(404);
                echo 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem đơn này.';
                exit;
            }
            $detailHistory = $model->getOrderHistory($viewId, $role === 3 ? $driverId : null);
            require_once 'views/driver_order_detail.php';
            exit;
        }
        $currentLocation = $role === 3 ? $model->getCurrentLocation($driverId) : '';
        require_once 'views/driver_orders.php';
    }
}
?>