<?php
$escape = static fn($value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$statusClasses = [
    'Da huy' => 'status-huy',
    'Hoan tat' => 'status-hoan-thanh',
    'Da giao hang' => 'status-hoan-thanh',
    'Dang van chuyen' => 'status-dang-giao',
    'Da nhan hang' => 'status-dang-giao',
];
$canApprove = in_array((int) ($_SESSION['role_id'] ?? 0), [2, 4], true)
    && in_array($orderDetail['TrangThai'], ['Cho xac nhan', 'Da xac nhan'], true);
$address = static function ($street, $ward, $district, $province) use ($escape) {
    return implode(', ', array_filter(array_map($escape, [$street, $ward, $district, $province]), static fn($part) => $part !== ''));
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?php echo (int) $orderDetail['MaDonHang']; ?> - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .order-detail-page { width: 100%; max-width: none; }
    .order-detail-heading { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
    .order-detail-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .order-detail-section { margin-bottom: 20px; }
    .order-detail-section h2 { margin: 0 0 16px; font-size: 16px; }
    .order-detail-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .order-detail-block { min-width: 0; }
    .order-detail-block p { margin: 7px 0; overflow-wrap: anywhere; }
    .order-detail-block strong { color: var(--text-muted); font-weight: 500; }
    .order-detail-notes { white-space: pre-wrap; }
    .order-detail-total { font-size: 17px; font-weight: 700; color: #ee4d2d; }
    .order-history-list { display: grid; gap: 0; }
    .order-history-item { display: grid; grid-template-columns: 165px 1fr; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border-color); }
    .order-history-item:last-child { border-bottom: 0; }
    .order-history-time { color: var(--text-muted); font-size: 13px; }
    @media (max-width: 700px) {
        .order-detail-cards { grid-template-columns: 1fr; }
        .order-history-item { grid-template-columns: 1fr; gap: 4px; }
    }
    </style>
</head>
<body class="dashboard-body">
<?php include 'views/menu.php'; ?>
<main class="main-content">
    <header class="topbar">
        <div class="search-bar"></div>
        <div class="topbar-right">
            <div class="user-dropdown">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User">
                <span class="user-greeting">Xin chào, <?php echo $escape($_SESSION['username'] ?? 'Khách'); ?>!</span>
            </div>
        </div>
    </header>
    <div class="content-area order-detail-page">
        <div class="page-header order-detail-heading">
            <div>
                <h1 class="page-title">Chi tiết đơn hàng #<?php echo (int) $orderDetail['MaDonHang']; ?></h1>
                <p class="page-subtitle">Ngày tạo: <?php echo $escape(date('d/m/Y H:i', strtotime($orderDetail['NgayTao']))); ?></p>
            </div>
            <div class="order-detail-actions">
                <a class="btn btn-outline" href="index.php?page=donhang"><i class="fas fa-arrow-left"></i> Danh sách đơn</a>
                <a class="btn btn-outline" href="index.php?page=taodonhang&edit=<?php echo (int) $orderDetail['MaDonHang']; ?>"><i class="fas fa-edit"></i> Sửa đơn</a>
                <?php if ($canApprove): ?>
                <form method="POST" action="index.php?page=donhang" onsubmit="return confirm('Duyệt đơn và chuyển sang trạng thái chờ phân công?');">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="order_id" value="<?php echo (int) $orderDetail['MaDonHang']; ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-check"></i> Duyệt đơn — Chờ phân công</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message !== ''): ?>
        <div class="alert-message <?php echo $escape($messageType); ?>"><?php echo $escape($message); ?></div>
        <?php endif; ?>

        <section class="card order-detail-section">
            <div class="card-body">
                <h2><i class="fas fa-circle-info"></i> Tổng quan đơn hàng</h2>
                <div class="order-detail-cards">
                    <div class="order-detail-block">
                        <p><strong>Mã đơn:</strong> #<?php echo (int) $orderDetail['MaDonHang']; ?></p>
                        <p><strong>Trạng thái:</strong> <span class="status-badge <?php echo $statusClasses[$orderDetail['TrangThai']] ?? 'status-cho-xu-ly'; ?>"><?php echo $escape($orderDetail['TrangThai']); ?></span></p>
                        <p><strong>Ngày tạo:</strong> <?php echo $escape(date('d/m/Y H:i:s', strtotime($orderDetail['NgayTao']))); ?></p>
                        <p><strong>Người chịu phí:</strong> <?php echo $escape($orderDetail['NguoiChiuPhi'] ?: 'Chưa xác định'); ?></p>
                    </div>
                    <div class="order-detail-block">
                        <p><strong>Mã khách hàng:</strong> <?php echo (int) $orderDetail['MaKhachHang']; ?></p>
                        <p><strong>Mã điểm nhận / điểm giao:</strong> <?php echo (int) $orderDetail['MaDiemNhan']; ?> / <?php echo (int) $orderDetail['MaDiemGiao']; ?></p>
                        <p><strong>Mã tuyến / mức phí:</strong> <?php echo $escape($orderDetail['MaTuyenGiao'] ?: '—'); ?> / <?php echo $escape($orderDetail['MaPhi'] ?: '—'); ?></p>
                    </div>
                    <?php if (!empty($orderDetail['LyDoHuy'])): ?><p class="order-detail-notes"><strong>Lý do hủy:</strong> <?php echo $escape($orderDetail['LyDoHuy']); ?></p><?php endif; ?>
                    <?php if (!empty($orderDetail['LyDoHoan'])): ?><p class="order-detail-notes"><strong>Lý do hoàn:</strong> <?php echo $escape($orderDetail['LyDoHoan']); ?></p><?php endif; ?>
                </div>
            </div>
        </section>

        <div class="order-detail-cards">
            <section class="card order-detail-section">
                <div class="card-body">
                    <h2><i class="fas fa-user"></i> Khách hàng</h2>
                    <div class="order-detail-block">
                        <p><strong>Họ tên:</strong> <?php echo $escape($orderDetail['TenKhachHang']); ?></p>
                        <p><strong>Mã khách hàng:</strong> <?php echo (int) $orderDetail['MaKhachHang']; ?></p>
                        <p><strong>Tài khoản:</strong> <?php echo $escape($orderDetail['TaiKhoanKhachHang']); ?></p>
                        <p><strong>Điện thoại:</strong> <?php echo $escape($orderDetail['SDTKhachHang']); ?></p>
                        <p><strong>Email:</strong> <?php echo $escape($orderDetail['EmailKhachHang']); ?></p>
                        <p><strong>Địa chỉ tài khoản:</strong> <?php echo $escape($orderDetail['DiaChiKhachHang']); ?></p>
                    </div>
                </div>
            </section>
            <section class="card order-detail-section">
                <div class="card-body">
                    <h2><i class="fas fa-route"></i> Tuyến giao</h2>
                    <div class="order-detail-block">
                        <p><strong>Tên tuyến:</strong> <?php echo $escape($orderDetail['TenTuyen'] ?: 'Chưa chọn'); ?></p>
                        <p><strong>Khu vực đi → đến:</strong> <?php echo $escape($orderDetail['KhuVucDi']); ?> → <?php echo $escape($orderDetail['KhuVucDen']); ?></p>
                        <p><strong>Mô tả:</strong> <?php echo $escape($orderDetail['MoTaTuyen']); ?></p>
                        <p><strong>Mức phí:</strong> <?php echo $escape($orderDetail['TenMucPhi'] ?: 'Không lưu mức phí'); ?></p>
                        <p><strong>Khung khối lượng:</strong> <?php echo $orderDetail['KhoiLuongTu'] === null ? '—' : $escape($orderDetail['KhoiLuongTu']); ?> – <?php echo $orderDetail['KhoiLuongDen'] === null ? '—' : $escape($orderDetail['KhoiLuongDen']); ?> kg</p>
                        <p><strong>Phí cơ bản / vượt mức:</strong> <?php echo number_format((float) ($orderDetail['PhiCoBan'] ?? 0), 0, ',', '.'); ?>đ / <?php echo number_format((float) ($orderDetail['PhiVuotKhoiLuong'] ?? 0), 0, ',', '.'); ?>đ/kg</p>
                    </div>
                </div>
            </section>
        </div>

        <div class="order-detail-cards">
            <section class="card order-detail-section">
                <div class="card-body">
                    <h2><i class="fas fa-box-arrow-up"></i> Điểm nhận hàng</h2>
                    <div class="order-detail-block">
                        <p><strong>Điện thoại người gửi:</strong> <?php echo $escape($orderDetail['SDTGoi']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo $escape($address($orderDetail['DiaChiNhan'], $orderDetail['PhuongXaNhan'], $orderDetail['QuanHuyenNhan'], $orderDetail['TinhThanhNhan'])); ?></p>
                        <p><strong>Khu vực:</strong> <?php echo $escape($orderDetail['KhuVucNhan']); ?></p>
                        <p class="order-detail-notes"><strong>Ghi chú:</strong> <?php echo $escape($orderDetail['GhiChuNhan'] ?: 'Không có'); ?></p>
                    </div>
                </div>
            </section>
            <section class="card order-detail-section">
                <div class="card-body">
                    <h2><i class="fas fa-box"></i> Điểm giao hàng</h2>
                    <div class="order-detail-block">
                        <p><strong>Người nhận:</strong> <?php echo $escape($orderDetail['TenNguoiNhan']); ?></p>
                        <p><strong>Điện thoại người nhận:</strong> <?php echo $escape($orderDetail['SDTNhan']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo $escape($address($orderDetail['DiaChiGiao'], $orderDetail['PhuongXaGiao'], $orderDetail['QuanHuyenGiao'], $orderDetail['TinhThanhGiao'])); ?></p>
                        <p><strong>Khu vực:</strong> <?php echo $escape($orderDetail['KhuVucGiao']); ?></p>
                        <p class="order-detail-notes"><strong>Ghi chú:</strong> <?php echo $escape($orderDetail['GhiChuGiao'] ?: 'Không có'); ?></p>
                    </div>
                </div>
            </section>
        </div>

        <section class="card order-detail-section">
            <div class="card-body">
                <h2><i class="fas fa-boxes-stacked"></i> Hàng hóa trong đơn</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Mã hàng</th><th>Tên hàng</th><th>Loại</th><th>Đơn vị</th><th>SL</th><th>Đơn giá</th><th>KL/đơn vị</th><th>Khối lượng dòng</th><th>Thành tiền</th><th>Mô tả</th></tr></thead>
                        <tbody>
                        <?php if (empty($orderDetail['detail'])): ?>
                            <tr><td colspan="10" class="text-center">Đơn hàng chưa có chi tiết hàng hóa.</td></tr>
                        <?php else: foreach ($orderDetail['detail'] as $item): ?>
                            <tr>
                                <td><?php echo (int) $item['MaHangHoa']; ?></td>
                                <td><?php echo $escape($item['TenHangHoa']); ?></td>
                                <td><?php echo $escape($item['LoaiHang']); ?></td>
                                <td><?php echo $escape($item['DonViTinh']); ?></td>
                                <td><?php echo (int) $item['SoLuong']; ?></td>
                                <td><?php echo number_format((float) $item['DonGia'], 0, ',', '.'); ?>đ</td>
                                <td><?php echo number_format((float) $item['KhoiLuongDonVi'], 2, ',', '.'); ?> kg</td>
                                <td><?php echo number_format((float) $item['KhoiLuong'], 2, ',', '.'); ?> kg</td>
                                <td><?php echo number_format((float) $item['ThanhTien'], 0, ',', '.'); ?>đ</td>
                                <td><?php echo $escape($item['MoTaHangHoa']); ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <div class="order-detail-cards">
            <section class="card order-detail-section">
                <div class="card-body">
                    <h2><i class="fas fa-money-bill-wave"></i> Cước phí</h2>
                    <div class="order-detail-block">
                        <p><strong>Tổng khối lượng:</strong> <?php echo number_format((float) $orderDetail['TongKhoiLuong'], 2, ',', '.'); ?> kg</p>
                        <p><strong>Tiền hàng:</strong> <?php echo number_format((float) $orderDetail['TienHang'], 0, ',', '.'); ?>đ</p>
                        <p><strong>Phí vận chuyển:</strong> <?php echo number_format((float) $orderDetail['PhiVanChuyen'], 0, ',', '.'); ?>đ</p>
                        <p><strong>Phí hoàn:</strong> <?php echo number_format((float) $orderDetail['PhiHoan'], 0, ',', '.'); ?>đ</p>
                        <p class="order-detail-total"><strong>Tổng phí đơn:</strong> <?php echo number_format((float) $orderDetail['TongPhi'], 0, ',', '.'); ?>đ</p>
                    </div>
                </div>
            </section>
            <section class="card order-detail-section">
                <div class="card-body">
                    <h2><i class="fas fa-hand-holding-dollar"></i> Thu hộ COD</h2>
                    <div class="order-detail-block">
                        <p><strong>Số tiền COD:</strong> <?php echo number_format((float) ($orderDetail['SoTienCOD'] ?? 0), 0, ',', '.'); ?>đ</p>
                        <p><strong>Trạng thái:</strong> <?php echo $escape($orderDetail['TrangThaiCOD'] ?: 'Không có COD'); ?></p>
                        <p><strong>Thời gian thu:</strong> <?php echo $orderDetail['ThoiGianThuCOD'] ? $escape(date('d/m/Y H:i:s', strtotime($orderDetail['ThoiGianThuCOD']))) : 'Chưa thu'; ?></p>
                        <p class="order-detail-notes"><strong>Ghi chú:</strong> <?php echo $escape($orderDetail['GhiChuCOD'] ?: 'Không có'); ?></p>
                    </div>
                </div>
            </section>
        </div>

        <section class="card order-detail-section">
            <div class="card-body">
                <h2><i class="fas fa-truck"></i> Phân công vận chuyển</h2>
                <div class="order-detail-cards">
                    <div class="order-detail-block">
                        <p><strong>Mã phân công:</strong> <?php echo $escape($orderDetail['MaPhanCong'] ?: 'Chưa phân công'); ?></p>
                        <p><strong>Trạng thái phân công:</strong> <?php echo $escape($orderDetail['TrangThaiPhanCong'] ?: 'Chưa phân công'); ?></p>
                        <p><strong>Thời gian phân công:</strong> <?php echo $orderDetail['ThoiGianPhanCong'] ? $escape(date('d/m/Y H:i:s', strtotime($orderDetail['ThoiGianPhanCong']))) : 'Chưa phân công'; ?></p>
                        <p><strong>Nhân viên phụ trách:</strong> <?php echo $escape($orderDetail['TenNhanVienPhanCong'] ?: 'Chưa có'); ?></p>
                        <p class="order-detail-notes"><strong>Ghi chú phân công:</strong> <?php echo $escape($orderDetail['GhiChuPhanCong'] ?: 'Không có'); ?></p>
                    </div>
                    <div class="order-detail-block">
                        <p><strong>Mã tài xế:</strong> <?php echo $escape($orderDetail['MaTaiXe'] ?: 'Chưa phân công'); ?></p>
                        <p><strong>Tài xế:</strong> <?php echo $escape($orderDetail['TenTaiXe'] ?: 'Chưa phân công'); ?></p>
                        <p><strong>Tài khoản tài xế:</strong> <?php echo $escape($orderDetail['TaiKhoanTaiXe']); ?></p>
                        <p><strong>Điện thoại tài xế:</strong> <?php echo $escape($orderDetail['SDTTaiXe']); ?></p>
                        <p><strong>Phương tiện:</strong> <?php echo $escape(trim(($orderDetail['BienSo'] ?? '') . ' ' . ($orderDetail['LoaiPhuongTien'] ?? '')) ?: 'Chưa phân công'); ?></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="card order-detail-section">
            <div class="card-body">
                <h2><i class="fas fa-clock-rotate-left"></i> Lịch sử trạng thái</h2>
                <?php if (!$orderHistory): ?>
                    <p>Chưa có lịch sử trạng thái.</p>
                <?php else: ?>
                <div class="order-history-list">
                    <?php foreach ($orderHistory as $history): ?>
                    <div class="order-history-item">
                        <div class="order-history-time"><?php echo $escape(date('d/m/Y H:i:s', strtotime($history['ThoiGian']))); ?></div>
                        <div><strong><?php echo $escape($history['TrangThai']); ?></strong> · <?php echo $escape($history['TenDangNhap'] ?: 'Hệ thống'); ?><br><?php echo $escape($history['GhiChu'] ?: ''); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>
<script src="assets/js/script.js"></script>
</body>
</html>
