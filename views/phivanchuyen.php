<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phí vận chuyển - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        max-width: 640px;
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
                <input type="hidden" name="page" value="phivanchuyen">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="keyword" placeholder="Tìm tên mức phí, khu vực..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
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
                <h1 class="page-title">Quản lý phí vận chuyển</h1>
                <p class="page-subtitle">Cấu hình bảng giá cước vận chuyển theo khối lượng và khu vực</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-primary" onclick="openFeeModal()"><i class="fas fa-plus"></i> Thêm mức phí</button>
            </div>
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert-message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Bộ lọc -->
        <div style="display:flex;gap:8px;margin-bottom:16px;align-items:center;">
            <form action="index.php" method="GET" style="display:flex;gap:8px;">
                <input type="hidden" name="page" value="phivanchuyen">
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
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tên mức phí</th>
                                <th>Khu vực đi</th>
                                <th>Khu vực đến</th>
                                <th>KL từ (kg)</th>
                                <th>KL đến (kg)</th>
                                <th>Phí cơ bản</th>
                                <th>Phí vượt/kg</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
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
                                    <button type="button" class="btn-icon text-primary" onclick='openFeeModal(<?php echo json_encode($f, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'><i class="fas fa-edit"></i></button>
                                    <a href="index.php?page=phivanchuyen&action=delete&id=<?php echo $f['MaPhi']; ?>" class="btn-icon text-danger" onclick="return confirm('Xóa mức phí này?')"><i class="fas fa-trash-alt"></i></a>
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

<!-- Modal Thêm / Sửa Phí Vận Chuyển -->
<div class="customer-modal" id="feeModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closeFeeModal()"></div>
    <section class="customer-modal-dialog" role="dialog">
        <div class="customer-modal-header">
            <div>
                <h2 id="feeModalTitle">Thêm mức phí</h2>
                <p>Cài đặt phí vận chuyển theo khu vực và trọng lượng</p>
            </div>
            <button class="modal-close" type="button" onclick="closeFeeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="index.php?page=phivanchuyen" id="feeForm">
            <input type="hidden" name="id" id="feeId" value="">
            <div class="customer-form-grid">
                <div class="input-group customer-form-wide">
                    <label>Tên mức phí <span>*</span></label>
                    <input name="ten" id="feeTen" required placeholder="VD: Nội thành dưới 5kg">
                </div>
                <div class="input-group">
                    <label>Khu vực đi <span>*</span></label>
                    <input name="khu_vuc_di" id="feeDi" required placeholder="VD: Hà Nội">
                </div>
                <div class="input-group">
                    <label>Khu vực đến <span>*</span></label>
                    <input name="khu_vuc_den" id="feeDen" required placeholder="VD: TP. Hồ Chí Minh">
                </div>
                <div class="input-group">
                    <label>KL từ (kg)</label>
                    <input name="kl_tu" id="feeKlTu" type="number" step="0.01" min="0" value="0">
                </div>
                <div class="input-group">
                    <label>KL đến (kg) <span>*</span></label>
                    <input name="kl_den" id="feeKlDen" type="number" step="0.01" min="0" required placeholder="VD: 5.0">
                </div>
                <div class="input-group">
                    <label>Phí cơ bản (đ) <span>*</span></label>
                    <input name="phi_co_ban" id="feePhiCoBan" type="number" min="0" required placeholder="VD: 30000">
                </div>
                <div class="input-group">
                    <label>Phí vượt/kg (đ)</label>
                    <input name="phi_vuot" id="feePhiVuot" type="number" min="0" placeholder="VD: 5000">
                </div>
                <div class="input-group customer-form-wide">
                    <label>Trạng thái</label>
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

<script>
function openFeeModal(data) {
    var m = document.getElementById('feeModal');
    if (!m) return;
    
    m.setAttribute('aria-hidden', 'false');
    if (data) {
        document.getElementById('feeModalTitle').textContent = 'Sửa mức phí';
        document.getElementById('feeId').value        = data.MaPhi || '';
        document.getElementById('feeTen').value       = data.TenMucPhi || '';
        document.getElementById('feeDi').value        = data.KhuVucDi || '';
        document.getElementById('feeDen').value       = data.KhuVucDen || '';
        document.getElementById('feeKlTu').value      = data.KhoiLuongTu || 0;
        document.getElementById('feeKlDen').value     = data.KhoiLuongDen || '';
        document.getElementById('feePhiCoBan').value  = data.PhiCoBan || '';
        document.getElementById('feePhiVuot').value   = data.PhiVuotKhoiLuong || 0;
        document.getElementById('feeStatus').value    = data.TrangThai || 'Dang ap dung';
    } else {
        document.getElementById('feeModalTitle').textContent = 'Thêm mức phí';
        document.getElementById('feeForm').reset();
        document.getElementById('feeId').value = '';
    }
    document.body.style.overflow = 'hidden';
}

function closeFeeModal() {
    var m = document.getElementById('feeModal');
    if (m) m.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}
</script>
</body>
</html>