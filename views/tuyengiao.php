<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tuyến giao - LogisTech</title>
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
                <input type="hidden" name="page" value="tuyengiao">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="keyword" placeholder="Tìm tuyến giao, khu vực..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
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
                <h1 class="page-title">Quản lý tuyến giao & phí vận chuyển</h1>
                <p class="page-subtitle">Thiết lập tuyến đường và bảng phí vận chuyển</p>
            </div>
            <div class="header-actions">
                <?php if ($tab === 'tuyengiao'): ?>
                <button class="btn btn-primary" onclick="openRouteModal()"><i class="fas fa-plus"></i> Thêm tuyến</button>
                <?php else: ?>
                <button class="btn btn-primary" onclick="openFeeModal()"><i class="fas fa-plus"></i> Thêm mức phí</button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message !== ''): ?>
        <div class="alert-message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Tab nav -->
        <div style="display:flex;gap:4px;margin-bottom:16px;border-bottom:2px solid var(--border-color);padding-bottom:0;">
            <a href="index.php?page=tuyengiao&tab=routes&keyword=<?php echo urlencode($_GET['keyword'] ?? ''); ?>"
               style="padding:10px 20px;font-weight:600;border-radius:6px 6px 0 0;text-decoration:none;
                      <?php echo $tab === 'tuyengiao' ? 'background:var(--primary);color:#fff;' : 'color:var(--text-secondary);'; ?>">
                <i class="fas fa-route"></i> Tuyến giao (<?php echo count($routes); ?>)
            </a>
            <a href="index.php?page=tuyengiao&tab=fees&keyword=<?php echo urlencode($_GET['keyword'] ?? ''); ?>"
               style="padding:10px 20px;font-weight:600;border-radius:6px 6px 0 0;text-decoration:none;
                      <?php echo $tab === 'fees' ? 'background:var(--primary);color:#fff;' : 'color:var(--text-secondary);'; ?>">
                <i class="fas fa-money-bill-wave"></i> Bảng phí (<?php echo count($fees); ?>)
            </a>
        </div>

        <?php if ($tab === 'tuyengiao'): ?>
        <!-- TUYẾN GIAO -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr>
                            <th>Mã</th><th>Tên tuyến</th><th>Khu vực đi</th><th>Khu vực đến</th>
                            <th>Số đơn hàng</th><th>Số mức phí</th><th>Mô tả</th><th class="text-center">Thao tác</th>
                        </tr></thead>
                        <tbody>
                        <?php if (empty($routes)): ?>
                            <tr><td colspan="8" class="text-center">Không có tuyến giao nào.</td></tr>
                        <?php else: ?>
                        <?php foreach ($routes as $r): ?>
                            <tr>
                                <td>#<?php echo $r['MaTuyenGiao']; ?></td>
                                <td class="font-medium"><?php echo htmlspecialchars($r['TenTuyen']); ?></td>
                                <td><span class="badge-pill status-blue"><?php echo htmlspecialchars($r['KhuVucDi'] ?? ''); ?></span></td>
                                <td><span class="badge-pill status-green"><?php echo htmlspecialchars($r['KhuVucDen'] ?? ''); ?></span></td>
                                <td class="text-center"><?php echo (int)$r['SoDonHang']; ?></td>
                                <td class="text-center"><?php echo (int)$r['SoMucPhi']; ?></td>
                                <td><?php echo htmlspecialchars($r['MoTa'] ?? ''); ?></td>
                                <td class="text-center">
                                    <button class="btn-icon text-primary" onclick="openRouteModal(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES); ?>)"><i class="fas fa-edit"></i></button>
                                    <a href="index.php?page=tuyengiao&action=delete_route&id=<?php echo $r['MaTuyenGiao']; ?>&tab=routes"
                                       class="btn-icon text-danger" onclick="return confirm('Xóa tuyến <?php echo htmlspecialchars($r['TenTuyen'], ENT_QUOTES); ?>?')"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- BẢNG PHÍ -->
        <div style="display:flex;gap:8px;margin-bottom:12px;align-items:center;">
            <form action="index.php" method="GET" style="display:flex;gap:8px;">
                <input type="hidden" name="page" value="tuyengiao">
                <input type="hidden" name="tab" value="fees">
                <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                <select name="status" class="form-control" style="width:auto;" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Dang ap dung" <?php echo ($_GET['status'] ?? '') === 'Dang ap dung' ? 'selected' : ''; ?>>Đang áp dụng</option>
                    <option value="Tam dung" <?php echo ($_GET['status'] ?? '') === 'Tam dung' ? 'selected' : ''; ?>>Tạm dừng</option>
                </select>
            </form>
        </div>
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr>
                            <th>Mã</th><th>Tên mức phí</th><th>Khu vực đi</th><th>Khu vực đến</th>
                            <th>KL từ (kg)</th><th>KL đến (kg)</th><th>Phí cơ bản</th><th>Phí vượt/kg</th>
                            <th>Trạng thái</th><th class="text-center">Thao tác</th>
                        </tr></thead>
                        <tbody>
                        <?php if (empty($fees)): ?>
                            <tr><td colspan="10" class="text-center">Không có mức phí nào.</td></tr>
                        <?php else: ?>
                        <?php foreach ($fees as $f): ?>
                            <tr>
                                <td>#<?php echo $f['MaPhi']; ?></td>
                                <td class="font-medium"><?php echo htmlspecialchars($f['TenMucPhi']); ?></td>
                                <td><?php echo htmlspecialchars($f['KhuVucDi'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($f['KhuVucDen'] ?? ''); ?></td>
                                <td><?php echo number_format($f['KhoiLuongTu'], 2, '.', ''); ?></td>
                                <td><?php echo number_format($f['KhoiLuongDen'], 2, '.', ''); ?></td>
                                <td class="font-medium"><?php echo number_format($f['PhiCoBan'], 0, ',', '.'); ?>đ</td>
                                <td><?php echo number_format($f['PhiVuotKhoiLuong'], 0, ',', '.'); ?>đ</td>
                                <td>
                                    <span class="badge-pill <?php echo $f['TrangThai'] === 'Dang ap dung' ? 'status-green' : 'status-gray'; ?>">
                                        <?php echo $f['TrangThai'] === 'Dang ap dung' ? 'Đang áp dụng' : 'Tạm dừng'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn-icon text-primary" onclick="openFeeModal(<?php echo htmlspecialchars(json_encode($f), ENT_QUOTES); ?>)"><i class="fas fa-edit"></i></button>
                                    <a href="index.php?page=tuyengiao&action=delete_fee&id=<?php echo $f['MaPhi']; ?>&tab=fees"
                                       class="btn-icon text-danger" onclick="return confirm('Xóa mức phí này?')"><i class="fas fa-trash-alt"></i></a>
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

<!-- Modal Tuyến Giao -->
<div class="customer-modal" id="routeModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closeRouteModal()"></div>
    <section class="customer-modal-dialog" role="dialog">
        <div class="customer-modal-header">
            <div><h2 id="routeModalTitle">Thêm tuyến giao</h2><p>Thông tin tuyến đường giao hàng</p></div>
            <button class="modal-close" type="button" onclick="closeRouteModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=tuyengiao" id="routeForm">
            <input type="hidden" name="form_type" value="route">
            <input type="hidden" name="id" id="routeId" value="">
            <div class="customer-form-grid">
                <div class="input-group customer-form-wide"><label>Tên tuyến <span>*</span></label><input name="ten" id="routeTen" required placeholder="VD: Thanh Xuân - Cầu Giấy"></div>
                <div class="input-group"><label>Khu vực đi <span>*</span></label><input name="khu_vuc_di" id="routeDi" required placeholder="VD: Thanh Xuan"></div>
                <div class="input-group"><label>Khu vực đến <span>*</span></label><input name="khu_vuc_den" id="routeDen" required placeholder="VD: Cau Giay"></div>
                <div class="input-group customer-form-wide"><label>Mô tả</label><input name="mo_ta" id="routeMoTa" placeholder="Ghi chú..."></div>
            </div>
            <div class="customer-modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeRouteModal()">Hủy</button>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu tuyến</button>
            </div>
        </form>
    </section>
</div>

<!-- Modal Phí Vận Chuyển -->
<div class="customer-modal" id="feeModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closeFeeModal()"></div>
    <section class="customer-modal-dialog" role="dialog" style="max-width:640px;">
        <div class="customer-modal-header">
            <div><h2 id="feeModalTitle">Thêm mức phí</h2><p>Cài đặt phí vận chuyển theo khu vực và trọng lượng</p></div>
            <button class="modal-close" type="button" onclick="closeFeeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=tuyengiao" id="feeForm">
            <input type="hidden" name="form_type" value="fee">
            <input type="hidden" name="id" id="feeId" value="">
            <div class="customer-form-grid">
                <div class="input-group customer-form-wide"><label>Tên mức phí <span>*</span></label><input name="ten" id="feeTen" required placeholder="VD: Nội thành dưới 5kg"></div>
                <div class="input-group"><label>Khu vực đi <span>*</span></label><input name="khu_vuc_di" id="feeDi" required></div>
                <div class="input-group"><label>Khu vực đến <span>*</span></label><input name="khu_vuc_den" id="feeDen" required></div>
                <div class="input-group"><label>KL từ (kg)</label><input name="kl_tu" id="feeKlTu" type="number" step="0.01" min="0" value="0"></div>
                <div class="input-group"><label>KL đến (kg) <span>*</span></label><input name="kl_den" id="feeKlDen" type="number" step="0.01" min="0" required></div>
                <div class="input-group"><label>Phí cơ bản (đ) <span>*</span></label><input name="phi_co_ban" id="feePhiCoBan" type="number" min="0" required placeholder="VD: 30000"></div>
                <div class="input-group"><label>Phí vượt/kg (đ)</label><input name="phi_vuot" id="feePhiVuot" type="number" min="0" placeholder="VD: 5000"></div>
                <div class="input-group"><label>Trạng thái</label>
                    <select name="status" id="feeStatus">
                        <option value="Dang ap dung">Đang áp dụng</option>
                        <option value="Tam dung">Tạm dừng</option>
                    </select>
                </div>
            </div>
            <div class="customer-modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeFeeModal()">Hủy</button>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu mức phí</button>
            </div>
        </form>
    </section>
</div>

<script src="assets/js/script.js"></script>
<script>
function openRouteModal(data) {
    const m = document.getElementById('routeModal');
    m.setAttribute('aria-hidden','false');
    if (data) {
        document.getElementById('routeModalTitle').textContent = 'Sửa tuyến giao';
        document.getElementById('routeId').value  = data.MaTuyenGiao;
        document.getElementById('routeTen').value = data.TenTuyen || '';
        document.getElementById('routeDi').value  = data.KhuVucDi || '';
        document.getElementById('routeDen').value = data.KhuVucDen || '';
        document.getElementById('routeMoTa').value= data.MoTa || '';
    } else {
        document.getElementById('routeModalTitle').textContent = 'Thêm tuyến giao';
        document.getElementById('routeForm').reset();
        document.getElementById('routeId').value = '';
    }
    document.body.style.overflow = 'hidden';
}
function closeRouteModal() { document.getElementById('routeModal').setAttribute('aria-hidden','true'); document.body.style.overflow=''; }

function openFeeModal(data) {
    const m = document.getElementById('feeModal');
    m.setAttribute('aria-hidden','false');
    if (data) {
        document.getElementById('feeModalTitle').textContent = 'Sửa mức phí';
        document.getElementById('feeId').value       = data.MaPhi;
        document.getElementById('feeTen').value      = data.TenMucPhi || '';
        document.getElementById('feeDi').value       = data.KhuVucDi || '';
        document.getElementById('feeDen').value      = data.KhuVucDen || '';
        document.getElementById('feeKlTu').value     = data.KhoiLuongTu || 0;
        document.getElementById('feeKlDen').value    = data.KhoiLuongDen || '';
        document.getElementById('feePhiCoBan').value = data.PhiCoBan || '';
        document.getElementById('feePhiVuot').value  = data.PhiVuotKhoiLuong || 0;
        document.getElementById('feeStatus').value   = data.TrangThai || 'Dang ap dung';
    } else {
        document.getElementById('feeModalTitle').textContent = 'Thêm mức phí';
        document.getElementById('feeForm').reset();
        document.getElementById('feeId').value = '';
    }
    document.body.style.overflow = 'hidden';
}
function closeFeeModal() { document.getElementById('feeModal').setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
</script>
</body>
</html>
