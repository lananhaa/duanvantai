<?php
$statuses = ['Cho phan cong', 'Da phan cong', 'Da nhan hang', 'Dang van chuyen', 'Da giao hang', 'Hoan tat', 'Da huy', 'Hoan hang'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo đơn hàng mới - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .spx-form-container { background: #f5f5f5; padding: 25px; display: flex; flex-direction: column; gap: 20px; border-radius: 8px; }
    .spx-section { background: #fff; border-radius: 8px; border: 1px solid #e5e5e5; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .spx-section-header { background: #fafafa; padding: 15px 20px; font-weight: 600; font-size: 15px; border-bottom: 1px solid #e5e5e5; color: #333; display: flex; align-items: center; gap: 10px; }
    .spx-section-header::before { content: ""; width: 4px; height: 18px; background: #ee4d2d; display: inline-block; border-radius: 2px; }
    .spx-section-body { padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .spx-section-body.full { grid-template-columns: 1fr; }
    .spx-input-group label { display: block; font-size: 14px; margin-bottom: 8px; color: #444; font-weight: 500; }
    .spx-input-group label span { color: #ee4d2d; margin-right: 3px; }
    .spx-input-group select, .spx-input-group input { width: 100%; padding: 10px 14px; border: 1px solid #d9d9d9; border-radius: 6px; font-family: inherit; font-size: 14px; outline: none; transition: 0.2s; }
    .spx-input-group select:focus, .spx-input-group input:focus { border-color: #ee4d2d; box-shadow: 0 0 0 3px rgba(238, 77, 45, 0.1); }
    .btn-submit-spx { background: #ee4d2d; color: #fff; border: none; box-shadow: none; padding: 12px 24px; font-size: 15px; }
    .btn-submit-spx:hover { background: #d73a1e; transform: none; }
    .page-title-wrap { margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
    .btn-back { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: #fff; border: 1px solid #ddd; color: #333; transition: all 0.2s; }
    .btn-back:hover { background: #f0f0f0; border-color: #ccc; }
    </style>
</head>
<body class="dashboard-body">
    <?php include 'views/menu.php'; ?>
    <main class="main-content">
        <header class="topbar">
            <div class="search-bar"></div>
            <div class="topbar-right">
                <div class="user-dropdown"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User"><span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Khách'); ?>!</span></div>
            </div>
        </header>
        <div class="content-area">
            <div class="page-title-wrap">
                <a href="index.php?page=donhang" class="btn-back"><i class="fas fa-arrow-left"></i></a>
                <div>
                    <h1 class="page-title">Tạo đơn hàng mới</h1>
                    <p class="page-subtitle">Điền thông tin chi tiết để tạo đơn vận chuyển mới</p>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert-message <?php echo htmlspecialchars($messageType ?? 'error'); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=donhang" id="orderForm">
                <div class="spx-form-container">
                    <div class="spx-section">
                        <div class="spx-section-header">1. Địa chỉ người gửi</div>
                        <div class="spx-section-body">
                            <div class="spx-input-group"><label><span>*</span>Điện thoại</label><input type="text" name="pickup_phone" required></div>
                            <div class="spx-input-group" style="grid-column: 1 / -1;"><label><span>*</span>Địa chỉ chi tiết</label><input type="text" name="pickup_address" required></div>
                            <div class="spx-input-group"><label><span>*</span>Tỉnh / Thành phố</label><select name="pickup_province" id="pickup_province" required><option value="">-- Chọn Tỉnh / Thành phố --</option></select></div>
                            <div class="spx-input-group"><label><span>*</span>Quận / Huyện</label><select name="pickup_district" id="pickup_district" required><option value="">-- Chọn Quận / Huyện --</option></select></div>
                            <div class="spx-input-group"><label>Phường / Xã</label><select name="pickup_ward" id="pickup_ward"><option value="">-- Chọn Phường / Xã --</option></select></div>
                        </div>
                    </div>
                    
                    <div class="spx-section">
                        <div class="spx-section-header">2. Địa chỉ người nhận</div>
                        <div class="spx-section-body">
                            <div class="spx-input-group"><label><span>*</span>Điện thoại</label><input type="text" name="delivery_phone" required></div>
                            <div class="spx-input-group"><label><span>*</span>Tên người nhận</label><input type="text" name="delivery_name" required></div>
                            <div class="spx-input-group" style="grid-column: 1 / -1;"><label><span>*</span>Địa chỉ chi tiết</label><input type="text" name="delivery_address" required></div>
                            <div class="spx-input-group"><label><span>*</span>Tỉnh / Thành phố</label><select name="delivery_province" id="delivery_province" required><option value="">-- Chọn Tỉnh / Thành phố --</option></select></div>
                            <div class="spx-input-group"><label><span>*</span>Quận / Huyện</label><select name="delivery_district" id="delivery_district" required><option value="">-- Chọn Quận / Huyện --</option></select></div>
                            <div class="spx-input-group"><label>Phường / Xã</label><select name="delivery_ward" id="delivery_ward"><option value="">-- Chọn Phường / Xã --</option></select></div>
                        </div>
                    </div>

                    <div class="spx-section" style="display: grid; grid-template-columns: 1fr 1fr; border: none; background: transparent; gap: 20px; box-shadow: none;">
                        <div class="spx-section" style="margin: 0;">
                            <div class="spx-section-header">3. Loại dịch vụ</div>
                            <div class="spx-section-body full">
                                <div class="spx-input-group"><label><span>*</span>Tuyến giao</label><select name="route_id" id="routeSelect" required><option value="">-- Chọn tuyến giao --</option><?php foreach ($options['tuyengiao'] as $item): ?><option value="<?php echo $item['MaTuyenGiao']; ?>"><?php echo htmlspecialchars($item['TenTuyen']); ?></option><?php endforeach; ?></select></div>
                                <div id="routeMessage" style="font-size: 13px; margin-top: -5px;"></div>
                            </div>
                        </div>
                        <div class="spx-section" style="margin: 0;">
                            <div class="spx-section-header">4. Thông tin chung</div>
                            <div class="spx-section-body full">
                                <div class="spx-input-group"><label><span>*</span>Khách hàng</label><select name="customer_id" required><?php foreach ($options['khachhang'] as $item): ?><option value="<?php echo $item['MaKhachHang']; ?>"><?php echo htmlspecialchars($item['HoTen']); ?></option><?php endforeach; ?></select></div>
                            </div>
                        </div>
                    </div>

                    <div class="spx-section">
                        <div class="spx-section-header">5. Thông tin bưu gửi</div>
                        <div class="spx-section-body" id="productList">
                            <div class="product-item" style="grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border: 1px dashed #ddd; padding: 15px; position: relative; background: #fafafa; border-radius: 6px;">
                                <div class="spx-input-group" style="grid-column: 1 / -1;"><label><span>*</span>Tên sản phẩm</label><input type="text" name="products[0][name]" required></div>
                                <div class="spx-input-group"><label><span>*</span>Khối lượng (kg/đv)</label><input type="number" step="0.1" min="0" name="products[0][product_weight]" value="0" required class="calc-trigger weight-input"></div>
                                <div class="spx-input-group"><label><span>*</span>Giá trị bưu gửi</label><input type="number" name="products[0][unit_price]" min="0" step="1000" value="0" required></div>
                                <div class="spx-input-group"><label><span>*</span>Số lượng</label><input type="number" name="products[0][quantity]" min="1" value="1" required class="calc-trigger qty-input"></div>
                            </div>
                        </div>
                        <div style="padding: 0 20px 20px;"><button type="button" class="btn btn-outline" id="btnAddProduct" style="width: 100%; border-style: dashed; color: #ee4d2d; border-color: #ee4d2d; background: transparent; padding: 10px;"><i class="fas fa-plus"></i> Thêm sản phẩm</button></div>
                    </div>
                    
                    <div class="spx-section">
                        <div class="spx-section-header">6. Dịch vụ & Khác</div>
                        <div class="spx-section-body">
                            <div class="spx-input-group"><label>Phí hoàn (Nếu có)</label><input type="number" name="return_fee" min="0" step="1000" value="0"></div>
                            <div class="spx-input-group" style="grid-column: 1 / -1;"><div id="calcFeeDisplay" style="color: #ee4d2d; font-weight: 600; background: #fff4f4; padding: 15px; border: 1px dashed #ee4d2d; text-align: center; border-radius: 6px; font-size: 16px;">Phí vận chuyển tạm tính: Đang tính toán...</div></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 15px; margin-top: 10px;">
                        <a class="btn btn-outline" href="index.php?page=donhang" style="padding: 12px 24px;">Hủy</a>
                        <button class="btn btn-primary btn-submit-spx" type="submit"><i class="fas fa-save"></i> Lưu đơn hàng</button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAddProduct = document.getElementById('btnAddProduct');
        const productList = document.getElementById('productList');
        let prodIndex = 1;
        
        if (btnAddProduct) {
            btnAddProduct.addEventListener('click', function() {
                const html = `<div class="product-item" style="grid-column: 1 / -1; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border: 1px dashed #ddd; padding: 15px; position: relative; background: #fafafa; border-radius: 6px; margin-top: 15px;">
                    <div class="spx-input-group" style="grid-column: 1 / -1;"><label><span>*</span>Tên sản phẩm</label><input type="text" name="products[${prodIndex}][name]" required></div>
                    <div class="spx-input-group"><label><span>*</span>Khối lượng (kg/đv)</label><input type="number" step="0.1" min="0" name="products[${prodIndex}][product_weight]" value="0" required class="calc-trigger weight-input"></div>
                    <div class="spx-input-group"><label><span>*</span>Giá trị bưu gửi</label><input type="number" name="products[${prodIndex}][unit_price]" min="0" step="1000" value="0" required></div>
                    <div class="spx-input-group"><label><span>*</span>Số lượng</label><input type="number" name="products[${prodIndex}][quantity]" min="1" value="1" required class="calc-trigger qty-input"></div>
                    <button type="button" class="btn-remove-prod" style="position: absolute; top: 15px; right: 15px; background: #ee4d2d; color: white; border: none; padding: 4px 10px; cursor: pointer; border-radius: 4px; font-size: 13px;">Xóa</button>
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

        if (routeSelect && pickupDist && deliveryDist) {
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
                selectEl.dataset.selected = ''; 
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
                        calculateFee();
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
    });
    </script>
</body>
</html>
