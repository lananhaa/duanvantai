<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý COD - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* 1. Sửa lỗi vỡ khung Thống kê nhanh */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 20px;
    }
    .stat-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 20px;
        flex-shrink: 0;
    }
    .stat-info {
        flex: 1;
        min-width: 0;
    }
    .stat-label {
        font-size: 13px;
        color: #64748b;
        margin: 0 0 4px 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .stat-value {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    /* 2. Sửa lỗi Modal Popup */
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
        max-width: 460px;
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
        gap: 14px;
    }
    .input-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 500;
        color: #334155;
        margin-bottom: 4px;
    }
    .input-group input, .input-group select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.875rem;
        outline: none;
        box-sizing: border-box;
    }
    .input-group input:focus, .input-group select:focus {
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
                <input type="hidden" name="page" value="thuho">
                <?php if (!empty($_GET['status'])): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status']); ?>"><?php endif; ?>
                <?php if (!empty($_GET['date_from'])): ?><input type="hidden" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from']); ?>"><?php endif; ?>
                <?php if (!empty($_GET['date_to'])): ?><input type="hidden" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to']); ?>"><?php endif; ?>
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="keyword" placeholder="Tìm mã đơn, khách hàng, người nhận..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
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
                <h1 class="page-title">Quản lý COD</h1>
                <p class="page-subtitle">Theo dõi và cập nhật tiền thu hộ (Cash On Delivery)</p>
            </div>
            <div class="header-actions">
                <!-- Bộ lọc ngày -->
                <form action="index.php" method="GET" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="page" value="thuho">
                    <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($_GET['status'] ?? ''); ?>">
                    <input type="date" name="date_from" class="form-control" style="width:140px;" value="<?php echo htmlspecialchars($dateFrom ?? ''); ?>">
                    <span>→</span>
                    <input type="date" name="date_to" class="form-control" style="width:140px;" value="<?php echo htmlspecialchars($dateTo ?? ''); ?>">
                    <button type="submit" class="btn btn-outline" style="padding:8px 12px;">Lọc</button>
                </form>
            </div>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert-message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Thống kê nhanh (Đã định hình lại chuẩn 4 cột) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#4361ee,#3a0ca3)"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Tổng COD</p>
                    <h3 class="stat-value" style="color:#3a0ca3;"><?php echo number_format($summary['TongTienCOD'] ?? 0, 0, ',', '.'); ?>đ</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#06d6a0,#0aa372)"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Đã thu (<?php echo $summary['SoDaThu'] ?? 0; ?> đơn)</p>
                    <h3 class="stat-value" style="color:#0aa372;"><?php echo number_format($summary['DaThu'] ?? 0, 0, ',', '.'); ?>đ</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#f77f00,#d62828)"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Chưa thu (<?php echo $summary['SoChuaThu'] ?? 0; ?> đơn)</p>
                    <h3 class="stat-value" style="color:#d62828;"><?php echo number_format($summary['ChuaThu'] ?? 0, 0, ',', '.'); ?>đ</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#adb5bd,#6c757d)"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Không thu (<?php echo $summary['SoKhongThu'] ?? 0; ?> đơn)</p>
                    <h3 class="stat-value" style="color:#6c757d;"><?php echo number_format($summary['KhongThu'] ?? 0, 0, ',', '.'); ?>đ</h3>
                </div>
            </div>
        </div>

        <!-- Bộ lọc trạng thái -->
        <div style="display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;">
            <?php foreach (['' => 'Tất cả', 'Chua thu' => 'Chưa thu', 'Da thu' => 'Đã thu', 'Khong thu' => 'Không thu'] as $val => $label): ?>
            <a href="index.php?page=thuho&status=<?php echo urlencode($val); ?>&keyword=<?php echo urlencode($_GET['keyword'] ?? ''); ?>&date_from=<?php echo urlencode($dateFrom ?? ''); ?>&date_to=<?php echo urlencode($dateTo ?? ''); ?>"
               class="btn <?php echo ($status ?? '') === $val ? 'btn-primary' : 'btn-outline'; ?>" style="padding:6px 16px;font-size:13px;">
                <?php echo $label; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã COD</th>
                                <th>Đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Người nhận</th>
                                <th>Số tiền COD</th>
                                <th>TT Đơn hàng</th>
                                <th>TT COD</th>
                                <th>Tài xế</th>
                                <th>Thời gian thu</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($cods)): ?>
                            <tr><td colspan="10" class="text-center">Không tìm thấy bản ghi COD nào.</td></tr>
                        <?php else: ?>
                        <?php foreach ($cods as $c):
                            $codStatusCls = match($c['TrangThaiCOD']) {
                                'Da thu'    => 'status-green',
                                'Chua thu'  => 'status-orange',
                                'Khong thu' => 'status-gray',
                                default     => 'status-gray',
                            };
                            $codStatusLbl = match($c['TrangThaiCOD']) {
                                'Da thu'    => 'Đã thu',
                                'Chua thu'  => 'Chưa thu',
                                'Khong thu' => 'Không thu',
                                default     => $c['TrangThaiCOD'],
                            };
                        ?>
                            <tr>
                                <td>#<?php echo $c['MaCOD']; ?></td>
                                <td><strong>#<?php echo $c['MaDonHang']; ?></strong></td>
                                <td><?php echo htmlspecialchars($c['TenKhachHang'] ?? ''); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($c['TenNguoiNhan'] ?? ''); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($c['DiaChiGiao'] ?? ''); ?></small>
                                </td>
                                <td class="font-medium" style="color:#4361ee;"><?php echo number_format($c['SoTienCOD'], 0, ',', '.'); ?>đ</td>
                                <td><span class="status-badge"><?php echo htmlspecialchars($c['TrangThaiDon']); ?></span></td>
                                <td><span class="badge-pill <?php echo $codStatusCls; ?>"><?php echo $codStatusLbl; ?></span></td>
                                <td><?php echo htmlspecialchars($c['TenTaiXe'] ?? 'Chưa phân công'); ?></td>
                                <td><?php echo !empty($c['ThoiGianThu']) ? date('d/m/Y H:i', strtotime($c['ThoiGianThu'])) : '<span class="text-muted">—</span>'; ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline" style="padding:4px 10px;font-size:12px;"
                                        onclick="openCodModal(<?php echo $c['MaCOD']; ?>, '<?php echo htmlspecialchars($c['TrangThaiCOD'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($c['GhiChu'] ?? '', ENT_QUOTES); ?>', <?php echo $c['MaDonHang']; ?>)">
                                        <i class="fas fa-edit"></i> Cập nhật
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Cập nhật COD -->
<div class="customer-modal" id="codModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closeCodModal()"></div>
    <section class="customer-modal-dialog" role="dialog">
        <div class="customer-modal-header">
            <div><h2>Cập nhật COD</h2><p id="codModalSubtitle">Trạng thái thu tiền COD</p></div>
            <button class="modal-close" type="button" onclick="closeCodModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=thuho">
            <input type="hidden" name="cod_id" id="codUpdateId" value="">
            <div class="customer-form-grid">
                <div class="input-group">
                    <label>Trạng thái COD</label>
                    <select name="cod_status" id="codStatusSelect">
                        <option value="Chua thu">Chưa thu</option>
                        <option value="Da thu">Đã thu</option>
                        <option value="Khong thu">Không thu</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Ghi chú</label>
                    <input name="cod_note" id="codNote" placeholder="Lý do hoặc ghi chú...">
                </div>
            </div>
            <div class="customer-modal-footer">
                <button class="btn btn-outline" type="button" onclick="closeCodModal()">Hủy</button>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu</button>
            </div>
        </form>
    </section>
</div>

<script>
function openCodModal(codId, status, note, orderId) {
    var m = document.getElementById('codModal');
    if (!m) return;
    
    m.setAttribute('aria-hidden', 'false');
    document.getElementById('codUpdateId').value            = codId;
    document.getElementById('codModalSubtitle').textContent = 'Đơn hàng #' + orderId;
    document.getElementById('codStatusSelect').value        = status || 'Chua thu';
    document.getElementById('codNote').value                = note || '';
    document.body.style.overflow = 'hidden';
}

function closeCodModal() {
    var m = document.getElementById('codModal');
    if (m) m.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}
</script>
</body>
</html>