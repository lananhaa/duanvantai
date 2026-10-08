<?php
$statuses = ['Cho xac nhan', 'Da xac nhan', 'Cho phan cong', 'Da phan cong', 'Da nhan hang', 'Dang van chuyen', 'Da giao hang', 'Hoan tat', 'Da huy', 'Hoan hang'];
$statusClasses = [
    'Da huy' => 'status-huy',
    'Hoan tat' => 'status-hoan-thanh',
    'Da giao hang' => 'status-hoan-thanh',
    'Dang van chuyen' => 'status-dang-giao',
    'Da nhan hang' => 'status-dang-giao'
];
$editDetail = $editOrder['detail'] ?? [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <?php include 'views/menu.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="search-bar">
                <form action="index.php" method="GET" class="order-search-form">
                    <input type="hidden" name="page" value="donhang">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="keyword" placeholder="Tìm mã đơn, khách hàng, người nhận..." value="<?php echo htmlspecialchars($keyword); ?>">
                </form>
            </div>
            <div class="topbar-right">
                <button class="icon-btn notification-btn"><i class="far fa-bell"></i><span class="badge">3</span></button>
                <div class="user-dropdown"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User"><span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Khách'); ?>!</span></div>
            </div>
        </header>
        <div class="content-area">
            <div class="page-header">
                <div><h1 class="page-title">Quản lý đơn hàng</h1><p class="page-subtitle">Tạo, theo dõi và xử lý toàn bộ đơn vận chuyển</p></div>
                <div class="header-actions"><a class="btn btn-primary" href="index.php?page=taodonhang"><i class="fas fa-plus"></i> Tạo đơn hàng</a></div>
            </div>
            <?php if ($message !== ''): ?><div class="alert-message <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
            <div class="card order-filter-card">
                <form action="index.php" method="GET" class="order-filter-form">
                    <input type="hidden" name="page" value="donhang">
                    <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                    <label>Trạng thái
                        <select name="status" onchange="this.form.submit()"><option value="">Tất cả trạng thái</option><?php foreach ($statuses as $item): ?><option value="<?php echo htmlspecialchars($item); ?>" <?php echo $status === $item ? 'selected' : ''; ?>><?php echo htmlspecialchars($item); ?></option><?php endforeach; ?></select>
                    </label>
                    <a class="btn btn-outline" href="index.php?page=donhang"><i class="fas fa-rotate-left"></i> Xóa lọc</a>
                </form>
            </div>
            <div class="card"><div class="card-body p-0"><div class="table-responsive">
                <table class="table"><thead><tr><th>Mã ĐH</th><th>Ngày tạo</th><th>Khách hàng</th><th>Người nhận</th><th>Khối lượng</th><th>Tổng phí</th><th>Trạng thái</th><th class="text-center">Thao tác</th></tr></thead>
                <tbody>
                <?php if (empty($orders)): ?><tr><td colspan="8" class="text-center">Không tìm thấy đơn hàng.</td></tr>
                <?php else: foreach ($orders as $row): $rowStatus = $row['TrangThaiDonHang']; $statusClass = $statusClasses[$rowStatus] ?? 'status-cho-xu-ly'; ?>
                    <tr class="order-row" data-detail-url="index.php?page=donhang&view=<?php echo (int) $row['MaDonHang']; ?>" tabindex="0" role="link" aria-label="Xem chi tiết đơn hàng #<?php echo (int) $row['MaDonHang']; ?>">
                        <td class="font-medium">#<?php echo $row['MaDonHang']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['NgayTao'])); ?></td>
                        <td><?php echo htmlspecialchars($row['TenKhachHang'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(($row['TenNguoiNhan'] ?? '') . ' (' . ($row['SDTNhan'] ?? '') . ')'); ?></td>
                        <td><?php echo number_format((float) $row['TongKhoiLuong'], 1, ',', '.'); ?> kg</td>
                        <td class="font-medium text-success"><?php echo number_format((float) $row['TongPhi'], 0, ',', '.'); ?>đ</td>
                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($rowStatus); ?></span></td>
                        <td class="text-center order-actions">
                            <a class="btn-icon text-primary" title="Xem chi tiết" href="index.php?page=donhang&view=<?php echo $row['MaDonHang']; ?>"><i class="fas fa-eye"></i></a>
                            <a class="btn-icon text-warning" title="Sửa đơn hàng" href="index.php?page=taodonhang&edit=<?php echo $row['MaDonHang']; ?>"><i class="fas fa-edit"></i></a>
                            <?php if (in_array($rowStatus, ['Cho xac nhan', 'Da xac nhan', 'Cho phan cong'], true)): ?><a class="btn-icon text-danger" title="Hủy đơn hàng" href="index.php?page=donhang&action=cancel&id=<?php echo $row['MaDonHang']; ?>" onclick="const reason = prompt('Nhập lý do hủy đơn:'); if (!reason) return false; this.href += '&reason=' + encodeURIComponent(reason); return confirm('Xác nhận hủy đơn hàng này?');"><i class="fas fa-ban"></i></a><?php endif; ?>
                            <?php if (($role ?? 0) !== 1): ?><a class="btn-icon text-danger" title="Xóa đơn hàng" href="index.php?page=donhang&action=delete&id=<?php echo $row['MaDonHang']; ?>" onclick="return confirm('Xóa đơn hàng này? Dữ liệu chi tiết liên quan cũng sẽ bị xóa.');"><i class="fas fa-trash"></i></a><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody></table>
            </div></div></div>
        </div>
    </main>
    <div class="order-modal <?php echo ($editOrder || $viewOrder) ? 'is-open' : ''; ?>" id="orderModal" aria-hidden="<?php echo ($editOrder || $viewOrder) ? 'false' : 'true'; ?>">
        <div class="customer-modal-backdrop" data-close-order-modal></div>
        <section class="customer-modal-dialog order-dialog" role="dialog" aria-modal="true">
            <?php $isReadOnly = (bool) $viewOrder; $formOrder = $editOrder ?: $viewOrder; $formDetail = $formOrder['detail'] ?? []; ?>
            <div class="customer-modal-header"><div><h2><?php echo $isReadOnly ? 'Chi tiết đơn hàng' : ($editOrder ? 'Sửa đơn hàng' : 'Tạo đơn hàng'); ?></h2><p>Thông tin nhận hàng, giao hàng và hàng hóa</p></div><a class="modal-close" href="index.php?page=donhang" aria-label="Đóng"><i class="fas fa-times"></i></a></div>
            <?php if ($isReadOnly && $formOrder): ?>
                <div class="order-detail-grid" style="grid-template-columns: 1fr 1fr; gap: 15px; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #eee;">
                    <div style="grid-column: 1 / -1; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px; color: #ee4d2d;">Thông tin chung</div>
                    <div><strong>Mã đơn:</strong> #<?php echo $formOrder['MaDonHang']; ?></div>
                    <div><strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($formOrder['NgayTao'])); ?></div>
                    <div><strong>Trạng thái:</strong> <span class="status-badge <?php echo $statusClasses[$formOrder['TrangThai']] ?? 'status-cho-xu-ly'; ?>"><?php echo htmlspecialchars($formOrder['TrangThai']); ?></span></div>
                    <div><strong>Tài xế phân công:</strong> <span style="font-weight: 500; color: #28a745;"><?php echo htmlspecialchars($formOrder['TenTaiXe'] ?? 'Chưa phân công'); ?></span></div>
                    <?php if ($formOrder['LyDoHuy']): ?>
                    <div style="grid-column: 1 / -1;"><strong>Lý do hủy:</strong> <span style="color: #dc3545;"><?php echo htmlspecialchars($formOrder['LyDoHuy']); ?></span></div>
                    <?php endif; ?>
                    
                    <div style="grid-column: 1 / -1; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px; margin-top: 10px; color: #ee4d2d;">Thông tin khách hàng & Giao nhận</div>
                    <div><strong>Khách hàng:</strong> <?php echo htmlspecialchars($formOrder['TenKhachHang']); ?></div>
                    <div><strong>Tuyến giao:</strong> <?php echo htmlspecialchars($formOrder['TenTuyen'] ?? ''); ?></div>
                    <div style="grid-column: 1 / -1; background: #f9f9f9; padding: 10px; border-radius: 4px;">
                        <strong><i class="fas fa-map-marker-alt" style="color: #007bff;"></i> Nhận hàng:</strong>
                        <br>SĐT: <?php echo htmlspecialchars($formOrder['SDTGoi'] ?? ''); ?>
                        <br>Địa chỉ: <?php echo htmlspecialchars($formOrder['DiaChiNhan'] . ', ' . $formOrder['PhuongXaNhan'] . ', ' . $formOrder['QuanHuyenNhan'] . ', ' . $formOrder['TinhThanhNhan']); ?>
                    </div>
                    <div style="grid-column: 1 / -1; background: #f9f9f9; padding: 10px; border-radius: 4px;">
                        <strong><i class="fas fa-map-marker-alt" style="color: #28a745;"></i> Giao hàng:</strong>
                        <br>Người nhận: <?php echo htmlspecialchars($formOrder['TenNguoiNhan'] . ' - ' . $formOrder['SDTNhan']); ?>
                        <br>Địa chỉ: <?php echo htmlspecialchars($formOrder['DiaChiGiao'] . ', ' . $formOrder['PhuongXaGiao'] . ', ' . $formOrder['QuanHuyenGiao'] . ', ' . $formOrder['TinhThanhGiao']); ?>
                    </div>

                    <div style="grid-column: 1 / -1; font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px; margin-top: 10px; color: #ee4d2d;">Thông tin hàng hóa & Cước phí</div>
                    <div style="grid-column: 1 / -1;">
                        <table class="table" style="margin-bottom: 0;">
                            <thead style="background: #f1f1f1;"><tr><th>Hàng hóa</th><th>SL</th><th>KL (kg)</th><th>Thành tiền</th></tr></thead>
                            <tbody>
                            <?php foreach ($formOrder['detail'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['TenHangHoa']); ?></td>
                                    <td><?php echo (int) $item['SoLuong']; ?></td>
                                    <td><?php echo number_format((float) $item['KhoiLuong'], 1, ',', '.'); ?></td>
                                    <td><?php echo number_format((float) $item['ThanhTien'], 0, ',', '.'); ?>đ</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div><strong>Tổng khối lượng:</strong> <?php echo number_format((float) $formOrder['TongKhoiLuong'], 1, ',', '.'); ?> kg</div>
                    <div><strong>Tiền hàng:</strong> <?php echo number_format((float) $formOrder['TienHang'], 0, ',', '.'); ?>đ</div>
                    <div><strong>Phí vận chuyển:</strong> <?php echo number_format((float) $formOrder['PhiVanChuyen'], 0, ',', '.'); ?>đ</div>
                    <?php if ((float)$formOrder['PhiHoan'] > 0): ?>
                    <div><strong>Phí hoàn:</strong> <?php echo number_format((float) $formOrder['PhiHoan'], 0, ',', '.'); ?>đ</div>
                    <?php endif; ?>
                    <div style="grid-column: 1 / -1; text-align: right; font-size: 16px;"><strong>Tổng phí:</strong> <span style="color: #ee4d2d; font-weight: bold; font-size: 18px;"><?php echo number_format((float) $formOrder['TongPhi'], 0, ',', '.'); ?>đ</span></div>
                </div>
                <div class="customer-modal-footer"><a class="btn btn-outline" href="index.php?page=donhang">Đóng</a></div>
            <?php else: ?>
                <style>
                .spx-form-container { background: #f5f5f5; padding: 15px; display: flex; flex-direction: column; gap: 15px; border-radius: 4px; }
                .spx-section { background: #fff; border-radius: 4px; border: 1px solid #e5e5e5; overflow: hidden; }
                .spx-section-header { background: #fafafa; padding: 12px 15px; font-weight: 600; font-size: 14px; border-bottom: 1px solid #e5e5e5; color: #333; display: flex; align-items: center; gap: 8px; }
                .spx-section-header::before { content: ""; width: 3px; height: 16px; background: #ee4d2d; display: inline-block; }
                .spx-section-body { padding: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
                .spx-section-body.full { grid-template-columns: 1fr; }
                .spx-input-group label { display: block; font-size: 13px; margin-bottom: 6px; color: #555; }
                .spx-input-group label span { color: #ee4d2d; margin-right: 3px; }
                .spx-input-group select, .spx-input-group input { width: 100%; padding: 9px 12px; border: 1px solid #d9d9d9; border-radius: 4px; font-family: inherit; font-size: 13px; outline: none; transition: 0.2s; }
                .spx-input-group select:focus, .spx-input-group input:focus { border-color: #ee4d2d; box-shadow: 0 0 0 2px rgba(238, 77, 45, 0.1); }
                .order-dialog { width: min(900px, 100%) !important; }
                .btn-submit-spx { background: #ee4d2d; color: #fff; border: none; box-shadow: none; }
                .btn-submit-spx:hover { background: #d73a1e; transform: none; }
                </style>
                <form method="POST" action="index.php?page=donhang" id="orderForm"><input type="hidden" name="id" value="<?php echo htmlspecialchars($editOrder['MaDonHang'] ?? ''); ?>">
                    <div class="spx-form-container">
                        <div class="spx-section">
                            <div class="spx-section-header">1. Đại chỉ người gửi</div>
                            <div class="spx-section-body">
                                <div class="spx-input-group"><label><span>*</span>Điện thoại</label><input type="text" name="pickup_phone" value="<?php echo htmlspecialchars($editOrder['SDTGoi'] ?? ''); ?>" required></div>
                                <div class="spx-input-group" style="grid-column: 1 / -1;"><label><span>*</span>Địa chỉ chi tiết</label><input type="text" name="pickup_address" value="<?php echo htmlspecialchars($editOrder['DiaChiNhan'] ?? ''); ?>" required></div>
                                <div class="spx-input-group"><label><span>*</span>Tỉnh / Thành phố</label><select name="pickup_province" id="pickup_province" data-selected="<?php echo htmlspecialchars($editOrder['TinhThanhNhan'] ?? ''); ?>" required><option value="">-- Chọn Tỉnh / Thành phố --</option></select></div>
                                <div class="spx-input-group"><label><span>*</span>Quận / Huyện</label><select name="pickup_district" id="pickup_district" data-selected="<?php echo htmlspecialchars($editOrder['QuanHuyenNhan'] ?? ''); ?>" required><option value="">-- Chọn Quận / Huyện --</option></select></div>
                                <div class="spx-input-group"><label>Phường / Xã</label><select name="pickup_ward" id="pickup_ward" data-selected="<?php echo htmlspecialchars($editOrder['PhuongXaNhan'] ?? ''); ?>"><option value="">-- Chọn Phường / Xã --</option></select></div>
                            </div>
                        </div>
                        
                        <div class="spx-section">
                            <div class="spx-section-header">2. Địa chỉ người nhận</div>
                            <div class="spx-section-body">
                                <div class="spx-input-group"><label><span>*</span>Điện thoại</label><input type="text" name="delivery_phone" value="<?php echo htmlspecialchars($editOrder['SDTNhan'] ?? ''); ?>" required></div>
                                <div class="spx-input-group"><label><span>*</span>Tên người nhận</label><input type="text" name="delivery_name" value="<?php echo htmlspecialchars($editOrder['TenNguoiNhan'] ?? ''); ?>" required></div>
                                <div class="spx-input-group" style="grid-column: 1 / -1;"><label><span>*</span>Địa chỉ chi tiết</label><input type="text" name="delivery_address" value="<?php echo htmlspecialchars($editOrder['DiaChiGiao'] ?? ''); ?>" required></div>
                                <div class="spx-input-group"><label><span>*</span>Tỉnh / Thành phố</label><select name="delivery_province" id="delivery_province" data-selected="<?php echo htmlspecialchars($editOrder['TinhThanhGiao'] ?? ''); ?>" required><option value="">-- Chọn Tỉnh / Thành phố --</option></select></div>
                                <div class="spx-input-group"><label><span>*</span>Quận / Huyện</label><select name="delivery_district" id="delivery_district" data-selected="<?php echo htmlspecialchars($editOrder['QuanHuyenGiao'] ?? ''); ?>" required><option value="">-- Chọn Quận / Huyện --</option></select></div>
                                <div class="spx-input-group"><label>Phường / Xã</label><select name="delivery_ward" id="delivery_ward" data-selected="<?php echo htmlspecialchars($editOrder['PhuongXaGiao'] ?? ''); ?>"><option value="">-- Chọn Phường / Xã --</option></select></div>
                            </div>
                        </div>

                        <div class="spx-section" style="display: grid; grid-template-columns: 1fr 1fr; border: none; background: transparent; gap: 15px;">
                            <div class="spx-section" style="margin: 0; border: 1px solid #e5e5e5;">
                                <div class="spx-section-header" style="border-bottom: 1px solid #e5e5e5;">3. Loại dịch vụ</div>
                                <div class="spx-section-body full">
                                    <div class="spx-input-group"><label><span>*</span>Tuyến giao</label><select name="route_id" id="routeSelect" required><option value="">-- Chọn tuyến giao --</option><?php foreach ($options['tuyengiao'] as $item): ?><option value="<?php echo $item['MaTuyenGiao']; ?>" <?php echo (($editOrder['MaTuyenGiao'] ?? '') == $item['MaTuyenGiao']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($item['TenTuyen']); ?></option><?php endforeach; ?></select></div>
                                    <div id="routeMessage" style="font-size: 13px; margin-top: -5px;"></div>
                                </div>
                            </div>
                            <div class="spx-section" style="margin: 0; border: 1px solid #e5e5e5;">
                                <div class="spx-section-header" style="border-bottom: 1px solid #e5e5e5;">4. Thông tin chung</div>
                                <div class="spx-section-body full">
                                    <div class="spx-input-group"><label><span>*</span>Khách hàng</label><select name="customer_id" required><?php foreach ($options['khachhang'] as $item): ?><option value="<?php echo $item['MaKhachHang']; ?>" <?php echo (($editOrder['MaKhachHang'] ?? '') == $item['MaKhachHang']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($item['HoTen']); ?></option><?php endforeach; ?></select></div>
                                </div>
                            </div>
                        </div>

                        <div class="spx-section">
                            <div class="spx-section-header">5. Thông tin bưu gửi</div>
                            <div class="spx-section-body" id="productList">
                                <?php $details = !empty($formOrder['detail']) ? $formOrder['detail'] : [[]]; ?>
                                <?php foreach ($details as $index => $det): ?>
                                <div class="product-item" style="grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border: 1px dashed #ddd; padding: 10px; position: relative; background: #fafafa;">
                                    <div class="spx-input-group" style="grid-column: 1 / -1;"><label><span>*</span>Tên sản phẩm</label><input type="text" name="products[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($det['TenHangHoa'] ?? ''); ?>" required></div>
                                    <div class="spx-input-group"><label><span>*</span>Khối lượng (kg/đv)</label><input type="number" step="0.1" min="0" name="products[<?php echo $index; ?>][product_weight]" value="<?php echo htmlspecialchars($det['KhoiLuongDonVi'] ?? 0); ?>" required class="calc-trigger weight-input"></div>
                                    <div class="spx-input-group"><label><span>*</span>Giá trị bưu gửi</label><input type="number" name="products[<?php echo $index; ?>][unit_price]" min="0" step="1000" value="<?php echo htmlspecialchars($det['DonGia'] ?? 0); ?>" required></div>
                                    <div class="spx-input-group"><label><span>*</span>Số lượng</label><input type="number" name="products[<?php echo $index; ?>][quantity]" min="1" value="<?php echo htmlspecialchars($det['SoLuong'] ?? 1); ?>" required class="calc-trigger qty-input"></div>
                                    <?php if ($index > 0): ?><button type="button" class="btn-remove-prod" style="position: absolute; top: 10px; right: 10px; background: #ee4d2d; color: white; border: none; padding: 3px 8px; cursor: pointer; border-radius: 4px; font-size: 12px;">Xóa</button><?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div style="padding: 0 15px 15px;"><button type="button" class="btn btn-outline" id="btnAddProduct" style="width: 100%; border-style: dashed; color: #ee4d2d; border-color: #ee4d2d; background: transparent;"><i class="fas fa-plus"></i> Thêm sản phẩm</button></div>
                        </div>
                        
                        <div class="spx-section">
                            <div class="spx-section-header">6. Dịch vụ & Khác</div>
                            <div class="spx-section-body">
                                <div class="spx-input-group"><label>Phí hoàn (Nếu có)</label><input type="number" name="return_fee" min="0" step="1000" value="<?php echo htmlspecialchars($editOrder['PhiHoan'] ?? 0); ?>"></div>
                                <div class="spx-input-group" style="grid-column: 1 / -1;"><div id="calcFeeDisplay" style="color: #ee4d2d; font-weight: 600; background: #fff4f4; padding: 12px; border: 1px dashed #ee4d2d; text-align: center; border-radius: 4px;">Phí vận chuyển tạm tính: Đang tính toán...</div></div>
                                <?php if ($editOrder): ?>
                                <div class="spx-input-group"><label>Trạng thái</label><select name="status"><?php foreach ($statuses as $item): ?><option value="<?php echo htmlspecialchars($item); ?>" <?php echo ($editOrder['TrangThai'] === $item) ? 'selected' : ''; ?>><?php echo htmlspecialchars($item); ?></option><?php endforeach; ?></select></div>
                                <div class="spx-input-group"><label>Lý do hủy</label><input name="cancel_reason" value="<?php echo htmlspecialchars($editOrder['LyDoHuy'] ?? ''); ?>"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="customer-modal-footer"><a class="btn btn-outline" href="index.php?page=donhang">Hủy</a><button class="btn btn-primary btn-submit-spx" type="submit"><i class="fas fa-save"></i> Lưu đơn hàng</button></div>
                </form>
            <?php endif; ?>
        </section>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAddProduct = document.getElementById('btnAddProduct');
        const productList = document.getElementById('productList');
        let prodIndex = <?php echo isset($details) ? count($details) : 1; ?>;
        
        if (btnAddProduct) {
            btnAddProduct.addEventListener('click', function() {
                const html = `<div class="product-item" style="grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border: 1px dashed #ddd; padding: 10px; position: relative; background: #fafafa; margin-top: 15px;">
                    <div class="spx-input-group" style="grid-column: 1 / -1;"><label><span>*</span>Tên sản phẩm</label><input type="text" name="products[${prodIndex}][name]" required></div>
                    <div class="spx-input-group"><label><span>*</span>Khối lượng (kg/đv)</label><input type="number" step="0.1" min="0" name="products[${prodIndex}][product_weight]" value="0" required class="calc-trigger weight-input"></div>
                    <div class="spx-input-group"><label><span>*</span>Giá trị bưu gửi</label><input type="number" name="products[${prodIndex}][unit_price]" min="0" step="1000" value="0" required></div>
                    <div class="spx-input-group"><label><span>*</span>Số lượng</label><input type="number" name="products[${prodIndex}][quantity]" min="1" value="1" required class="calc-trigger qty-input"></div>
                    <button type="button" class="btn-remove-prod" style="position: absolute; top: 10px; right: 10px; background: #ee4d2d; color: white; border: none; padding: 3px 8px; cursor: pointer; border-radius: 4px; font-size: 12px;">Xóa</button>
                </div>`;
                productList.insertAdjacentHTML('beforeend', html);
                prodIndex++;
                attachEvents();
            });
        }

        function attachEvents() {
            document.querySelectorAll('.btn-remove-prod').forEach(btn => {
                btn.onclick = function() {
                    this.parentElement.remove();
                    calculateFee();
                }
            });
            document.querySelectorAll('.calc-trigger').forEach(inp => {
                inp.oninput = calculateFee;
            });
            const routeSelect = document.getElementById('routeSelect');
            if (routeSelect) routeSelect.onchange = calculateFee;
        }

        function calculateFee() {
            const routeId = document.getElementById('routeSelect')?.value;
            if (!routeId) return;
            
            let totalWeight = 0;
            document.querySelectorAll('.product-item').forEach(item => {
                const w = parseFloat(item.querySelector('.weight-input').value) || 0;
                const q = parseInt(item.querySelector('.qty-input').value) || 0;
                totalWeight += w * q;
            });
            
            fetch(`index.php?page=donhang&action=calc_fee&route_id=${routeId}&weight=${totalWeight}`)
            .then(res => res.json())
            .then(data => {
                const feeDisplay = document.getElementById('calcFeeDisplay');
                if (feeDisplay) {
                    feeDisplay.innerHTML = 'Phí vận chuyển tạm tính: <strong>' + new Intl.NumberFormat('vi-VN').format(data.amount) + 'đ</strong>';
                }
            }).catch(e => console.error(e));
        }

        const routeSelect = document.getElementById('routeSelect');
        const routeMessage = document.getElementById('routeMessage');
        const pickupDist = document.getElementById('pickup_district');
        const deliveryDist = document.getElementById('delivery_district');
        
        let originalRouteOptions = routeSelect ? routeSelect.innerHTML : '';
        const isEdit = document.querySelector('input[name="id"]').value !== '';

        if (!isEdit && routeSelect && pickupDist && deliveryDist) {
            routeSelect.disabled = true;
            routeSelect.innerHTML = '<option value="">-- Vui lòng chọn Quận/Huyện trước --</option>' + originalRouteOptions;
        }

        const apiBase = 'https://provinces.open-api.vn/api/';
        
        function populateSelect(selectEl, data, selectedName = '') {
            const firstText = selectEl.options.length > 0 ? selectEl.options[0].text : '-- Chọn --';
            selectEl.innerHTML = '<option value="">' + firstText + '</option>';
            data.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.name;
                opt.dataset.code = item.code;
                opt.textContent = item.name;
                if (item.name === selectedName || (selectedName && item.name.includes(selectedName))) {
                    opt.selected = true;
                }
                selectEl.appendChild(opt);
            });
            if(selectedName) {
                selectEl.dataset.selected = ''; // Prevent infinite loops
                selectEl.dispatchEvent(new Event('change'));
            }
        }

        function initLocation(prefix) {
            const provSelect = document.getElementById(prefix + '_province');
            const distSelect = document.getElementById(prefix + '_district');
            const wardSelect = document.getElementById(prefix + '_ward');
            
            if(!provSelect) return;

            fetch(apiBase + 'p/')
                .then(r => r.json())
                .then(data => populateSelect(provSelect, data, provSelect.dataset.selected));
                
            provSelect.addEventListener('change', function() {
                const code = this.options[this.selectedIndex]?.dataset?.code;
                if(code) {
                    fetch(apiBase + 'p/' + code + '?depth=2')
                        .then(r => r.json())
                        .then(data => populateSelect(distSelect, data.districts, distSelect.dataset.selected));
                } else {
                    distSelect.innerHTML = '<option value="">-- Chọn Quận / Huyện --</option>';
                    wardSelect.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
                    distSelect.dispatchEvent(new Event('change'));
                }
            });
            
            distSelect.addEventListener('change', function() {
                const code = this.options[this.selectedIndex]?.dataset?.code;
                if(code) {
                    fetch(apiBase + 'd/' + code + '?depth=2')
                        .then(r => r.json())
                        .then(data => populateSelect(wardSelect, data.wards, wardSelect.dataset.selected));
                } else {
                    wardSelect.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
                }
            });
        }
        
        initLocation('pickup');
        initLocation('delivery');

        let debounceTimer;
        function onDistrictChange() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const pickup = pickupDist.value.trim();
                const delivery = deliveryDist.value.trim();
                
                if (!pickup || !delivery) {
                    routeSelect.disabled = true;
                    routeSelect.innerHTML = '<option value="">-- Vui lòng nhập Quận/Huyện trước --</option>' + originalRouteOptions;
                    if (routeMessage) routeMessage.innerHTML = '';
                    return;
                }
                
                routeSelect.disabled = false;
                if(routeSelect.querySelector('option[value=""]')) {
                     routeSelect.querySelector('option[value=""]').text = '-- Chọn tuyến giao --';
                }
                
                let totalWeight = 0;
                document.querySelectorAll('.product-item').forEach(item => {
                    const w = parseFloat(item.querySelector('.weight-input').value) || 0;
                    const q = parseInt(item.querySelector('.qty-input').value) || 0;
                    totalWeight += w * q;
                });

                fetch(`index.php?page=donhang&action=find_route&pickup=${encodeURIComponent(pickup)}&delivery=${encodeURIComponent(delivery)}&weight=${totalWeight}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.route_id) {
                        routeSelect.value = data.route_id;
                        if (routeMessage) routeMessage.innerHTML = '<span style="color: green;"><i class="fas fa-check-circle"></i> Đã tự động xác định tuyến giao và áp dụng mức phí.</span>';
                        const feeDisplay = document.getElementById('calcFeeDisplay');
                        if (feeDisplay) {
                            feeDisplay.innerHTML = 'Phí vận chuyển tạm tính: <strong>' + new Intl.NumberFormat('vi-VN').format(data.fee.amount) + 'đ</strong>';
                        }
                    } else {
                        if (routeMessage) routeMessage.innerHTML = '<span style="color: #ee4d2d;"><i class="fas fa-exclamation-triangle"></i> Chưa có tuyến giao tự động cho khu vực này, vui lòng chọn tuyến thủ công.</span>';
                    }
                }).catch(e => console.error(e));
            }, 500);
        }

        if (pickupDist && deliveryDist) {
            pickupDist.addEventListener('change', onDistrictChange);
            deliveryDist.addEventListener('change', onDistrictChange);
        }

        const orderForm = document.getElementById('orderForm');
        if (orderForm) {
            orderForm.addEventListener('submit', function(e) {
                if (!routeSelect || !routeSelect.value || routeSelect.value === '') {
                    e.preventDefault();
                    alert('Vui lòng chọn Tuyến giao hợp lệ trước khi lưu đơn!');
                    if (routeSelect) {
                        routeSelect.style.borderColor = 'red';
                        routeSelect.focus();
                    }
                }
            });
        }
        
        attachEvents();
        if (isEdit) {
            setTimeout(calculateFee, 500);
        }
    });
    </script>
</body>
</html>
