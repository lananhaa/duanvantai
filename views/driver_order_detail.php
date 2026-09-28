<?php
$role = (int) ($_SESSION['role_id'] ?? 0);
$isDriver = $role === 3;
$canManageDriverOrders = $isDriver || $role === 4;
$statusLabels = [
    'Da phan cong' => 'Đã phân công', 'Dang phan cong' => 'Đang phân công',
    'Da nhan hang' => 'Đã nhận hàng', 'Dang van chuyen' => 'Đang vận chuyển',
    'Dang giao hang' => 'Đang giao hàng', 'Da giao hang' => 'Đã giao hàng',
    'Giao khong thanh cong' => 'Giao không thành công', 'Hoan tat' => 'Hoàn tất',
    'Hoan hang' => 'Hoàn hàng', 'Da huy' => 'Đã hủy'
];
$statusClass = [
    'Da phan cong' => 'driver-status-pending', 'Dang phan cong' => 'driver-status-pending',
    'Da nhan hang' => 'driver-status-progress', 'Dang van chuyen' => 'driver-status-progress',
    'Dang giao hang' => 'driver-status-progress', 'Giao khong thanh cong' => 'driver-status-warning',
    'Da giao hang' => 'driver-status-success', 'Hoan tat' => 'driver-status-success',
    'Hoan hang' => 'driver-status-warning', 'Da huy' => 'driver-status-cancel'
];
$currentStatus = $detailOrder['TrangThai'];
$nextStatuses = [
    'Da phan cong' => ['Da nhan hang'], 'Dang phan cong' => ['Da nhan hang'],
    'Da nhan hang' => ['Dang van chuyen'], 'Dang van chuyen' => ['Dang giao hang'],
    'Dang giao hang' => ['Da giao hang', 'Giao khong thanh cong'],
    'Giao khong thanh cong' => ['Dang giao hang', 'Hoan hang'], 'Da giao hang' => ['Hoan tat']
][$currentStatus] ?? [];
$isActiveAssignment = !in_array($detailOrder['TrangThaiPhanCong'], ['Hoan thanh', 'Hoan tat', 'Da huy'], true)
    && !in_array($currentStatus, ['Hoan tat', 'Hoan hang', 'Da huy'], true);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php include 'views/sidebar.php'; ?>
    <main class="main-content">
        <header class="topbar"><div class="search-bar"><a class="detail-back-link" href="index.php?page=tracking"><i class="fas fa-arrow-left"></i> Danh sách đơn</a></div><div class="topbar-right"><div class="user-dropdown"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="Người dùng"><span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>!</span></div></div></header>
        <div class="content-area driver-page driver-detail-page">
            <div class="page-header"><div><p class="detail-eyebrow">CHI TIẾT ĐƠN HÀNG</p><h1 class="page-title">Đơn #<?php echo (int) $detailOrder['MaDonHang']; ?></h1><p class="page-subtitle">Tạo lúc <?php echo date('d/m/Y H:i', strtotime($detailOrder['NgayTao'])); ?></p></div><span class="driver-status <?php echo $statusClass[$currentStatus] ?? 'driver-status-pending'; ?>"><?php echo htmlspecialchars($statusLabels[$currentStatus] ?? $currentStatus); ?></span></div>
            <div class="driver-detail-grid">
                <section class="driver-detail-panel"><h2><i class="fas fa-route"></i> Lộ trình giao hàng</h2><div class="detail-route"><div><span class="route-dot pickup-dot"></span><div><small>ĐIỂM NHẬN</small><strong><?php echo htmlspecialchars($detailOrder['DiaChiNhan'] ?? ''); ?></strong><span><?php echo htmlspecialchars($detailOrder['KhuVucNhan'] ?? ''); ?></span></div></div><div><span class="route-dot delivery-dot"></span><div><small>ĐIỂM GIAO</small><strong><?php echo htmlspecialchars($detailOrder['TenNguoiNhan'] ?? ''); ?> · <?php echo htmlspecialchars($detailOrder['SDTNhan'] ?? ''); ?></strong><span><?php echo htmlspecialchars($detailOrder['DiaChiGiao'] ?? ''); ?> · <?php echo htmlspecialchars($detailOrder['KhuVucGiao'] ?? ''); ?></span></div></div></div></section>
                <section class="driver-detail-panel"><h2><i class="fas fa-box"></i> Hàng hóa và thanh toán</h2><dl class="detail-facts"><dt>Hàng hóa</dt><dd><?php echo htmlspecialchars($detailOrder['HangHoa'] ?? 'Chưa có hàng hóa'); ?></dd><dt>Khối lượng</dt><dd><?php echo number_format((float) $detailOrder['TongKhoiLuong'], 1, ',', '.'); ?> kg</dd><dt>COD</dt><dd><?php echo number_format((float) ($detailOrder['SoTienCOD'] ?? 0), 0, ',', '.'); ?>đ · <?php echo htmlspecialchars($detailOrder['TrangThaiCOD'] ?? 'Không có COD'); ?></dd></dl></section>
            </div>
            <section class="driver-detail-panel driver-detail-history"><h2><i class="fas fa-clock-rotate-left"></i> Lịch sử và lý do</h2><ol class="detail-history-list"><?php foreach ($detailHistory as $item): ?><li><span class="driver-status <?php echo $statusClass[$item['TrangThai']] ?? 'driver-status-pending'; ?>"><?php echo htmlspecialchars($statusLabels[$item['TrangThai']] ?? $item['TrangThai']); ?></span><time><?php echo date('d/m/Y H:i', strtotime($item['ThoiGian'])); ?></time><?php if (!empty($item['GhiChu'])): ?><p><?php echo htmlspecialchars($item['GhiChu']); ?></p><?php endif; ?></li><?php endforeach; ?></ol></section>
            <?php if ($canManageDriverOrders && $isActiveAssignment && $nextStatuses): ?><section class="driver-detail-panel detail-action-panel"><h2><i class="fas fa-arrows-rotate"></i> Chuyển đổi trạng thái</h2><form method="POST" action="index.php?page=tracking" class="driver-status-form"><input type="hidden" name="action" value="status"><input type="hidden" name="order_id" value="<?php echo (int) $detailOrder['MaDonHang']; ?>"><div class="driver-next-statuses"><?php foreach ($nextStatuses as $nextStatus): ?><button class="btn btn-primary" type="submit" name="status" value="<?php echo htmlspecialchars($nextStatus); ?>"><i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($statusLabels[$nextStatus] ?? $nextStatus); ?></button><?php endforeach; ?></div><label class="driver-note-field">Ghi chú / lý do<textarea name="note" rows="2" placeholder="Bắt buộc nếu giao thất bại hoặc hoàn hàng"></textarea></label><button class="btn btn-outline" type="submit" disabled aria-hidden="true" tabindex="-1" style="display:none">Cập nhật</button></form></section><?php endif; ?>
            <a class="btn btn-outline detail-back-button" href="index.php?page=tracking"><i class="fas fa-arrow-left"></i> Quay lại danh sách</a>
        </div>
    </main>
</body>
</html>
