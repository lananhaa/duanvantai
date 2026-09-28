    <?php $activePage = $_GET['page'] ?? 'dashboard'; $sidebarRole = (int) ($_SESSION['role_id'] ?? 0); ?>
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
                    <a href="index.php?page=dashboard" class="nav-link <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i>
                        <span class="nav-text">Trang chủ</span>
                    </a>
                </li>
                
                <li class="nav-title">Nghiệp vụ Đơn hàng</li>
                <?php if ($sidebarRole !== 3): ?>
                <li class="nav-item">
                    <a href="index.php?page=orders" class="nav-link <?php echo $activePage === 'orders' ? 'active' : ''; ?>">
                        <i class="fas fa-box-open"></i>
                        <span class="nav-text">Quản lý đơn hàng</span>
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-map-marker-alt"></i>
                        <span class="nav-text">Quản lý điểm nhận/giao</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=tracking" class="nav-link <?php echo in_array($activePage, ['tracking', 'driver-orders'], true) ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-check"></i>
                        <span class="nav-text">Theo dõi trạng thái</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-search"></i>
                        <span class="nav-text">Tra cứu đơn hàng</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="nav-text">Quản lý COD</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-calculator"></i>
                        <span class="nav-text">Tính phí vận chuyển</span>
                    </a>
                </li>

                <li class="nav-title">Nghiệp vụ Vận tải</li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-id-card"></i>
                        <span class="nav-text">Quản lý tài xế</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-truck"></i>
                        <span class="nav-text">Quản lý phương tiện</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=assign" class="nav-link <?php echo $activePage === 'assign' ? 'active' : ''; ?>">
                        <i class="fas fa-user-check"></i>
                        <span class="nav-text">Phân công tài xế</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-route"></i>
                        <span class="nav-text">Quản lý tuyến giao</span>
                    </a>
                </li>

                <li class="nav-title">Khách hàng & Báo cáo</li>
                <li class="nav-item">
                    <a href="index.php?page=customers" class="nav-link <?php echo $activePage === 'customers' ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Quản lý khách hàng</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span class="nav-text">Thống kê giao hàng</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        <span class="nav-text">Thống kê doanh thu</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?page=permissions" class="nav-link <?php echo $activePage === 'permissions' ? 'active' : ''; ?>">
                        <i class="fas fa-shield-alt"></i>
                        <span class="nav-text">Phân quyền chức năng</span>
                    </a>
                </li>
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
                        $roleId = $_SESSION['role_id'] ?? 0;
                        switch($roleId) {
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
