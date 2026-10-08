<?php
require_once 'models/donhang_model.php';

class OrderController {
    public function index() {
        $role = (int) ($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [1, 2, 4], true)) {
            echo 'Bạn không có quyền truy cập trang này.';
            exit;
        }

        if (isset($_GET['edit'])) {
            header('Location: index.php?page=taodonhang&edit=' . (int) $_GET['edit']);
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

        $isApprovalRequest = $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'approve';
        if ($isApprovalRequest) {
            $id = (int) ($_POST['order_id'] ?? 0);
            if (!in_array($role, [2, 4], true)) {
                $message = 'Bạn không có quyền duyệt đơn hàng.';
                $messageType = 'error';
            } else {
                $result = $orderModel->approveForAssign($id, $_SESSION['user_id'] ?? 0);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $redirect = $isApprovalRequest
                ? 'index.php?page=donhang&view=' . (int) ($_POST['order_id'] ?? 0)
                : 'index.php?page=donhang';
            header('Location: ' . $redirect);
            exit;
        }

        $detailId = isset($_GET['view'])
            ? (int) $_GET['view']
            : ($isApprovalRequest ? (int) ($_POST['order_id'] ?? 0) : 0);
        if ($detailId > 0) {
            $orderDetail = $orderModel->getById($detailId);
            if (!$orderDetail || ($customerId !== null && (int) $orderDetail['MaKhachHang'] !== (int) $customerId)) {
                http_response_code(404);
                echo 'Không tìm thấy đơn hàng hoặc bạn không có quyền xem.';
                return;
            }
            $orderHistory = $orderModel->getHistory($detailId);
            require_once 'views/chitietdonhang.php';
            return;
        }

        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $orders = $orderModel->getAll($keyword, $status, $customerId)->fetchAll(PDO::FETCH_ASSOC);
        $options = $orderModel->getFormOptions($customerId);
        $editOrder = $_SERVER['REQUEST_METHOD'] === 'POST' && (int) ($_POST['id'] ?? 0) > 0
            ? $orderModel->getById((int) $_POST['id'])
            : null;
        $viewOrder = null;
        if ($customerId !== null) {
            if ($editOrder && (int) $editOrder['MaKhachHang'] !== (int) $customerId) {
                $editOrder = null;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $messageType === 'error') {
            if ((int) ($_POST['id'] ?? 0) > 0 && !$editOrder) {
                require_once 'views/donhang.php';
                return;
            }
            $submittedOrder = $_POST;
            require_once 'views/taodonhang.php';
            return;
        }

        require_once 'views/donhang.php';
    }

    public function create() {
        $role = (int) ($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [1, 2, 4], true)) {
            echo 'Bạn không có quyền truy cập trang này.';
            exit;
        }

        $orderModel = new OrderModel();
        $customerId = $role === 1 ? $orderModel->getCustomerIdByAccount($_SESSION['user_id'] ?? 0) : null;
        $editOrder = null;
        $message = '';
        $messageType = 'error';
        if (isset($_GET['edit'])) {
            $editOrder = $orderModel->getById((int) $_GET['edit']);
            if (!$editOrder || ($customerId !== null && (int) $editOrder['MaKhachHang'] !== (int) $customerId)) {
                http_response_code(404);
                echo 'Không tìm thấy đơn hàng hoặc bạn không có quyền thao tác.';
                return;
            }
        }
        $options = $orderModel->getFormOptions($customerId);

        require_once 'views/taodonhang.php';
    }
}
?>
