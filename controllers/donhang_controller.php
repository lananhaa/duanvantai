<?php
require_once 'models/donhang_model.php';

class OrderController {
    public function index() {
        $role = (int) ($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [1, 2, 4], true)) {
            echo 'Bạn không có quyền truy cập trang này.';
            exit;
        }

        $orderModel = new OrderModel();
        $customerId = $role === 1 ? $orderModel->getCustomerIdByAccount($_SESSION['user_id'] ?? 0) : null;
        $message = '';
        $messageType = 'success';

        if (($_GET['action'] ?? '') === 'calc_fee') {
            $routeId = (int) ($_GET['route_id'] ?? 0);
            $weight = max(0, (float) ($_GET['weight'] ?? 0));
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($orderModel->calculateShipping($routeId, $weight));
            exit;
        }

        if (($_GET['action'] ?? '') === 'find_route') {
            $routeId = $orderModel->findRouteByDistricts(
                trim($_GET['pickup'] ?? ''),
                trim($_GET['delivery'] ?? '')
            );
            $weight = max(0, (float) ($_GET['weight'] ?? 0));
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => $routeId !== null,
                'route_id' => $routeId,
                'fee' => $routeId === null ? ['id' => null, 'amount' => 0] : $orderModel->calculateShipping($routeId, $weight)
            ]);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'customer_id' => $_POST['customer_id'] ?? 0,
                'route_id' => $_POST['route_id'] ?? 0,
                'pickup_phone' => trim($_POST['pickup_phone'] ?? ''),
                'pickup_address' => trim($_POST['pickup_address'] ?? ''),
                'pickup_province' => trim($_POST['pickup_province'] ?? ''),
                'pickup_district' => trim($_POST['pickup_district'] ?? ''),
                'pickup_ward' => trim($_POST['pickup_ward'] ?? ''),
                'delivery_phone' => trim($_POST['delivery_phone'] ?? ''),
                'delivery_name' => trim($_POST['delivery_name'] ?? ''),
                'delivery_address' => trim($_POST['delivery_address'] ?? ''),
                'delivery_province' => trim($_POST['delivery_province'] ?? ''),
                'delivery_district' => trim($_POST['delivery_district'] ?? ''),
                'delivery_ward' => trim($_POST['delivery_ward'] ?? ''),
                'products' => $_POST['products'] ?? [],
                'return_fee' => $_POST['return_fee'] ?? 0,
                'cod_amount' => $_POST['cod_amount'] ?? 0,
                'status' => $_POST['status'] ?? 'Cho xac nhan',
                'cancel_reason' => trim($_POST['cancel_reason'] ?? ''),
                'account_id' => $_SESSION['user_id'] ?? 0
            ];
            $id = (int) ($_POST['id'] ?? 0);
            if ($customerId !== null) {
                $data['customer_id'] = $customerId;
            }
            if ($customerId !== null && $id > 0) {
                $existingOrder = $orderModel->getById($id);
                if (!$existingOrder || (int) $existingOrder['MaKhachHang'] !== (int) $customerId) {
                    $message = 'Bạn không có quyền thao tác trên đơn hàng này.';
                    $messageType = 'error';
                    $id = -1;
                } else {
                    $data['status'] = $existingOrder['TrangThai'];
                    $data['cancel_reason'] = $existingOrder['LyDoHuy'] ?? '';
                }
            }
            $hasValidProducts = is_array($data['products']) && !empty($data['products']);
            if ($hasValidProducts) {
                foreach ($data['products'] as $product) {
                    if (trim($product['name'] ?? '') === '' || (int) ($product['quantity'] ?? 0) <= 0
                        || (float) ($product['product_weight'] ?? -1) < 0 || (float) ($product['unit_price'] ?? -1) < 0) {
                        $hasValidProducts = false;
                        break;
                    }
                }
            }
            if ($message === '' && ((int) $data['customer_id'] <= 0 || (int) $data['route_id'] <= 0
                || $data['pickup_phone'] === '' || $data['pickup_address'] === '' || $data['pickup_province'] === ''
                || $data['pickup_district'] === '' || $data['delivery_phone'] === '' || $data['delivery_name'] === ''
                || $data['delivery_address'] === '' || $data['delivery_province'] === '' || $data['delivery_district'] === ''
                || !$hasValidProducts || (float) $data['return_fee'] < 0)) {
                $message = 'Vui lòng nhập đầy đủ thông tin đơn hàng hợp lệ.';
                $messageType = 'error';
            } elseif ($message === '' && $data['status'] === 'Da huy' && $data['cancel_reason'] === '') {
                $message = 'Vui lòng nhập lý do hủy đơn.';
                $messageType = 'error';
            } elseif ($message === '') {
                $result = $id > 0 ? $orderModel->update($id, $data) : $orderModel->create($data);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
        }

        if (isset($_GET['action'], $_GET['id'])) {
            $id = (int) $_GET['id'];
            $existingOrder = $orderModel->getById($id);
            if ($customerId !== null && (!$existingOrder || (int) $existingOrder['MaKhachHang'] !== (int) $customerId)) {
                $result = ['success' => false, 'message' => 'Bạn không có quyền thao tác trên đơn hàng này.'];
            } elseif ($_GET['action'] === 'cancel') {
                $result = $orderModel->cancel($id, trim($_GET['reason'] ?? ''), $_SESSION['user_id'] ?? 0);
            } elseif ($_GET['action'] === 'receive' && in_array($role, [2, 4], true)) {
                $result = $orderModel->receive($id, $_SESSION['user_id'] ?? 0);
            } elseif ($_GET['action'] === 'queue' && in_array($role, [2, 4], true)) {
                $result = $orderModel->queueForAssign($id, $_SESSION['user_id'] ?? 0);
            } elseif ($_GET['action'] === 'delete' && $role !== 1) {
                $result = $orderModel->delete($id);
            } elseif ($_GET['action'] === 'delete') {
                $result = ['success' => false, 'message' => 'Khách hàng chỉ được hủy đơn theo trạng thái cho phép.'];
            }
            if (isset($result)) {
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $messageType === 'success') {
            header('Location: index.php?page=donhang');
            exit;
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $orders = $orderModel->getAll($keyword, $status, $customerId)->fetchAll(PDO::FETCH_ASSOC);
        $options = $orderModel->getFormOptions($customerId);
        $editOrder = isset($_GET['edit']) ? $orderModel->getById($_GET['edit']) : null;
        $viewOrder = isset($_GET['view']) ? $orderModel->getById($_GET['view']) : null;
        if ($customerId !== null) {
            if ($editOrder && (int) $editOrder['MaKhachHang'] !== (int) $customerId) {
                $editOrder = null;
            }
            if ($viewOrder && (int) $viewOrder['MaKhachHang'] !== (int) $customerId) {
                $viewOrder = null;
            }
        }

        require_once 'views/donhang.php';
    }
}
?>
