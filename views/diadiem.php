<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý điểm nhận/giao - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* CSS Modal Popup */
    .customer-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        visibility: hidden;
        opacity: 0;
        transition: all 0.2s ease-in-out;
    }
    .customer-modal[aria-hidden="false"] {
        visibility: visible;
        opacity: 1;
    }
    .customer-modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(3px);
    }
    .customer-modal-dialog {
        position: relative;
        background: #ffffff;
        width: 90%;
        max-width: 620px;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 10;
        overflow: hidden;
    }
    .customer-modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .customer-modal-header h2 {
        font-size: 1.15rem;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
    }
    .customer-modal-header p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 2px 0 0 0;
    }
    .modal-close {
        background: transparent;
        border: none;
        font-size: 1.2rem;
        color: #94a3b8;
        cursor: pointer;
    }
    .customer-form-grid {
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 14px;
    }
    .customer-form-wide {
        grid-column: span 2;
    }
    .input-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 500;
        color: #334155;
        margin-bottom: 4px;
    }
    .input-group label span {
        color: #ef4444;
    }
    .input-group input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.875rem;
        outline: none;
        box-sizing: border-box;
    }
    .input-group input:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
    }
    .customer-modal-footer {
        padding: 14px 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    </style>
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
                <button type="button" class="btn btn-primary" onclick="openPickupModal()"><i class="fas fa-plus"></i> Thêm điểm nhận</button>
                <?php else: ?>
                <button type="button" class="btn btn-primary" onclick="openDeliveryModal()"><i class="fas fa-plus"></i> Thêm điểm giao</button>
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
                                    <button type="button" class="btn-icon text-primary" onclick='openPickupModal(<?php echo json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'><i class="fas fa-edit"></i></button>
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
                                    <button type="button" class="btn-icon text-primary" onclick='openDeliveryModal(<?php echo json_encode($d, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'><i class="fas fa-edit"></i></button>
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
            <div>
                <h2 id="pickupModalTitle">Thêm điểm nhận</h2>
                <p>Thông tin địa điểm lấy hàng</p>
            </div>
            <button class="modal-close" type="button" onclick="closePickupModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=diadiem" id="pickupForm">
            <input type="hidden" name="form_type" value="pickup">
            <input type="hidden" name="id" id="pickupId" value="">
            <div class="customer-form-grid">
                <div class="input-group customer-form-wide">
                    <label>Địa chỉ <span>*</span></label>
                    <input name="dia_chi" id="pickupDiaChi" required>
                </div>
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
            <div>
                <h2 id="deliveryModalTitle">Thêm điểm giao</h2>
                <p>Thông tin người nhận và địa điểm giao hàng</p>
            </div>
            <button class="modal-close" type="button" onclick="closeDeliveryModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=diadiem" id="deliveryForm">
            <input type="hidden" name="form_type" value="delivery">
            <input type="hidden" name="id" id="deliveryId" value="">
            <div class="customer-form-grid">
                <div class="input-group">
                    <label>Tên người nhận <span>*</span></label>
                    <input name="ten_nguoi_nhan" id="deliveryTen" required>
                </div>
                <div class="input-group"><label>Số điện thoại</label><input name="sdt" id="deliverySdt" type="tel"></div>
                <div class="input-group customer-form-wide">
                    <label>Địa chỉ <span>*</span></label>
                    <input name="dia_chi" id="deliveryDiaChi" required>
                </div>
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

<script>
function openPickupModal(data) {
    var m = document.getElementById('pickupModal');
    if (!m) return;
    
    m.setAttribute('aria-hidden', 'false');
    if (data) {
        document.getElementById('pickupModalTitle').textContent = 'Sửa điểm nhận';
        document.getElementById('pickupId').value        = data.MaDiemNhan || '';
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

function closePickupModal() {
    var m = document.getElementById('pickupModal');
    if (m) m.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function openDeliveryModal(data) {
    var m = document.getElementById('deliveryModal');
    if (!m) return;
    
    m.setAttribute('aria-hidden', 'false');
    if (data) {
        document.getElementById('deliveryModalTitle').textContent = 'Sửa điểm giao';
        document.getElementById('deliveryId').value        = data.MaDiemGiao || '';
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

function closeDeliveryModal() {
    var m = document.getElementById('deliveryModal');
    if (m) m.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}
</script>
</body>
</html>