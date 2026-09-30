<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý Vận tải</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-left">
            <div class="login-content">
                <h2>Hệ thống Vận tải <br><span class="highlight">Thông minh</span></h2>
                <p>Nền tảng quản lý và điều phối vận tải toàn diện. Dễ dàng theo dõi đơn hàng, quản lý tài xế và tối ưu tuyến đường giao hàng của bạn.</p>
                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Quản lý thời gian thực</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Phân quyền chi tiết, bảo mật cao</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Báo cáo và thống kê tự động</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="login-right">
            <div class="login-form-wrapper">
                <div class="logo-container">
                    <div class="logo-icon">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <span class="logo-text">LogisTech</span>
                </div>
                <h3>Đăng nhập</h3>
                <p class="subtitle">Vui lòng đăng nhập để tiếp tục</p>
                
                <?php if(!empty($error)): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form id="loginForm" method="POST" action="index.php?page=dangnhap">
                    <div class="input-group">
                        <label for="username">Tên đăng nhập / Email</label>
                        <div class="input-wrapper">
                            <i class="far fa-envelope"></i>
                            <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Mật khẩu</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                            <i class="far fa-eye toggle-password" id="togglePassword"></i>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <label class="checkbox-container">
                            <input type="checkbox" checked>
                            <span class="checkmark"></span>
                            Ghi nhớ đăng nhập
                        </label>
                        <a href="#" class="forgot-password">Quên mật khẩu?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Đăng nhập ngay <i class="fas fa-arrow-right"></i></button>
                </form>
                
                <div class="login-footer">
                    <p>Chưa có tài khoản? <a href="#">Liên hệ Quản trị viên</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>
