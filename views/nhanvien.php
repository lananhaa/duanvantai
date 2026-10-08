<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nhân viên - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
<?php include 'views/menu.php'; ?>
<main class="main-content">
    <header class="topbar">
        <div class="search-bar"><form action="index.php" method="GET" style="display:flex;width:100%;"><input type="hidden" name="page" value="nhanvien"><input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>"><i class="fas fa-search search-icon"></i><input name="keyword" placeholder="Tìm tên, chức vụ, điện thoại hoặc tài khoản..." value="<?php echo htmlspecialchars($keyword); ?>"><button type="submit" class="btn-icon" title="Tìm kiếm" aria-label="Tìm kiếm"><i class="fas fa-arrow-right"></i></button></form></div>
        <div class="topbar-right"><span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>!</span></div>
    </header>
    <div class="content-area">
        <div class="page-header"><div><h1 class="page-title">Quản lý nhân viên</h1><p class="page-subtitle">Hồ sơ nhân viên điều phối và tài khoản đăng nhập liên kết</p></div><div class="header-actions">
            <div style="display:flex;gap:6px;"><?php foreach (['' => 'Tất cả', 'Dang lam viec' => 'Đang làm việc', 'Nghi viec' => 'Nghỉ việc'] as $value => $label): ?><a class="btn <?php echo $status === $value ? 'btn-primary' : 'btn-outline'; ?>" href="index.php?page=nhanvien&status=<?php echo urlencode($value); ?>&keyword=<?php echo urlencode($keyword); ?>"><?php echo $label; ?></a><?php endforeach; ?></div>
            <button class="btn btn-primary" type="button" onclick="openEmployeeModal()"><i class="fas fa-plus"></i> Thêm nhân viên</button>
        </div></div>
        <?php if ($message !== ''): ?><div class="alert-message <?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table">
            <thead><tr><th>Mã NV</th><th>Họ tên</th><th>Chức vụ</th><th>Điện thoại</th><th>Email</th><th>Tài khoản</th><th>Trạng thái</th><th class="text-center">Thao tác</th></tr></thead>
            <tbody>
            <?php if (!$employees): ?><tr><td colspan="8" class="text-center">Không tìm thấy nhân viên.</td></tr><?php endif; ?>
            <?php foreach ($employees as $employee): $employeeJson = htmlspecialchars(json_encode($employee, JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>
            <tr><td>#<?php echo (int)$employee['MaNhanVien']; ?></td><td class="font-medium"><?php echo htmlspecialchars($employee['HoTen']); ?></td><td><?php echo htmlspecialchars($employee['ChucVu'] ?: 'Chưa cập nhật'); ?></td><td><?php echo htmlspecialchars($employee['SoDienThoai'] ?: ''); ?></td><td><?php echo htmlspecialchars($employee['Email'] ?: ''); ?></td><td><?php echo htmlspecialchars($employee['TenDangNhap']); ?><br><small class="text-muted"><?php echo $employee['TrangThaiTaiKhoan'] === 'Hoat dong' ? 'Tài khoản hoạt động' : 'Tài khoản đã khóa'; ?></small></td><td><span class="badge-pill <?php echo $employee['TrangThai'] === 'Dang lam viec' ? 'status-green' : 'status-gray'; ?>"><?php echo $employee['TrangThai'] === 'Dang lam viec' ? 'Đang làm việc' : 'Nghỉ việc'; ?></span></td><td class="text-center"><button class="btn-icon text-primary" type="button" title="Sửa nhân viên" onclick='openEmployeeModal(<?php echo $employeeJson; ?>)'><i class="fas fa-edit"></i></button><form method="POST" action="index.php?page=nhanvien" style="display:inline;" onsubmit="return confirm('Xóa hoặc chuyển nghỉ việc nhân viên này? Dữ liệu lịch sử sẽ được giữ lại.');"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo (int)$employee['MaNhanVien']; ?>"><button class="btn-icon text-danger" type="submit" title="Xóa nhân viên"><i class="fas fa-trash-alt"></i></button></form></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div></div>
    </div>
</main>
<div class="customer-modal" id="employeeModal" aria-hidden="true">
    <div class="customer-modal-backdrop" onclick="closeEmployeeModal()"></div>
    <section class="customer-modal-dialog" role="dialog" aria-modal="true">
        <div class="customer-modal-header"><div><h2 id="employeeModalTitle">Thêm nhân viên</h2><p>Thông tin hồ sơ và tài khoản điều phối</p></div><button class="modal-close" type="button" onclick="closeEmployeeModal()"><i class="fas fa-times"></i></button></div>
        <form method="POST" action="index.php?page=nhanvien" id="employeeForm">
            <input type="hidden" name="id" id="employeeId">
            <div class="customer-form-grid">
                <div class="input-group"><label>Họ tên <span>*</span></label><input name="name" id="employeeName" required></div>
                <div class="input-group"><label>Chức vụ</label><input name="position" id="employeePosition" placeholder="Nhân viên điều phối"></div>
                <div class="input-group"><label>Tên đăng nhập <span>*</span></label><input name="username" id="employeeUsername" required></div>
                <div class="input-group"><label>Mật khẩu <span id="employeePasswordRequired">*</span></label><input name="password" id="employeePassword" type="password" minlength="8" autocomplete="new-password"><small>Ít nhất 8 ký tự; để trống khi sửa nếu không đổi</small></div>
                <div class="input-group"><label>Số điện thoại</label><input name="phone" id="employeePhone" type="tel"></div>
                <div class="input-group"><label>Email</label><input name="email" id="employeeEmail" type="email"></div>
                <div class="input-group"><label>Trạng thái</label><select name="status" id="employeeStatus"><option value="Dang lam viec">Đang làm việc</option><option value="Nghi viec">Nghỉ việc</option></select></div>
            </div>
            <div class="customer-modal-footer"><button class="btn btn-outline" type="button" onclick="closeEmployeeModal()">Hủy</button><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu nhân viên</button></div>
        </form>
    </section>
</div>
<script src="assets/js/script.js"></script>
<script>
function openEmployeeModal(employee) {
    const form = document.getElementById('employeeForm');
    const modal = document.getElementById('employeeModal');
    form.reset();
    document.getElementById('employeeId').value = employee ? employee.MaNhanVien : '';
    document.getElementById('employeeModalTitle').textContent = employee ? 'Sửa nhân viên' : 'Thêm nhân viên';
    document.getElementById('employeePasswordRequired').textContent = employee ? '' : '*';
    if (employee) {
        document.getElementById('employeeName').value = employee.HoTen || '';
        document.getElementById('employeePosition').value = employee.ChucVu || '';
        document.getElementById('employeeUsername').value = employee.TenDangNhap || '';
        document.getElementById('employeePhone').value = employee.SoDienThoai || '';
        document.getElementById('employeeEmail').value = employee.Email || '';
        document.getElementById('employeeStatus').value = employee.TrangThai || 'Dang lam viec';
    }
    document.getElementById('employeePassword').required = !employee;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    document.getElementById('employeeName').focus();
}
function closeEmployeeModal() {
    const modal = document.getElementById('employeeModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && document.getElementById('employeeModal').classList.contains('is-open')) {
        closeEmployeeModal();
    }
});
</script>
</body>
</html>