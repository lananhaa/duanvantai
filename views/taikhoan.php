<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài khoản - LogisTech</title>
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
                <input type="hidden" name="page" value="taikhoan">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <input type="hidden" name="role_id" value="<?php echo $roleId; ?>">
                <i class="fas fa-search search-icon"></i>
                <input name="keyword" placeholder="Tìm tên đăng nhập hoặc người dùng..." value="<?php echo htmlspecialchars($keyword); ?>">
            </form>
        </div>
        <div class="topbar-right"><span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>!</span></div>
    </header>
    <div class="content-area">
        <div class="page-header">
            <div><h1 class="page-title">Quản lý tài khoản</h1><p class="page-subtitle">Quản lý quyền đăng nhập; hồ sơ khách hàng, nhân viên và tài xế được giữ nguyên.</p></div>
            <div class="header-actions">
                <form method="GET" action="index.php" style="display:flex;gap:8px;">
                    <input type="hidden" name="page" value="taikhoan"><input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                    <select name="role_id" class="form-control"><option value="0">Tất cả vai trò</option>
                    <?php foreach ($roles as $role): ?><option value="<?php echo (int)$role['MaVaiTro']; ?>" <?php echo $roleId === (int)$role['MaVaiTro'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($role['TenVaiTro']); ?></option><?php endforeach; ?></select>
                    <select name="status" class="form-control"><option value="">Mọi trạng thái</option><option value="Hoat dong" <?php echo $status === 'Hoat dong' ? 'selected' : ''; ?>>Đang hoạt động</option><option value="Da khoa" <?php echo $status === 'Da khoa' ? 'selected' : ''; ?>>Đã khóa</option></select>
                    <button class="btn btn-outline" type="submit"><i class="fas fa-filter"></i> Lọc</button>
                </form>
            </div>
        </div>
        <?php if ($message !== ''): ?><div class="alert-message <?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table">
            <thead><tr><th>Mã</th><th>Tên đăng nhập</th><th>Người dùng</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th class="text-center">Thao tác</th></tr></thead>
            <tbody>
            <?php if (!$accounts): ?><tr><td colspan="7" class="text-center">Không tìm thấy tài khoản.</td></tr><?php endif; ?>
            <?php foreach ($accounts as $account):
                $person = $account['TenKhachHang'] ?: ($account['TenNhanVien'] ?: ($account['TenTaiXe'] ?: ''));
                $accountJson = htmlspecialchars(json_encode(['id' => (int)$account['MaTaiKhoan'], 'username' => $account['TenDangNhap'], 'status' => $account['TrangThai']], JSON_UNESCAPED_UNICODE), ENT_QUOTES);
            ?>
            <tr>
                <td>#<?php echo (int)$account['MaTaiKhoan']; ?></td>
                <td class="font-medium"><?php echo htmlspecialchars($account['TenDangNhap']); ?></td>
                <td><?php echo htmlspecialchars($person ?: 'Chưa liên kết hồ sơ'); ?></td>
                <td><?php echo htmlspecialchars($account['TenVaiTro']); ?></td>
                <td><span class="badge-pill <?php echo $account['TrangThai'] === 'Hoat dong' ? 'status-green' : 'status-gray'; ?>"><?php echo $account['TrangThai'] === 'Hoat dong' ? 'Đang hoạt động' : 'Đã khóa'; ?></span></td>
                <td><?php echo date('d/m/Y', strtotime($account['NgayTao'])); ?></td>
                <td class="text-center"><button class="btn-icon text-primary" type="button" title="Cập nhật tài khoản" onclick='openAccountModal(<?php echo $accountJson; ?>)'><i class="fas fa-edit"></i></button></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div></div>
    </div>
</main>
<div class="customer-modal" id="accountModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closeAccountModal()"></div>
    <section class="customer-modal-dialog" role="dialog" aria-modal="true" style="max-width:480px;">
        <div class="customer-modal-header"><div><h2>Cập nhật tài khoản</h2><p>Đổi tên đăng nhập, mật khẩu hoặc trạng thái</p></div><button class="modal-close" type="button" onclick="closeAccountModal()"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="index.php?page=taikhoan">
            <input type="hidden" name="id" id="accountId">
            <div class="customer-form-grid" style="grid-template-columns:1fr;">
                <div class="input-group"><label>Tên đăng nhập</label><input name="username" id="accountUsername" required></div>
                <div class="input-group"><label>Mật khẩu mới</label><input name="password" type="password" minlength="8" autocomplete="new-password"><small>Để trống nếu không đổi</small></div>
                <div class="input-group"><label>Trạng thái đăng nhập</label><select name="status" id="accountStatus"><option value="Hoat dong">Đang hoạt động</option><option value="Da khoa">Đã khóa</option></select></div>
            </div>
            <div class="customer-modal-footer"><button class="btn btn-outline" type="button" onclick="closeAccountModal()">Hủy</button><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu</button></div>
        </form>
    </section>
</div>
<script src="assets/js/script.js"></script>
<script>
function openAccountModal(account) {
    document.getElementById('accountId').value = account.id;
    document.getElementById('accountUsername').value = account.username;
    document.getElementById('accountStatus').value = account.status;
    document.getElementById('accountModal').setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}
function closeAccountModal() {
    document.getElementById('accountModal').setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}
</script>
</body>
</html>