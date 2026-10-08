<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phương tiện - LogisTech</title>
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
                <input type="hidden" name="page" value="phuongtien">
                <?php if (!empty($_GET['status'])): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status']); ?>"><?php endif; ?>
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="keyword" placeholder="Tìm biển số, loại phương tiện..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
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
                <h1 class="page-title"><?php echo (int) ($_SESSION['role_id'] ?? 0) === 3 ? 'Phương tiện của tôi' : 'Quản lý phương tiện'; ?></h1>
                <p class="page-subtitle"><?php echo (int) ($_SESSION['role_id'] ?? 0) === 3 ? 'Hiển thị tất cả phương tiện được gán cho tài khoản tài xế của bạn' : 'Danh sách phương tiện, tài xế phụ trách và tình trạng xe'; ?></p>
            </div>
            <div class="header-actions">
                <div style="display:flex;gap:6px;">
                    <?php foreach (['' => 'Tất cả', 'San sang' => 'Sẵn sàng', 'Du phong' => 'Dự phòng', 'Dang chay' => 'Đang chạy', 'Bao tri' => 'Bảo trì'] as $val => $label): ?>
                    <a href="index.php?page=phuongtien&status=<?php echo urlencode($val); ?>&keyword=<?php echo urlencode($_GET['keyword'] ?? ''); ?>"
                       class="btn <?php echo ($_GET['status'] ?? '') === $val ? 'btn-primary' : 'btn-outline'; ?>" style="padding:6px 14px;font-size:13px;">
                        <?php echo $label; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-primary" type="button" onclick="openVehicleModal()">
                    <i class="fas fa-plus"></i> Thêm phương tiện
                </button>
            </div>
        </div>

        <?php if ($message !== ''): ?>
        <div class="alert-message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Thống kê nhanh -->
        <?php
        $vTotal = count($vehicles);
        $vReady = count(array_filter($vehicles, fn($v) => $v['TrangThai'] === 'San sang'));
        $vReserve = count(array_filter($vehicles, fn($v) => $v['TrangThai'] === 'Du phong'));
        $vRun   = count(array_filter($vehicles, fn($v) => $v['TrangThai'] === 'Dang chay'));
        $vMaint = count(array_filter($vehicles, fn($v) => $v['TrangThai'] === 'Bao tri'));
        ?>
        <div class="stats-grid" style="grid-template-columns:repeat(5,minmax(0,1fr));margin-bottom:20px;">
            <div class="stat-card"><div class="stat-icon" style="background:linear-gradient(135deg,#4361ee,#3a0ca3)"><i class="fas fa-truck"></i></div><div class="stat-info"><p class="stat-label">Tổng phương tiện</p><h3 class="stat-value"><?php echo $vTotal; ?></h3></div></div>
            <div class="stat-card"><div class="stat-icon" style="background:linear-gradient(135deg,#06d6a0,#0aa372)"><i class="fas fa-check-circle"></i></div><div class="stat-info"><p class="stat-label">Sẵn sàng</p><h3 class="stat-value"><?php echo $vReady; ?></h3></div></div>
            <div class="stat-card"><div class="stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="fas fa-warehouse"></i></div><div class="stat-info"><p class="stat-label">Dự phòng</p><h3 class="stat-value"><?php echo $vReserve; ?></h3></div></div>
            <div class="stat-card"><div class="stat-icon" style="background:linear-gradient(135deg,#f77f00,#d62828)"><i class="fas fa-road"></i></div><div class="stat-info"><p class="stat-label">Đang chạy</p><h3 class="stat-value"><?php echo $vRun; ?></h3></div></div>
            <div class="stat-card"><div class="stat-icon" style="background:linear-gradient(135deg,#ff6b6b,#ee5a24)"><i class="fas fa-wrench"></i></div><div class="stat-info"><p class="stat-label">Bảo trì</p><h3 class="stat-value"><?php echo $vMaint; ?></h3></div></div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã PT</th>
                                <th>Biển số</th>
                                <th>Loại phương tiện</th>
                                <th>Tải trọng (kg)</th>
                                <th>Tài xế phụ trách</th>
                                <th>Trạng thái</th>
                                <th>Mô tả</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($vehicles)): ?>
                            <tr><td colspan="8" class="text-center">Không tìm thấy phương tiện nào.</td></tr>
                        <?php else: ?>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td>#<?php echo $v['MaPhuongTien']; ?></td>
                                <td class="font-medium"><?php echo htmlspecialchars($v['BienSo']); ?></td>
                                <td><?php echo htmlspecialchars($v['LoaiPhuongTien'] ?? ''); ?></td>
                                <td><?php echo number_format($v['TaiTrong'], 0, ',', '.'); ?></td>
                                <td class="text-center">
                                    <?php echo $v['TenTaiXe'] ? htmlspecialchars($v['TenTaiXe']) : '<span class="text-muted">Chưa gán</span>'; ?>
                                </td>
                                <td>
                                    <?php
                                    $cls = match($v['TrangThai']) {
                                        'San sang'  => 'status-green',
                                        'Du phong'  => 'status-gray',
                                        'Dang chay' => 'status-blue',
                                        'Bao tri'   => 'status-red',
                                        default     => 'status-gray',
                                    };
                                    $lbl = match($v['TrangThai']) {
                                        'San sang'  => 'Sẵn sàng',
                                        'Du phong'  => 'Dự phòng',
                                        'Dang chay' => 'Đang chạy',
                                        'Bao tri'   => 'Bảo trì',
                                        default     => $v['TrangThai'],
                                    };
                                    ?>
                                    <span class="badge-pill <?php echo $cls; ?>"><?php echo $lbl; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($v['MoTa'] ?? ''); ?></td>
                                <td class="text-center">
                                    <button class="btn-icon text-primary" title="Sửa"
                                        onclick="openVehicleModal(<?php echo htmlspecialchars(json_encode($v), ENT_QUOTES); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="index.php?page=phuongtien&action=delete&id=<?php echo $v['MaPhuongTien']; ?>"
                                       class="btn-icon text-danger" title="Xóa"
                                       onclick="return confirm('Xóa phương tiện <?php echo htmlspecialchars($v['BienSo'], ENT_QUOTES); ?>?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Phương tiện -->
