<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ cá nhân - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
<?php include 'views/menu.php'; ?>
<main class="main-content">
    <header class="topbar">
        <div class="search-bar"><i class="fas fa-user-circle search-icon"></i><span style="color:var(--text-secondary);font-size:14px;">Thông tin tài khoản đang đăng nhập</span></div>
        <div class="topbar-right"><span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>!</span></div>
    </header>
    <div class="content-area">
        <div class="page-header"><div><h1 class="page-title">Hồ sơ cá nhân</h1><p class="page-subtitle">Cập nhật thông tin và thông tin đăng nhập của bạn</p></div></div>
        <?php if ($message !== ''): ?><div class="alert-message <?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <div class="card" style="max-width:900px;">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-user-edit" style="color:#4361ee;"></i> Thông tin tài khoản</h3><span class="badge-pill status-gray">Mã #<?php echo (int)$account['MaTaiKhoan']; ?> · Vai trò <?php echo (int)$roleId; ?></span></div>
            <div class="card-body">
                <form method="POST" action="index.php?page=hoso" autocomplete="off">
                    <div class="customer-form-grid">
                        <div class="input-group"><label for="profileUsername">Tên đăng nhập <span>*</span></label><input id="profileUsername" name="username" value="<?php echo htmlspecialchars($account['TenDangNhap']); ?>" required maxlength="100" autocomplete="username"></div>
                        <?php if ($roleId !== 4): ?>
                        <div class="input-group"><label for="profileName">Họ tên <span>*</span></label><input id="profileName" name="name" value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>" required maxlength="100" autocomplete="name"></div>
                        <div class="input-group"><label for="profilePhone">Số điện thoại</label><input id="profilePhone" name="phone" type="tel" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" maxlength="20" autocomplete="tel"></div>
                        <?php endif; ?>

                        <?php if (in_array($roleId, [1, 2], true)): ?>
                        <div class="input-group"><label for="profileEmail">Email</label><input id="profileEmail" name="email" type="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" maxlength="100" autocomplete="email"></div>
                        <?php endif; ?>

                        <?php if ($roleId === 1): ?>
                        <div class="input-group customer-form-wide"><label for="profileAddress">Địa chỉ</label><input id="profileAddress" name="address" value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>" maxlength="255" autocomplete="street-address"></div>
                        <?php elseif ($roleId === 2): ?>
                        <div class="input-group"><label>Chức vụ</label><input value="<?php echo htmlspecialchars($profile['position'] ?? ''); ?>" readonly></div>
                        <?php elseif ($roleId === 3): ?>
                        <div class="input-group"><label for="profileArea">Khu vực hiện tại</label><input id="profileArea" name="area" value="<?php echo htmlspecialchars($profile['area'] ?? ''); ?>" maxlength="100"></div>
                        <div class="input-group"><label for="profileLicense">Số bằng lái</label><input id="profileLicense" name="license" value="<?php echo htmlspecialchars($profile['license'] ?? ''); ?>" maxlength="50"></div>
                        <div class="input-group customer-form-wide"><label for="profileAddress">Địa chỉ</label><input id="profileAddress" name="address" value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>" maxlength="255" autocomplete="street-address"></div>
                        <?php endif; ?>
                    </div>

                    <div style="border-top:1px solid var(--border-color);margin:24px 0 20px;padding-top:20px;">
                        <h3 style="font-size:16px;margin-bottom:16px;">Đổi mật khẩu</h3>
                        <div class="customer-form-grid">
                            <div class="input-group"><label for="currentPassword">Mật khẩu hiện tại</label><input id="currentPassword" name="current_password" type="password" autocomplete="current-password"><small>Bắt buộc khi đặt mật khẩu mới</small></div>
                            <div class="input-group"><label for="newPassword">Mật khẩu mới</label><input id="newPassword" name="new_password" type="password" minlength="8" autocomplete="new-password"><small>Để trống nếu không đổi; tối thiểu 8 ký tự</small></div>
                            <div class="input-group"><label for="confirmPassword">Xác nhận mật khẩu mới</label><input id="confirmPassword" name="confirm_password" type="password" minlength="8" autocomplete="new-password"></div>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:10px;">
                        <a class="btn btn-outline" href="index.php?page=trangchu">Hủy</a>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu hồ sơ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<script src="assets/js/script.js"></script>
</body>
</html>