<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khách hàng - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php include 'views/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="search-bar">
                <form action="index.php" method="GET" style="display: flex; width: 100%;">
                    <input type="hidden" name="page" value="customers">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="keyword" placeholder="Tìm kiếm họ tên, sđt, email..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                </form>
            </div>
            <div class="topbar-right">
                <button class="icon-btn notification-btn"><i class="far fa-bell"></i><span class="badge">3</span></button>
                <div class="user-dropdown">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User">
                    <span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Khách'); ?>!</span>
                </div>
            </div>
        </header>

        <div class="content-area">
            <div class="page-header">
                <div>
                    <h1 class="page-title">Quản lý khách hàng</h1>
                    <p class="page-subtitle">Danh sách khách hàng và thông tin liên hệ</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" type="button" data-customer-modal="create"><i class="fas fa-plus"></i> Thêm khách hàng</button>
                </div>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert-message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mã KH</th>
                                    <th>Họ Tên</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Email</th>
                                    <th>Địa Chỉ</th>
                                    <th>Ngày Tạo</th>
                                    <th>Tài Khoản</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($customers)): ?>
                                <tr><td colspan="8" class="text-center">Không tìm thấy khách hàng nào.</td></tr>
                                <?php else: ?>
                                <?php foreach($customers as $row): ?>
                                <tr>
                                    <td>#<?php echo $row['MaKhachHang']; ?></td>
                                    <td class="font-medium"><?php echo htmlspecialchars($row['HoTen']); ?></td>
                                    <td><?php echo htmlspecialchars($row['SoDienThoai'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($row['DiaChi'] ?? ''); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['NgayTao'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['TenDangNhap'] ?? 'Chưa có'); ?></td>
                                    <td class="text-center">
                                        <button class="btn-icon text-primary" type="button" title="Sửa khách hàng" data-customer-modal="edit"
                                            data-id="<?php echo $row['MaKhachHang']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['HoTen'], ENT_QUOTES); ?>"
                                            data-username="<?php echo htmlspecialchars($row['TenDangNhap'] ?? '', ENT_QUOTES); ?>"
                                            data-phone="<?php echo htmlspecialchars($row['SoDienThoai'] ?? '', ENT_QUOTES); ?>"
                                            data-email="<?php echo htmlspecialchars($row['Email'] ?? '', ENT_QUOTES); ?>"
                                            data-address="<?php echo htmlspecialchars($row['DiaChi'] ?? '', ENT_QUOTES); ?>"><i class="fas fa-edit"></i></button>
                                        <a href="index.php?page=customers&action=delete&id=<?php echo $row['MaKhachHang']; ?>" class="btn-icon text-danger" onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?');"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <div class="customer-modal" id="customerModal" aria-hidden="true">
        <div class="customer-modal-backdrop" data-close-customer-modal></div>
        <section class="customer-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="customerModalTitle">
            <div class="customer-modal-header">
                <div><h2 id="customerModalTitle">Thêm khách hàng</h2><p>Thông tin tài khoản và liên hệ</p></div>
                <button class="modal-close" type="button" aria-label="Đóng" data-close-customer-modal><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="index.php?page=customers" id="customerForm">
                <input type="hidden" name="id" id="customerId" value="">
                <div class="customer-form-grid">
                    <div class="input-group"><label for="customerName">Họ tên <span>*</span></label><input id="customerName" name="name" required></div>
                    <div class="input-group"><label for="customerUsername">Tên đăng nhập <span>*</span></label><input id="customerUsername" name="username" required></div>
                    <div class="input-group"><label for="customerPassword">Mật khẩu <span id="passwordRequired">*</span></label><input id="customerPassword" name="password" type="password" autocomplete="new-password"><small id="passwordHint">Mật khẩu đăng nhập của khách hàng</small></div>
                    <div class="input-group"><label for="customerPhone">Số điện thoại</label><input id="customerPhone" name="phone" type="tel"></div>
                    <div class="input-group"><label for="customerEmail">Email</label><input id="customerEmail" name="email" type="email"></div>
                    <div class="input-group customer-form-wide"><label for="customerAddress">Địa chỉ</label><input id="customerAddress" name="address"></div>
                </div>
                <div class="customer-modal-footer"><button class="btn btn-outline" type="button" data-close-customer-modal>Hủy</button><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> Lưu khách hàng</button></div>
            </form>
        </section>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
