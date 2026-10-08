    <?php $activePage = $_GET['page'] ?? 'trangchu'; $sidebarRole = (int) ($_SESSION['role_id'] ?? 0); ?>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-container">
                <div class="logo-icon">
                    <i class="fas fa-truck-fast"></i>
                </div>
                <span class="logo-text">LogisTech</span>
            </div>
            <button class="toggle-btn" id="toggleSidebar">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <div class="sidebar-menu-container">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php?page=trangchu" class="nav-link <?php echo $activePage === 'trangchu' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Trang chủ</span>
                    </a>
                </li>
                
                <li class="nav-title">Nghiệp vụ Đơn hàng</li>
                <?php if ($sidebarRole !== 3): ?>
                <li class="nav-item">
                    <a href="index.php?page=donhang" class="nav-link <?php echo $activePage === 'donhang' ? 'active' : ''; ?>">
                        <i class="fas fa-box-open"></i>
                        <span class="nav-text">Quản lý đơn hàng</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="index.php?page=diadiem" class="nav-link <?php echo $activePage === 'diadiem' ? 'active' : ''; ?>">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="nav-text">Quản lý điểm nhận/giao</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=phivanchuyen" class="nav-link <?php echo $activePage === 'phivanchuyen' ? 'active' : ''; ?>">
                        <i class="fas fa-calculator"></i>
                        <span class="nav-text">Quản lý phí vận chuyển</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=donhangtaixe" class="nav-link <?php echo in_array($activePage, ['donhangtaixe', 'donhangtaixe'], true) ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-check"></i>
                        <span class="nav-text">Trạng thái đơn hàng</span>
                    </a>
                </li>
            
                <?php if (in_array($sidebarRole, [2, 4], true)): ?>
                <li class="nav-item">
                    <a href="index.php?page=thuho" class="nav-link <?php echo $activePage === 'thuho' ? 'active' : ''; ?>">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">Quản lý COD</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-title">Nghiệp vụ Vận tải</li>
                <li class="nav-item">
                    <a href="index.php?page=taixe" class="nav-link <?php echo $activePage === 'taixe' ? 'active' : ''; ?>">
                        <i class="fas fa-id-card"></i>
                        <span class="nav-text">Quản lý tài xế</span>
                    </a>
                </li>
                <?php if (in_array($sidebarRole, [3, 4], true)): ?>
                <li class="nav-item">
                    <a href="index.php?page=phuongtien" class="nav-link <?php echo $activePage === 'phuongtien' ? 'active' : ''; ?>">
                        <i class="fas fa-truck"></i>
                        <span class="nav-text">Quản lý phương tiện</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="index.php?page=phancong" class="nav-link <?php echo $activePage === 'phancong' ? 'active' : ''; ?>">
                        <i class="fas fa-user-check"></i>
                        <span class="nav-text">Phân công tài xế</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="index.php?page=tuyengiao" class="nav-link <?php echo $activePage === 'tuyengiao' ? 'active' : ''; ?>">
                        <i class="fas fa-route"></i>
                        <span class="nav-text">Quản lý tuyến giao</span>
                    </a>
                </li>

                <li class="nav-title">Khách hàng & Báo cáo</li>
                <li class="nav-item">
                    <a href="index.php?page=khachhang" class="nav-link <?php echo $activePage === 'khachhang' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Quản lý khách hàng</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=hoso" class="nav-link <?php echo $activePage === 'hoso' ? 'active' : ''; ?>">
                        <i class="fas fa-user-circle"></i>
                        <span class="nav-text">Hồ sơ cá nhân</span>
                    </a>
                </li>
                <?php if (in_array($sidebarRole, [2, 4], true)): ?>
                <li class="nav-item">
                    <a href="index.php?page=thongke" class="nav-link <?php echo $activePage === 'thongke' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-line"></i>
                        <span class="nav-text">Thống kê giao hàng / doanh thu</span>
                    </a>
                </li>
                <?php endif; ?>
                <?php if ($sidebarRole === 4): ?>
                <li class="nav-title">Quản trị hệ thống</li>
                <li class="nav-item">
                    <a href="index.php?page=taikhoan" class="nav-link <?php echo $activePage === 'taikhoan' ? 'active' : ''; ?>">
                        <i class="fas fa-user-lock"></i>
                        <span class="nav-text">Quản lý tài khoản</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=nhanvien" class="nav-link <?php echo $activePage === 'nhanvien' ? 'active' : ''; ?>">
                        <i class="fas fa-user-tie"></i>
                        <span class="nav-text">Quản lý nhân viên</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="user-profile">
            <div class="avatar">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" alt="User">
            </div>
            <div class="user-info">
                <h4 class="name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Khách'); ?></h4>
                <p class="role">
                    <?php 
                        $displayRoleId = $_SESSION['role_id'] ?? 0;
                        switch($displayRoleId) {
                            case 1: echo 'Khách hàng'; break;
                            case 2: echo 'Điều phối'; break;
                            case 3: echo 'Tài xế'; break;
                            case 4: echo 'Quản trị viên'; break;
                            default: echo 'Unknown';
                        }
                    ?>
                </p>
            </div>
            <a href="index.php?action=logout" class="logout-btn" title="Đăng xuất"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>
