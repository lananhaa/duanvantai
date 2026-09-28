<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân quyền & Trang chủ - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <?php include 'views/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" placeholder="Tìm kiếm chức năng, mã đơn hàng...">
            </div>
            <div class="topbar-right">
                <button class="icon-btn notification-btn">
                    <i class="far fa-bell"></i>
                    <span class="badge">3</span>
                </button>
                <div class="user-dropdown">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User">
                    <span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Khách'); ?>! <i class="fas fa-chevron-down ml-1"></i></span>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Phân quyền chức năng theo tác nhân</h1>
                    <p class="page-subtitle">Cấu hình và kiểm soát quyền truy cập hệ thống của từng nhóm người dùng</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-outline"><i class="fas fa-undo"></i> Hủy</button>
                    <button class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bảng 3.17. Phân quyền chức năng</h3>
                    <div class="card-tools">
                        <button class="btn-icon"><i class="fas fa-download"></i> Xuất Excel</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table permissions-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%">Chức năng</th>
                                    <th class="text-center" style="width: 17.5%"><div class="role-badge customer">Khách hàng</div></th>
                                    <th class="text-center" style="width: 17.5%"><div class="role-badge coordinator">Điều phối</div></th>
                                    <th class="text-center" style="width: 17.5%"><div class="role-badge driver">Tài xế</div></th>
                                    <th class="text-center" style="width: 17.5%"><div class="role-badge admin">Quản trị viên</div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="font-medium">Quản lý khách hàng</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Quản lý đơn hàng</td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr class="bg-light">
                                    <td class="font-medium">Quản lý tài xế</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr class="bg-light">
                                    <td class="font-medium">Quản lý phương tiện</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Quản lý điểm nhận/giao</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Phân công tài xế</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                
                                <tr>
                                    <td class="font-medium">Quản lý tuyến giao</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Theo dõi trạng thái đơn hàng</td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Tính phí vận chuyển</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Quản lý COD</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i> <span class="note-star">(*)</span></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Tra cứu đơn hàng</td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><span class="conditional-badge">Đơn được phân công</span></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Thống kê giao hàng</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Thống kê doanh thu</td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-minus text-muted"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card-footer alert-info">
                        <div class="note-content">
                            <i class="fas fa-info-circle note-icon"></i>
                            <p><strong>Ghi chú:</strong> <span class="note-star">(*)</span> quyền thực hiện phụ thuộc vào trạng thái đơn hàng và quyền dữ liệu cụ thể.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>
</html>