<div class="customer-modal" id="vehicleModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closeVehicleModal()"></div>
    <section class="customer-modal-dialog" role="dialog" aria-modal="true">
        <div class="customer-modal-header">
            <div><h2 id="vehicleModalTitle">Thêm phương tiện</h2><p>Thông tin xe và tình trạng</p></div>
            <button class="modal-close" type="button" onclick="closeVehicleModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=phuongtien" id="vehicleForm">
            <input type="hidden" name="id" id="vehicleId" value="">
            <div class="customer-form-grid">
                <div class="input-group"><label for="vehicleBienSo">Biển số <span>*</span></label><input id="vehicleBienSo" name="bien_so" required placeholder="VD: 29A-12345"></div>
                <div class="input-group"><label for="vehicleLoai">Loại phương tiện</label>
                    <select id="vehicleLoai" name="loai">
                        <option value="Xe tai nho">Xe tải nhỏ</option>
                        <option value="Xe tai">Xe tải</option>
                        <option value="Xe ban tai">Xe bán tải</option>
                        <option value="Xe may">Xe máy</option>
                        <option value="Xe container">Xe container</option>
                    </select>
                </div>
                <div class="input-group"><label for="vehicleTaiTrong">Tải trọng (kg)</label><input id="vehicleTaiTrong" name="tai_trong" type="number" min="0" step="0.01" value="0"></div>
                <?php if ((int)($_SESSION['role_id'] ?? 0) === 4): ?>
                <div class="input-group"><label for="vehicleDriver">Tài xế phụ trách</label>
                    <select id="vehicleDriver" name="driver_id">
                        <option value="">-- Chưa gán tài xế --</option>
                        <?php foreach ($drivers as $driver): ?>
                        <option value="<?php echo (int)$driver['MaTaiXe']; ?>"><?php echo htmlspecialchars($driver['HoTen']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="input-group"><label for="vehicleStatus">Trạng thái</label>
                    <select id="vehicleStatus" name="status">
                        <option value="San sang">Sẵn sàng</option>
                        <option value="Du phong">Dự phòng</option>
                        <option value="Dang chay">Đang chạy</option>
                        <option value="Bao tri">Bảo trì</option>
                    </select>
                </div>
                <div class="input-group customer-form-wide"><label for="vehicleMoTa">Mô tả</label><input id="vehicleMoTa" name="mo_ta" placeholder="Ghi chú về phương tiện..."></div>
            </div>
            <div class="customer-modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeVehicleModal()">Hủy</button>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu phương tiện</button>
            </div>
        </form>
    </section>
</div>

<script src="assets/js/script.js"></script>
<script>
function openVehicleModal(data) {
    const modal = document.getElementById('vehicleModal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    if (data) {
        document.getElementById('vehicleModalTitle').textContent = 'Sửa phương tiện';
        document.getElementById('vehicleId').value      = data.MaPhuongTien;
        document.getElementById('vehicleBienSo').value  = data.BienSo || '';
        document.getElementById('vehicleLoai').value    = data.LoaiPhuongTien || 'Xe tai';
        document.getElementById('vehicleTaiTrong').value= data.TaiTrong || 0;
        document.getElementById('vehicleStatus').value  = data.TrangThai || 'San sang';
        document.getElementById('vehicleMoTa').value    = data.MoTa || '';
        const driverSelect = document.getElementById('vehicleDriver');
        if (driverSelect) driverSelect.value = data.MaTaiXe || '';
    } else {
        document.getElementById('vehicleModalTitle').textContent = 'Thêm phương tiện';
        document.getElementById('vehicleForm').reset();
        document.getElementById('vehicleId').value = '';
        document.getElementById('vehicleStatus').value = '<?php echo htmlspecialchars($defaultVehicleStatus, ENT_QUOTES); ?>';
    }
    document.body.classList.add('modal-open');
}
function closeVehicleModal() {
    const modal = document.getElementById('vehicleModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
}
</script>
</body>
</html>
