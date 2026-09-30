<?php
function app_status_labels() {
    return [
        'Cho xac nhan' => 'Chờ xác nhận',
        'Da xac nhan' => 'Đã xác nhận',
        'Cho phan cong' => 'Chờ phân công',
        'Da phan cong' => 'Đã phân công',
        'Dang phan cong' => 'Đang phân công',
        'Da nhan hang' => 'Đã nhận hàng',
        'Dang van chuyen' => 'Đang vận chuyển',
        'Dang giao hang' => 'Đang giao hàng',
        'Da giao hang' => 'Đã giao hàng',
        'Giao khong thanh cong' => 'Giao không thành công',
        'Hoan tat' => 'Hoàn tất',
        'Hoan hang' => 'Hoàn hàng',
        'Da huy' => 'Đã hủy'
    ];
}

function app_status_label($status) {
    $labels = app_status_labels();
    return $labels[$status] ?? $status;
}

function app_status_class($status) {
    $map = [
        'Cho xac nhan' => 'driver-status-pending',
        'Da xac nhan' => 'driver-status-pending',
        'Cho phan cong' => 'driver-status-pending',
        'Da phan cong' => 'driver-status-pending',
        'Dang phan cong' => 'driver-status-pending',
        'Da nhan hang' => 'driver-status-progress',
        'Dang van chuyen' => 'driver-status-progress',
        'Dang giao hang' => 'driver-status-progress',
        'Giao khong thanh cong' => 'driver-status-warning',
        'Da giao hang' => 'driver-status-success',
        'Hoan tat' => 'driver-status-success',
        'Hoan hang' => 'driver-status-warning',
        'Da huy' => 'driver-status-cancel'
    ];
    return $map[$status] ?? 'driver-status-pending';
}

function app_cancel_statuses() {
    return ['Cho xac nhan', 'Da xac nhan', 'Cho phan cong'];
}

function app_require_roles(array $roles) {
    $role = (int) ($_SESSION['role_id'] ?? 0);
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        echo '<div style="padding:40px;text-align:center;color:#991b1b;"><h2>Từ chối truy cập</h2><p>Bạn không có quyền sử dụng chức năng này.</p><a href="index.php?page=trangchu">Quay lại trang chủ</a></div>';
        exit;
    }
    return $role;
}

function app_flash_redirect($page, $result, $extra = '') {
    $_SESSION['app_flash'] = $result;
    header('Location: index.php?page=' . $page . $extra);
    exit;
}

function app_take_flash() {
    $flash = $_SESSION['app_flash'] ?? null;
    unset($_SESSION['app_flash']);
    return $flash;
}

function app_role() {
    return (int) ($_SESSION['role_id'] ?? 0);
}
