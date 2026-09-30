<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý điểm nhận/giao - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
<?php include 'views/menu.php'; ?>
<main class="main-content">
    <header class="topbar">
        <div class="search-bar">
            <form action="index.php" method="GET" style="display:flex;width:100%;">
                <input type="hidden" name="page" value="diadiem">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="keyword" placeholder="Tìm địa chỉ, khu vực, SĐT..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
            </form>
        </div>
        <div class="topbar-right">
            <div class="user-dropdown">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User">
                <span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Khách'); ?>!</span>
            </div>
        </div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h1 class="page-title">Quản lý điểm nhận & điểm giao</h1>
                <p class="page-subtitle">Địa chỉ lấy hàng và giao hàng trong hệ thống</p>
            </div>
            <div class="header-actions">
                <?php if ($tab === 'pickups'): ?>
                <button class="btn btn-primary" onclick="openPickupModal()"><i class="fas fa-plus"></i> Thêm điểm nhận</button>
                <?php else: ?>
                <button class="btn btn-primary" onclick="openDeliveryModal()"><i class="fas fa-plus"></i> Thêm điểm giao</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message !== ''): ?>
        <div class="alert-message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Tab nav -->
        <div style="display:flex;gap:4px;margin-bottom:16px;border-bottom:2px solid var(--border-color);">
            <a href="index.php?page=diadiem&tab=pickups&keyword=<?php echo urlencode($_GET['keyword'] ?? ''); ?>"
               style="padding:10px 20px;font-weight:600;border-radius:6px 6px 0 0;text-decoration:none;
                      <?php echo $tab === 'pickups' ? 'background:var(--primary);color:#fff;' : 'color:var(--text-secondary);'; ?>">
                <i class="fas fa-map-pin"></i> Điểm nhận (<?php echo count($pickups); ?>)
            </a>
            <a href="index.php?page=diadiem&tab=deliveries&keyword=<?php echo urlencode($_GET['keyword'] ?? ''); ?>"
               style="padding:10px 20px;font-weight:600;border-radius:6px 6px 0 0;text-decoration:none;
                      <?php echo $tab === 'deliveries' ? 'background:var(--primary);color:#fff;' : 'color:var(--text-secondary);'; ?>">
                <i class="fas fa-map-marker-alt"></i> Điểm giao (<?php echo count($deliveries); ?>)
            </a>
        </div>

        <?php if ($tab === 'pickups'): ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr>
                            <th>Mã</th><th>Địa chỉ</th><th>Khu vực</th><th>Tỉnh/Thành</th>
                            <th>SĐT</th><th>Số đơn</th><th>Ghi chú</th><th class="text-center">Thao tác</th>
                        </tr></thead>
                        <tbody>
                        <?php if (empty($pickups)): ?>
                            <tr><td colspan="8" class="text-center">Không có điểm nhận nào.</td></tr>
                        <?php else: ?>
                        <?php foreach ($pickups as $p): ?>
                            <tr>
                                <td>#<?php echo $p['MaDiemNhan']; ?></td>
                                <td class="font-medium" style="max-width:200px;"><?php echo htmlspecialchars($p['DiaChi']); ?></td>
                                <td><span class="badge-pill status-blue"><?php echo htmlspecialchars($p['KhuVuc'] ?? ''); ?></span></td>
                                <td><?php echo htmlspecialchars($p['TinhThanh'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['SoDienThoai'] ?? ''); ?></td>
                                <td class="text-center"><?php echo (int)$p['SoDonHang']; ?></td>
                                <td><?php echo htmlspecialchars($p['GhiChu'] ?? ''); ?></td>
                                <td class="text-center">
                                    <button class="btn-icon text-primary" onclick="openPickupModal(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES); ?>)"><i class="fas fa-edit"></i></button>
                                    <a href="index.php?page=diadiem&action=delete_pickup&id=<?php echo $p['MaDiemNhan']; ?>&tab=pickups"
                                       class="btn-icon text-danger" onclick="return confirm('Xóa điểm nhận này?')"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr>
                            <th>Mã</th><th>Người nhận</th><th>SĐT</th><th>Địa chỉ</th>
                            <th>Khu vực</th><th>Tỉnh/Thành</th><th>Số đơn</th><th class="text-center">Thao tác</th>
                        </tr></thead>
                        <tbody>
                        <?php if (empty($deliveries)): ?>
                            <tr><td colspan="8" class="text-center">Không có điểm giao nào.</td></tr>
                        <?php else: ?>
                        <?php foreach ($deliveries as $d): ?>
                            <tr>
                                <td>#<?php echo $d['MaDiemGiao']; ?></td>
                                <td class="font-medium"><?php echo htmlspecialchars($d['TenNguoiNhan']); ?></td>
                                <td><?php echo htmlspecialchars($d['SoDienThoai'] ?? ''); ?></td>
                                <td style="max-width:200px;"><?php echo htmlspecialchars($d['DiaChi']); ?></td>
                                <td><span class="badge-pill status-green"><?php echo htmlspecialchars($d['KhuVuc'] ?? ''); ?></span></td>
                                <td><?php echo htmlspecialchars($d['TinhThanh'] ?? ''); ?></td>
                                <td class="text-center"><?php echo (int)$d['SoDonHang']; ?></td>
                                <td class="text-center">
                                    <button class="btn-icon text-primary" onclick="openDeliveryModal(<?php echo htmlspecialchars(json_encode($d), ENT_QUOTES); ?>)"><i class="fas fa-edit"></i></button>
                                    <a href="index.php?page=diadiem&action=delete_delivery&id=<?php echo $d['MaDiemGiao']; ?>&tab=deliveries"
                                       class="btn-icon text-danger" onclick="return confirm('Xóa điểm giao này?')"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Điểm Nhận -->
<div class="customer-modal" id="pickupModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closePickupModal()"></div>
    <section class="customer-modal-dialog" role="dialog">
        <div class="customer-modal-header">
            <div><h2 id="pickupModalTitle">Thêm điểm nhận</h2><p>Thông tin địa điểm lấy hàng</p></div>
            <button class="modal-close" type="button" onclick="closePickupModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=diadiem" id="pickupForm">
            <input type="hidden" name="form_type" value="pickup">
            <input type="hidden" name="id" id="pickupId" value="">
            <div class="customer-form-grid">
                <div class="input-group customer-form-wide"><label>Địa chỉ <span>*</span></label><input name="dia_chi" id="pickupDiaChi" required></div>
                <div class="input-group"><label>Khu vực</label><input name="khu_vuc" id="pickupKhuVuc"></div>
                <div class="input-group"><label>Tỉnh/Thành</label><input name="tinh_thanh" id="pickupTinhThanh"></div>
                <div class="input-group"><label>Quận/Huyện</label><input name="quan_huyen" id="pickupQuanHuyen"></div>
                <div class="input-group"><label>Phường/Xã</label><input name="phuong_xa" id="pickupPhuongXa"></div>
                <div class="input-group"><label>Số điện thoại</label><input name="sdt" id="pickupSdt" type="tel"></div>
                <div class="input-group customer-form-wide"><label>Ghi chú</label><input name="ghi_chu" id="pickupGhiChu"></div>
            </div>
            <div class="customer-modal-footer">
                <button class="btn btn-outline" type="button" onclick="closePickupModal()">Hủy</button>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu điểm nhận</button>
            </div>
        </form>
    </section>
</div>

<!-- Modal Điểm Giao -->
<div class="customer-modal" id="deliveryModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closeDeliveryModal()"></div>
    <section class="customer-modal-dialog" role="dialog">
        <div class="customer-modal-header">
            <div><h2 id="deliveryModalTitle">Thêm điểm giao</h2><p>Thông tin người nhận và địa điểm giao hàng</p></div>
            <button class="modal-close" type="button" onclick="closeDeliveryModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=diadiem" id="deliveryForm">
            <input type="hidden" name="form_type" value="delivery">
            <input type="hidden" name="id" id="deliveryId" value="">
            <div class="customer-form-grid">
                <div class="input-group"><label>Tên người nhận <span>*</span></label><input name="ten_nguoi_nhan" id="deliveryTen" required></div>
                <div class="input-group"><label>Số điện thoại</label><input name="sdt" id="deliverySdt" type="tel"></div>
                <div class="input-group customer-form-wide"><label>Địa chỉ <span>*</span></label><input name="dia_chi" id="deliveryDiaChi" required></div>
                <div class="input-group"><label>Khu vực</label><input name="khu_vuc" id="deliveryKhuVuc"></div>
                <div class="input-group"><label>Tỉnh/Thành</label><input name="tinh_thanh" id="deliveryTinhThanh"></div>
                <div class="input-group"><label>Quận/Huyện</label><input name="quan_huyen" id="deliveryQuanHuyen"></div>
                <div class="input-group"><label>Phường/Xã</label><input name="phuong_xa" id="deliveryPhuongXa"></div>
                <div class="input-group customer-form-wide"><label>Ghi chú</label><input name="ghi_chu" id="deliveryGhiChu"></div>
            </div>
            <div class="customer-modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeDeliveryModal()">Hủy</button>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu điểm giao</button>
            </div>
        </form>
    </section>
</div>

<script src="assets/js/script.js"></script>
<script>
function openPickupModal(data) {
    const m = document.getElementById('pickupModal'); m.setAttribute('aria-hidden','false');
    if (data) {
        document.getElementById('pickupModalTitle').textContent = 'Sửa điểm nhận';
        document.getElementById('pickupId').value        = data.MaDiemNhan;
        document.getElementById('pickupDiaChi').value    = data.DiaChi || '';
        document.getElementById('pickupKhuVuc').value    = data.KhuVuc || '';
        document.getElementById('pickupTinhThanh').value = data.TinhThanh || '';
        document.getElementById('pickupQuanHuyen').value = data.QuanHuyen || '';
        document.getElementById('pickupPhuongXa').value  = data.PhuongXa || '';
        document.getElementById('pickupSdt').value       = data.SoDienThoai || '';
        document.getElementById('pickupGhiChu').value    = data.GhiChu || '';
    } else {
        document.getElementById('pickupModalTitle').textContent = 'Thêm điểm nhận';
        document.getElementById('pickupForm').reset();
        document.getElementById('pickupId').value = '';
    }
    document.body.style.overflow = 'hidden';
}
function closePickupModal() { document.getElementById('pickupModal').setAttribute('aria-hidden','true'); document.body.style.overflow=''; }

function openDeliveryModal(data) {
    const m = document.getElementById('deliveryModal'); m.setAttribute('aria-hidden','false');
    if (data) {
        document.getElementById('deliveryModalTitle').textContent = 'Sửa điểm giao';
        document.getElementById('deliveryId').value        = data.MaDiemGiao;
        document.getElementById('deliveryTen').value       = data.TenNguoiNhan || '';
        document.getElementById('deliverySdt').value       = data.SoDienThoai || '';
        document.getElementById('deliveryDiaChi').value    = data.DiaChi || '';
        document.getElementById('deliveryKhuVuc').value    = data.KhuVuc || '';
        document.getElementById('deliveryTinhThanh').value = data.TinhThanh || '';
        document.getElementById('deliveryQuanHuyen').value = data.QuanHuyen || '';
        document.getElementById('deliveryPhuongXa').value  = data.PhuongXa || '';
        document.getElementById('deliveryGhiChu').value    = data.GhiChu || '';
    } else {
        document.getElementById('deliveryModalTitle').textContent = 'Thêm điểm giao';
        document.getElementById('deliveryForm').reset();
        document.getElementById('deliveryId').value = '';
    }
    document.body.style.overflow = 'hidden';
}
function closeDeliveryModal() { document.getElementById('deliveryModal').setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
</script>
</body>
</html>
