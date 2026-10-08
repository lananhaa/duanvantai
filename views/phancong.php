<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân công tài xế - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ─── Layout 2 cột ─── */
        .assign-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 1100px) { .assign-layout { grid-template-columns: 1fr; } }

        /* ─── Driver card ─── */
        .driver-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 700px) { .driver-grid { grid-template-columns: 1fr; } }

        .driver-card {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 14px;
            cursor: pointer;
            transition: all .2s;
            position: relative;
        }
        .driver-card:hover { border-color: var(--primary-color); background: #f5f8ff; }
        .driver-card.selected { border-color: var(--primary-color); background: #eef2ff; }
        .driver-card.busy { border-color: #fbbf24; background: #fffbeb; cursor: default; opacity: .85; }
        .driver-card.offline { opacity: .5; cursor: not-allowed; }

        .driver-name { font-weight: 600; margin-bottom: 4px; }
        .driver-meta { font-size: 12px; color: var(--text-muted); display: flex; flex-direction: column; gap: 3px; }
        .driver-meta i { width: 14px; }
        .driver-badge {
            position: absolute; top: 10px; right: 10px;
            padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600;
        }
        .badge-available { background: #dcfce7; color: #166534; }
        .badge-busy      { background: #fef3c7; color: #92400e; }
        .badge-offline   { background: #f3f4f6; color: #6b7280; }

        /* ─── Order list table ─── */
        .order-row { cursor: pointer; transition: background .15s; }
        .order-row:hover { background: #f5f8ff !important; }
        .order-row.selected { background: #eef2ff !important; }

        .order-row td { vertical-align: middle; }

        /* ─── Assign panel ─── */
        .assign-panel {
            position: sticky; top: 80px;
            background: white; border: 1px solid var(--border-color);
            border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(67,97,238,.08);
        }
        .assign-panel-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white; padding: 16px 20px;
        }
        .assign-panel-header h3 { margin: 0; font-size: 16px; }
        .assign-panel-header p { margin: 4px 0 0; opacity: .85; font-size: 13px; }
        .assign-panel-body { padding: 20px; }

        .selected-summary {
            background: #f8faff; border: 1px dashed #4361ee;
            border-radius: 8px; padding: 12px; margin-bottom: 16px;
            font-size: 13px; line-height: 1.8;
        }
        .selected-summary.empty { color: var(--text-muted); text-align: center; padding: 20px; }

        .status-badge {
            padding: 3px 9px; border-radius: 10px; font-size: 11px; font-weight: 600;
        }
        .s-cho-phan-cong { background:#fef3c7; color:#92400e; }
        .s-da-phan-cong  { background:#e0e7ff; color:#4338ca; }
        .s-dang-van-chuyen { background:#dbeafe; color:#1e40af; }
        .s-da-giao-hang  { background:#dcfce7; color:#166534; }
        .s-da-huy        { background:#fee2e2; color:#991b1b; }

        /* Alert message */
        .alert-message {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-message.success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-message.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* Filter row */
        .filter-row {
            display: flex; gap: 10px; align-items: center;
            padding: 14px 18px; border-bottom: 1px solid var(--border-color);
            background: #fafbfc; flex-wrap: wrap;
        }
        .filter-row input, .filter-row select {
            padding: 7px 12px; border: 1px solid #e2e8f0; border-radius: 6px;
            font-family: inherit; font-size: 13px; outline: none;
            transition: border-color .2s;
        }
        .filter-row input:focus, .filter-row select:focus { border-color: var(--primary-color); }
        .filter-row label { font-size: 13px; font-weight: 500; color: var(--text-muted); }
        .btn-sm { padding: 7px 14px; font-size: 13px; }

        .vehicle-chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f0f4ff; color: var(--primary-color);
            padding: 2px 8px; border-radius: 8px; font-size: 11px;
        }
    </style>
</head>
<body class="dashboard-body">
    <?php include 'views/menu.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="search-bar">
                <form action="index.php" method="GET" style="display:flex;width:100%">
                    <input type="hidden" name="page" value="phancong">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="keyword"
                           placeholder="Tìm đơn hàng theo mã, KH, người nhận..."
                           value="<?php echo htmlspecialchars($keyword); ?>">
                </form>
            </div>
            <div class="topbar-right">
                <button class="icon-btn"><i class="far fa-bell"></i><span class="badge">3</span></button>
                <div class="user-dropdown">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User">
                    <span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>!</span>
                </div>
            </div>
        </header>

        <div class="content-area">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Phân công Tài xế</h1>
                    <p class="page-subtitle">Chọn đơn hàng → Chọn tài xế → Xác nhận phân công</p>
                </div>
            </div>

            <?php if (!empty($message)): ?>
            <div class="alert-message <?php echo htmlspecialchars($messageType); ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="assign-layout">
                <!-- ════════ CỘT TRÁI: Danh sách đơn hàng ════════ -->
                <div>
                    <div class="card" style="overflow:hidden">
                        <div class="card-header" style="background:#fafbfc">
                            <h3 class="card-title"><i class="fas fa-box-open" style="color:var(--primary-color)"></i> Đơn hàng chờ phân công</h3>
                            <span style="font-size:13px;color:var(--text-muted)"><?php echo count($orders); ?> đơn</span>
                        </div>

                        <div class="filter-row">
                            <form action="index.php" method="GET" style="display:flex;gap:8px;width:100%;flex-wrap:wrap">
                                <input type="hidden" name="page" value="phancong">
                                <input type="text" name="keyword" placeholder="Tìm đơn..." value="<?php echo htmlspecialchars($keyword); ?>" style="flex:1;min-width:140px">
                                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i></button>
                                <?php if ($keyword): ?>
                                <a href="index.php?page=phancong" class="btn btn-outline btn-sm">Xóa lọc</a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:500px;overflow-y:auto">
                                <table class="table" style="font-size:13px">
                                    <thead style="position:sticky;top:0;background:white;z-index:2">
                                        <tr>
                                            <th>Mã ĐH</th>
                                            <th>Khách hàng</th>
                                            <th>Khu vực giao</th>
                                            <th>KL (kg)</th>
                                            <th>Trạng thái</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($orders)): ?>
                                        <tr><td colspan="6" class="text-center" style="padding:30px;color:var(--text-muted)">
                                            <i class="fas fa-check-circle" style="font-size:32px;color:#86efac;display:block;margin-bottom:8px"></i>
                                            Không có đơn hàng nào chờ phân công.
                                        </td></tr>
                                    <?php else: ?>
                                    <?php foreach ($orders as $o):
                                        $sClass = 's-cho-phan-cong';
                                        if ($o['TrangThai'] === 'Da phan cong')    $sClass = 's-da-phan-cong';
                                        if ($o['TrangThai'] === 'Dang van chuyen') $sClass = 's-dang-van-chuyen';
                                    ?>
                                        <tr class="order-row" data-order-id="<?php echo $o['MaDonHang']; ?>"
                                            data-order-info="<?php echo htmlspecialchars(json_encode([
                                                'id'      => $o['MaDonHang'],
                                                'kh'      => $o['TenKhachHang'],
                                                'nguoiNhan' => $o['TenNguoiNhan'],
                                                'sdt'     => $o['SDTNhan'],
                                                'khuVuc'  => $o['KhuVucGiao'],
                                                'tuyen'   => $o['TenTuyen'],
                                                'klg'     => $o['TongKhoiLuong'],
                                                'phi'     => $o['TongPhi'],
                                                'taiXe'   => $o['TenTaiXe'],
                                            ])); ?>">
                                            <td class="font-medium">#<?php echo $o['MaDonHang']; ?></td>
                                            <td><?php echo htmlspecialchars($o['TenKhachHang'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($o['KhuVucGiao'] ?? 'N/A'); ?></td>
                                            <td><?php echo number_format($o['TongKhoiLuong'], 1); ?></td>
                                            <td><span class="status-badge <?php echo $sClass; ?>"><?php echo htmlspecialchars($o['TrangThai']); ?></span></td>
                                            <td class="text-center">
                                                <?php if ($o['TaiXeDaPhanCong']): ?>
                                                <a href="index.php?page=phancong&action=cancel_assign&id=<?php echo $o['MaDonHang']; ?>"
                                                   class="btn-icon" style="color:#f59e0b"
                                                   title="Hủy phân công"
                                                   onclick="return confirm('Hủy phân công đơn #<?php echo $o['MaDonHang']; ?>?')">
                                                    <i class="fas fa-undo"></i>
                                                </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ════════ Danh sách Tài xế ════════ -->
                    <div class="card" style="margin-top:20px;overflow:hidden">
                        <div class="card-header" style="background:#fafbfc">
                            <h3 class="card-title"><i class="fas fa-id-card" style="color:#10b981"></i> Danh sách Tài xế</h3>
                        </div>

                        <div class="filter-row">
                            <form action="index.php" method="GET" style="display:flex;gap:8px;width:100%;flex-wrap:wrap">
                                <input type="hidden" name="page" value="phancong">
                                <?php if ($keyword): ?><input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>"><?php endif; ?>
                                <input type="text" name="driver_kw" placeholder="Tên, SĐT, khu vực..." value="<?php echo htmlspecialchars($driverKeyword); ?>" style="flex:1;min-width:140px">
                                <select name="driver_status" onchange="this.form.submit()" style="min-width:140px">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="San sang"  <?php echo $driverStatus==='San sang'  ?'selected':''; ?>>Sẵn sàng</option>
                                    <option value="Dang giao" <?php echo $driverStatus==='Dang giao' ?'selected':''; ?>>Đang giao</option>
                                    <option value="Nghi"      <?php echo $driverStatus==='Nghi'      ?'selected':''; ?>>Nghỉ</option>
                                </select>
                                <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>

                        <div class="card-body">
                            <?php if (empty($drivers)): ?>
                                <p style="text-align:center;color:var(--text-muted);padding:20px">Không tìm thấy tài xế nào.</p>
                            <?php else: ?>
                            <div class="driver-grid">
                                <?php foreach ($drivers as $d):
                                    $isBusy    = $d['TrangThai'] === 'Dang giao';
                                    $isOffline = $d['TrangThai'] === 'Nghi'
                                        || $d['TrangThaiTaiKhoan'] !== 'Hoat dong'
                                        || !$d['MaPhuongTien']
                                        || $d['TrangThaiXe'] === 'Bao tri';
                                    $cardClass = $isBusy ? 'busy' : ($isOffline ? 'offline' : '');
                                    $badgeClass= $isBusy ? 'badge-busy' : ($isOffline ? 'badge-offline' : 'badge-available');
                                    $badgeText = $isBusy ? 'Đang giao' : ($isOffline
                                        ? ($d['TrangThai'] === 'Nghi' ? 'Nghỉ' : (!$d['MaPhuongTien'] ? 'Chưa có xe' : ($d['TrangThaiXe'] === 'Bao tri' ? 'Xe bảo trì' : 'Tài khoản khóa')))
                                        : 'Sẵn sàng');
                                ?>
                                <div class="driver-card <?php echo $cardClass; ?>"
                                     data-driver-id="<?php echo $d['MaTaiXe']; ?>"
                                      data-driver-selectable="<?php echo ($isBusy || $isOffline) ? '0' : '1'; ?>"
                                     data-driver-name="<?php echo htmlspecialchars($d['HoTen']); ?>"
                                     data-driver-vehicle="<?php echo htmlspecialchars($d['BienSo'] ?? ''); ?>"
                                     data-driver-tai-trong="<?php echo $d['TaiTrong'] ?? 0; ?>"
                                     data-driver-khu-vuc="<?php echo htmlspecialchars($d['KhuVucHienTai'] ?? ''); ?>"
                                     <?php echo ($isBusy || $isOffline) ? '' : 'onclick="selectDriver(this)"'; ?>>
                                    <span class="driver-badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                                    <div class="driver-name"><i class="fas fa-user-tie" style="color:var(--primary-color)"></i> <?php echo htmlspecialchars($d['HoTen']); ?></div>
                                    <div class="driver-meta">
                                        <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($d['SoDienThoai'] ?? 'N/A'); ?></span>
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($d['KhuVucHienTai'] ?? 'Chưa cập nhật'); ?></span>
                                        <?php if ($d['BienSo']): ?>
                                        <span><i class="fas fa-truck"></i>
                                            <span class="vehicle-chip"><i class="fas fa-id-badge"></i> <?php echo htmlspecialchars($d['BienSo']); ?></span>
                                            <?php echo htmlspecialchars($d['LoaiPhuongTien'] ?? ''); ?>
                                            · Tải <?php echo number_format($d['TaiTrong'] ?? 0, 0); ?>kg
                                        </span>
                                        <?php else: ?>
                                        <span style="color:#f59e0b"><i class="fas fa-exclamation-triangle"></i> Chưa gán phương tiện</span>
                                        <?php endif; ?>
                                        <span><i class="fas fa-boxes"></i> Đang nhận: <strong><?php echo (int)$d['SoDonDangGiao']; ?> đơn</strong></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ════════ CỘT PHẢI: Bảng phân công ════════ -->
                <div>
                    <div class="assign-panel">
                        <div class="assign-panel-header">
                            <h3><i class="fas fa-clipboard-check"></i> Xác nhận Phân công</h3>
                            <p>Chọn đơn hàng và tài xế từ danh sách bên trái</p>
                        </div>
                        <div class="assign-panel-body">
                            <!-- Đơn hàng đã chọn -->
                            <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:8px">
                                <i class="fas fa-box" style="color:var(--primary-color)"></i> Đơn hàng đã chọn
                            </label>
                            <div class="selected-summary empty" id="orderSummary">
                                <i class="fas fa-mouse-pointer"></i> Nhấp vào một hàng trong bảng đơn hàng để chọn
                            </div>

                            <label style="font-size:13px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:8px;margin-top:16px">
                                <i class="fas fa-id-card" style="color:#10b981"></i> Tài xế đã chọn
                            </label>
                            <div class="selected-summary empty" id="driverSummary">
                                <i class="fas fa-mouse-pointer"></i> Nhấp vào thẻ tài xế để chọn
                            </div>

                            <form method="POST" action="index.php?page=phancong" id="assignForm">
                                <input type="hidden" name="action" value="phancong">
                                <input type="hidden" name="don_hang_id" id="hiddenOrderId" value="">
                                <input type="hidden" name="tai_xe_id"   id="hiddenDriverId" value="">

                                <div style="margin-top:16px">
                                    <label style="font-size:13px;font-weight:500;display:block;margin-bottom:6px">
                                        Ghi chú (tuỳ chọn)
                                    </label>
                                    <textarea name="ghi_chu" rows="2"
                                              placeholder="Ghi chú cho tài xế về đơn hàng này..."
                                              style="width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-family:inherit;font-size:13px;resize:vertical;outline:none"></textarea>
                                </div>

                                <button type="submit" id="btnAssign" class="btn btn-primary" style="width:100%;margin-top:16px" disabled>
                                    <i class="fas fa-paper-plane"></i> Xác nhận Phân công
                                </button>
                            </form>

                            <div style="margin-top:24px;border-top:1px dashed var(--border-color);padding-top:16px">
                                <p style="font-size:12px;color:var(--text-muted)">
                                    <i class="fas fa-info-circle"></i>
                                    Nếu đơn đã được phân công, phân công cũ sẽ bị hủy và thay bằng tài xế mới.
                                    Hành động này sẽ được ghi nhận vào Lịch sử trạng thái.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.assign-layout -->
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
    let selectedOrderId  = null;
    let selectedDriverId = null;

    function updateAssignButton() {
        const btn = document.getElementById('btnAssign');
        if (selectedOrderId && selectedDriverId) {
            btn.disabled = false;
            btn.style.opacity = '1';
        } else {
            btn.disabled = true;
            btn.style.opacity = '.5';
        }
    }

    // ─── Chọn đơn hàng
    document.querySelectorAll('.order-row').forEach(row => {
        row.addEventListener('click', function() {
            document.querySelectorAll('.order-row').forEach(r => r.classList.remove('selected'));
            this.classList.add('selected');
            selectedOrderId = this.dataset.orderId;
            document.getElementById('hiddenOrderId').value = selectedOrderId;

            const info = JSON.parse(this.dataset.orderInfo);
                const orderWeight = parseFloat(info.klg) || 0;
                document.querySelectorAll('.driver-card').forEach(card => {
                    const exceedsCapacity = parseFloat(card.dataset.driverTaiTrong) < orderWeight;
                    card.classList.toggle('offline', exceedsCapacity || card.dataset.driverSelectable !== '1');
                    if (card.dataset.driverSelectable === '1') {
                        const badge = card.querySelector('.driver-badge');
                        badge.textContent = exceedsCapacity ? 'Không đủ tải trọng' : 'Sẵn sàng';
                        badge.classList.toggle('badge-offline', exceedsCapacity);
                        badge.classList.toggle('badge-available', !exceedsCapacity);
                    }
                });
                if (selectedDriverId) {
                    const selectedCard = document.querySelector(`.driver-card[data-driver-id="${selectedDriverId}"]`);
                    if (!selectedCard || parseFloat(selectedCard.dataset.driverTaiTrong) < orderWeight) {
                        if (selectedCard) selectedCard.classList.remove('selected');
                        selectedDriverId = null;
                        document.getElementById('hiddenDriverId').value = '';
                        document.getElementById('driverSummary').classList.add('empty');
                        document.getElementById('driverSummary').innerHTML = '<i class="fas fa-mouse-pointer"></i> Nhấp vào thẻ tài xế để chọn';
                    }
                }
            document.getElementById('orderSummary').classList.remove('empty');
            document.getElementById('orderSummary').innerHTML = `
                <strong>Đơn #${info.id}</strong><br>
                👤 Khách hàng: ${info.kh || 'N/A'}<br>
                📦 Người nhận: ${info.nguoiNhan || 'N/A'} — ${info.sdt || ''}<br>
                📍 Khu vực giao: ${info.khuVuc || 'N/A'}<br>
                🚛 Tuyến: ${info.tuyen || 'N/A'}<br>
                ⚖️ Khối lượng: ${parseFloat(info.klg).toFixed(1)} kg<br>
                💰 Tổng phí: ${parseInt(info.phi).toLocaleString('vi-VN')}đ
                ${info.taiXe ? '<br>👨‍✈️ Đang phân công cho: <strong>' + info.taiXe + '</strong>' : ''}
            `;
            updateAssignButton();
        });
    });

    // ─── Chọn tài xế
    function selectDriver(card) {
        const selectedOrderRow = selectedOrderId
            ? document.querySelector(`.order-row[data-order-id="${selectedOrderId}"]`)
            : null;
        const selectedOrderInfo = selectedOrderRow ? JSON.parse(selectedOrderRow.dataset.orderInfo) : null;
        if (card.dataset.driverSelectable !== '1'
            || (selectedOrderInfo && parseFloat(card.dataset.driverTaiTrong) < (parseFloat(selectedOrderInfo.klg) || 0))) {
            return;
        }
        document.querySelectorAll('.driver-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        selectedDriverId = card.dataset.driverId;
        document.getElementById('hiddenDriverId').value = selectedDriverId;

        const name     = card.dataset.driverName;
        const vehicle  = card.dataset.driverVehicle;
        const taiTrong = card.dataset.driverTaiTrong;
        const khuVuc   = card.dataset.driverKhuVuc;

        document.getElementById('driverSummary').classList.remove('empty');
        document.getElementById('driverSummary').innerHTML = `
            <strong>${name}</strong><br>
            🚛 Phương tiện: ${vehicle || 'Chưa gán'}<br>
            ⚖️ Tải trọng: ${parseFloat(taiTrong).toLocaleString('vi-VN')} kg<br>
            📍 Khu vực hiện tại: ${khuVuc || 'Chưa cập nhật'}
        `;
        updateAssignButton();
    }

    // ─── Xác nhận trước khi submit
    document.getElementById('assignForm').addEventListener('submit', function(e) {
        if (!selectedOrderId || !selectedDriverId) {
            e.preventDefault();
            alert('Vui lòng chọn cả đơn hàng và tài xế trước khi phân công.');
            return;
        }
        const orderRow  = document.querySelector(`.order-row[data-order-id="${selectedOrderId}"]`);
        const orderInfo = orderRow ? JSON.parse(orderRow.dataset.orderInfo) : {};
        const driverCard = document.querySelector(`.driver-card[data-driver-id="${selectedDriverId}"]`);
        const driverName = driverCard ? driverCard.dataset.driverName : '';

        if (!confirm(`Xác nhận phân công:\n📦 Đơn #${selectedOrderId}\n👨‍✈️ Tài xế: ${driverName}\n\nTiếp tục?`)) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>
