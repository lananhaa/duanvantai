<?php
$role = (int) ($_SESSION['role_id'] ?? 0);
$isDriver = $role === 3;
$canManageDriverOrders = $isDriver || $role === 4;
$isCustomer = $role === 1;
$pageTitle = $isDriver ? 'Đơn hàng được giao' : ($isCustomer ? 'Theo dõi đơn hàng của tôi' : 'Theo dõi trạng thái đơn hàng');
$statusLabels = [
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
$allowedTransitions = [
    'Da phan cong' => ['Da nhan hang'],
    'Dang phan cong' => ['Da nhan hang'],
    'Da nhan hang' => ['Dang van chuyen'],
    'Dang van chuyen' => ['Dang giao hang'],
    'Dang giao hang' => ['Da giao hang', 'Giao khong thanh cong'],
    'Giao khong thanh cong' => ['Dang giao hang', 'Hoan hang'],
    'Da giao hang' => ['Hoan tat']
];
$statusClass = [
    'Da phan cong' => 'driver-status-pending', 'Dang phan cong' => 'driver-status-pending',
    'Da nhan hang' => 'driver-status-progress', 'Dang van chuyen' => 'driver-status-progress',
    'Dang giao hang' => 'driver-status-progress', 'Giao khong thanh cong' => 'driver-status-warning',
    'Da giao hang' => 'driver-status-success', 'Hoan tat' => 'driver-status-success',
    'Hoan hang' => 'driver-status-warning', 'Da huy' => 'driver-status-cancel'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php include 'views/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="search-bar"><form action="index.php" method="GET" class="driver-order-search"><input type="hidden" name="page" value="tracking"><i class="fas fa-search search-icon"></i><input name="keyword" placeholder="Tìm mã đơn, người nhận, địa chỉ..." value="<?php echo htmlspecialchars($keyword); ?>"></form></div>
            <div class="topbar-right"><div class="user-dropdown"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'Driver'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="Tài xế"><span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Tài xế'); ?>!</span></div></div>
        </header>
        <div class="content-area driver-page">
            <div class="page-header"><div><h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1><p class="page-subtitle"><?php echo $isDriver ? 'Theo dõi hành trình và cập nhật tình trạng giao nhận' : ($isCustomer ? 'Theo dõi tiến trình các đơn do bạn tạo' : 'Tra cứu trạng thái và lịch sử giao nhận toàn hệ thống'); ?></p></div></div>
            <?php if ($flash): ?><div class="alert-message <?php echo $flash['success'] ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($flash['message']); ?></div><?php endif; ?>

            <?php if ($isDriver): ?><section class="driver-location-bar">
                <div><span class="driver-section-label">Vị trí hiện tại</span><strong><?php echo htmlspecialchars($currentLocation ?: 'Chưa cập nhật'); ?></strong></div>
                <form method="POST" action="index.php?page=tracking" class="driver-location-form">
                    <input type="hidden" name="action" value="location"><input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                    <label class="sr-only" for="driverLocation">Cập nhật khu vực</label><input id="driverLocation" name="location" maxlength="100" placeholder="Nhập khu vực hiện tại" required>
                    <button class="btn btn-primary" type="submit"><i class="fas fa-location-dot"></i> Cập nhật vị trí</button>
                </form>
            </section><?php endif; ?>

            <div class="driver-orders-heading"><h2>Danh sách đơn <span><?php echo count($orders); ?></span></h2><span><?php echo $isDriver ? 'Chỉ đơn được phân công cho tài khoản tài xế này' : ($isCustomer ? 'Chỉ đơn hàng thuộc tài khoản của bạn' : 'Phạm vi dữ liệu: toàn hệ thống'); ?></span></div>
            <?php if (!$orders): ?><div class="card"><div class="card-body text-center"><?php echo $isDriver ? 'Chưa có đơn hàng được phân công cho bạn.' : ($isCustomer ? 'Tài khoản của bạn chưa có đơn hàng.' : 'Không có đơn hàng phù hợp.'); ?></div></div><?php endif; ?>

            <div class="driver-order-table-wrap">
                <table class="driver-order-table">
                    <thead><tr><th>Đơn hàng</th><th>Điểm giao</th><th>Hàng hóa</th><th>Trạng thái</th><th>COD</th><th>Thao tác</th></tr></thead>
                    <tbody>
                <?php foreach ($orders as $order):
                    $currentStatus = $order['TrangThai'];
                    $nextStatuses = $allowedTransitions[$currentStatus] ?? [];
                    $history = $historyByOrder[$order['MaDonHang']] ?? [];
                    $isActiveAssignment = !in_array($order['TrangThaiPhanCong'], ['Hoan thanh', 'Hoan tat', 'Da huy'], true)
                        && !in_array($currentStatus, ['Hoan tat', 'Hoan hang', 'Da huy'], true);
                ?>
                <tr class="driver-order-row" data-detail-url="index.php?page=tracking&view=<?php echo (int) $order['MaDonHang']; ?>">
                    <td data-label="Đơn hàng"><a class="driver-order-link" href="index.php?page=tracking&view=<?php echo (int) $order['MaDonHang']; ?>"><strong class="driver-order-number">#<?php echo (int) $order['MaDonHang']; ?></strong><small class="driver-table-muted"><?php echo number_format((float) $order['TongKhoiLuong'], 1, ',', '.'); ?> kg</small></a></td>
                    <td data-label="Điểm giao"><strong><?php echo htmlspecialchars($order['TenNguoiNhan'] ?? ''); ?> · <?php echo htmlspecialchars($order['SDTNhan'] ?? ''); ?></strong><span class="driver-table-muted"><?php echo htmlspecialchars($order['DiaChiGiao'] ?? ''); ?></span><span class="driver-table-muted"><?php echo htmlspecialchars($order['KhuVucGiao'] ?? ''); ?></span></td>
                    <td data-label="Hàng hóa"><span><?php echo htmlspecialchars($order['HangHoa'] ?? 'Chưa có hàng hóa'); ?></span><span class="driver-table-muted">Nhận: <?php echo htmlspecialchars($order['DiaChiNhan'] ?? ''); ?></span></td>
                    <td data-label="Trạng thái"><span class="driver-status <?php echo $statusClass[$currentStatus] ?? 'driver-status-pending'; ?>"><?php echo htmlspecialchars($statusLabels[$currentStatus] ?? $currentStatus); ?></span></td>
                    <td data-label="COD"><strong><?php echo number_format((float) ($order['SoTienCOD'] ?? 0), 0, ',', '.'); ?>đ</strong><span class="driver-table-muted"><?php echo htmlspecialchars($order['TrangThaiCOD'] ?? 'Không có COD'); ?></span></td>
                    <td data-label="Thao tác" class="driver-table-actions">
                        <?php if (!$canManageDriverOrders): ?><span class="driver-readonly-note"><i class="fas fa-lock"></i> Chỉ tài xế hoặc admin được chuyển trạng thái</span><?php endif; ?>
                        <?php if ($canManageDriverOrders && $isActiveAssignment && $nextStatuses): ?>
                        <form method="POST" action="index.php?page=tracking" class="driver-status-form">
                            <input type="hidden" name="action" value="status"><input type="hidden" name="order_id" value="<?php echo (int) $order['MaDonHang']; ?>"><input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                            <div class="driver-next-statuses"><span class="driver-action-label">Chuyển đổi trạng thái</span><?php foreach ($nextStatuses as $nextStatus): ?><button class="btn btn-primary" type="submit" name="status" value="<?php echo htmlspecialchars($nextStatus); ?>"><i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($statusLabels[$nextStatus] ?? $nextStatus); ?></button><?php endforeach; ?></div>
                            <label class="driver-note-field">Ghi chú / lý do<textarea name="note" rows="1" placeholder="Lý do nếu giao thất bại"></textarea></label>
                        </form>
                        <?php elseif ($canManageDriverOrders && !$isActiveAssignment): ?><span class="driver-completed"><i class="fas fa-circle-check"></i> Đã hoàn thành</span><?php endif; ?>

                        <?php if ($canManageDriverOrders && !empty($order['SoTienCOD']) && in_array($order['TrangThai'], ['Da giao hang', 'Hoan tat'], true)): ?>
                        <form method="POST" action="index.php?page=tracking" class="driver-cod-form">
                            <input type="hidden" name="action" value="cod"><input type="hidden" name="order_id" value="<?php echo (int) $order['MaDonHang']; ?>"><input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                            <label>Thu COD<select name="cod_status"><option value="Da thu" <?php echo ($order['TrangThaiCOD'] ?? '') === 'Da thu' ? 'selected' : ''; ?>>Đã thu đủ</option><option value="Chua thu" <?php echo ($order['TrangThaiCOD'] ?? '') === 'Chua thu' ? 'selected' : ''; ?>>Chưa thu</option><option value="Khong thu" <?php echo ($order['TrangThaiCOD'] ?? '') === 'Khong thu' ? 'selected' : ''; ?>>Không thu được</option></select></label>
                            <label class="driver-note-field">Ghi chú COD<input name="cod_note" value="<?php echo htmlspecialchars($order['GhiChuCOD'] ?? ''); ?>" placeholder="Ghi nhận thu tiền"></label>
                            <button class="btn btn-outline" type="submit"><i class="fas fa-coins"></i> Lưu COD</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($isCustomer && in_array($order['TrangThai'], ['Cho xac nhan', 'Da xac nhan', 'Cho phan cong'], true)): ?>
                        <form method="POST" action="index.php?page=tracking" class="driver-cancel-form" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?');">
                            <input type="hidden" name="action" value="cancel"><input type="hidden" name="order_id" value="<?php echo (int) $order['MaDonHang']; ?>"><input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                            <label>Lý do hủy<input name="reason" maxlength="255" required placeholder="Nhập lý do hủy đơn"></label><button class="btn btn-outline text-danger" type="submit"><i class="fas fa-ban"></i> Hủy đơn</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="assets/js/script.js"></script>
</body>
</html>