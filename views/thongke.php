<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="dashboard-body">
<?php include 'views/menu.php'; ?>
<main class="main-content">
    <header class="topbar">
        <div class="search-bar">
            <i class="fas fa-chart-line search-icon"></i>
            <span style="color:var(--text-secondary);font-size:14px;">Tổng quan thống kê hệ thống</span>
        </div>
        <div class="topbar-right">
            <div class="user-dropdown">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User">
                <span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Khách'); ?>!</span>
            </div>
        </div>
    </header>

    <div class="content-area">
        <div class="page-header">
            <div>
                <h1 class="page-title">Thống kê giao hàng & Doanh thu</h1>
                <p class="page-subtitle">Phân tích hiệu suất vận hành hệ thống</p>
            </div>
            <div class="header-actions">
                <form action="index.php" method="GET" style="display:flex;gap:8px;align-items:center;">
                    <input type="hidden" name="page" value="thongke">
                    <label style="font-size:13px;color:var(--text-secondary);">Từ:</label>
                    <input type="date" name="date_from" class="form-control" style="width:140px;" value="<?php echo htmlspecialchars($dateFrom); ?>">
                    <label style="font-size:13px;color:var(--text-secondary);">Đến:</label>
                    <input type="date" name="date_to" class="form-control" style="width:140px;" value="<?php echo htmlspecialchars($dateTo); ?>">
                    <button type="submit" class="btn btn-primary" style="padding:8px 16px;">
                        <i class="fas fa-filter"></i> Xem thống kê
                    </button>
                </form>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#4361ee,#3a0ca3)"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Tổng đơn hàng</p>
                    <h3 class="stat-value"><?php echo number_format($summary['TongDon'] ?? 0); ?></h3>
                    <small class="text-muted"><?php echo number_format($summary['DangXuLy'] ?? 0); ?> đang xử lý</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#06d6a0,#0aa372)"><i class="fas fa-check-double"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Hoàn tất</p>
                    <h3 class="stat-value"><?php echo number_format($summary['DonHoanTat'] ?? 0); ?></h3>
                    <?php $rate = $summary['TongDon'] > 0 ? round($summary['DonHoanTat'] / $summary['TongDon'] * 100, 1) : 0; ?>
                    <small style="color:#06d6a0;font-weight:600;"><?php echo $rate; ?>% tỷ lệ thành công</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#f77f00,#d62828)"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Hủy / Hoàn</p>
                    <h3 class="stat-value"><?php echo number_format(($summary['DonHuy'] ?? 0) + ($summary['DonHoanHang'] ?? 0)); ?></h3>
                    <small class="text-muted"><?php echo $summary['DonHuy'] ?? 0; ?> hủy · <?php echo $summary['DonHoanHang'] ?? 0; ?> hoàn</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#7b2ff7,#f107a3)"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Doanh thu</p>
                    <h3 class="stat-value" style="font-size:18px;"><?php echo number_format($summary['TongDoanhThu'] ?? 0, 0, ',', '.'); ?>đ</h3>
                    <small class="text-muted">Phí: <?php echo number_format($summary['DoanhThuPhi'] ?? 0, 0, ',', '.'); ?>đ</small>
                </div>
            </div>
        </div>

        <!-- COD Summary -->
        <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:24px;">
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#06d6a0,#0aa372)"><i class="fas fa-hand-holding-usd"></i></div>
                <div class="stat-info"><p class="stat-label">COD đã thu</p><h3 class="stat-value" style="font-size:18px;color:#06d6a0;"><?php echo number_format($codStats['DaThu'] ?? 0, 0, ',', '.'); ?>đ</h3></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#f77f00,#e67e22)"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><p class="stat-label">COD chưa thu</p><h3 class="stat-value" style="font-size:18px;color:#f77f00;"><?php echo number_format($codStats['ChuaThu'] ?? 0, 0, ',', '.'); ?>đ</h3></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:linear-gradient(135deg,#adb5bd,#6c757d)"><i class="fas fa-ban"></i></div>
                <div class="stat-info"><p class="stat-label">COD không thu</p><h3 class="stat-value" style="font-size:18px;"><?php echo number_format($codStats['KhongThu'] ?? 0, 0, ',', '.'); ?>đ</h3></div>
            </div>
        </div>

        <!-- Charts + Tables 2 cột -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
            <!-- Biểu đồ tròn trạng thái đơn -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie" style="color:#4361ee;"></i> Tỷ lệ đơn hàng</h3></div>
                <div class="card-body" style="display:flex;justify-content:center;align-items:center;padding:20px;">
                    <canvas id="orderStatusChart" width="280" height="280"></canvas>
                </div>
            </div>

            <!-- Biểu đồ cột doanh thu 7 ngày -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar" style="color:#4361ee;"></i> Doanh thu 30 ngày gần nhất</h3></div>
                <div class="card-body" style="padding:20px;">
                    <canvas id="revenueChart" height="260"></canvas>
                </div>
            </div>
        </div>

        <!-- Thống kê theo tài xế -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card" style="color:#4361ee;"></i> Thống kê theo tài xế</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr>
                            <th>Tài xế</th><th>Khu vực</th><th>TT</th>
                            <th class="text-center">Tổng phân công</th>
                            <th class="text-center">Hoàn tất</th>
                            <th class="text-center">Hủy</th>
                            <th class="text-center">Hoàn hàng</th>
                            <th>Doanh thu</th>
                        </tr></thead>
                        <tbody>
                        <?php if (empty($driverStats)): ?>
                            <tr><td colspan="8" class="text-center">Không có dữ liệu.</td></tr>
                        <?php else: ?>
                        <?php foreach ($driverStats as $ds):
                            $cls = match($ds['TrangThaiTaiXe']) {
                                'San sang' => 'status-green', 'Dang giao' => 'status-orange', default => 'status-gray'
                            };
                        ?>
                            <tr>
                                <td class="font-medium"><?php echo htmlspecialchars($ds['TenTaiXe']); ?></td>
                                <td><?php echo htmlspecialchars($ds['KhuVucHienTai'] ?? '—'); ?></td>
                                <td><span class="badge-pill <?php echo $cls; ?>"><?php echo match($ds['TrangThaiTaiXe']){'San sang'=>'Sẵn sàng','Dang giao'=>'Đang giao',default=>$ds['TrangThaiTaiXe']}; ?></span></td>
                                <td class="text-center"><?php echo (int)$ds['TongPhanCong']; ?></td>
                                <td class="text-center" style="color:#06d6a0;font-weight:600;"><?php echo (int)$ds['HoanTat']; ?></td>
                                <td class="text-center" style="color:#dc3545;"><?php echo (int)$ds['DaHuy']; ?></td>
                                <td class="text-center" style="color:#f77f00;"><?php echo (int)$ds['HoanHang']; ?></td>
                                <td class="font-medium"><?php echo number_format($ds['DoanhThu'], 0, ',', '.'); ?>đ</td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2 cột: Thống kê tuyến + Top KH -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
            <!-- Thống kê theo tuyến -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-route" style="color:#4361ee;"></i> Hiệu suất theo tuyến giao</h3></div>
                <div class="card-body p-0">
                    <table class="table" style="margin:0;">
                        <thead><tr><th>Tuyến</th><th class="text-center">Tổng</th><th class="text-center">Hoàn tất</th><th>Doanh thu</th></tr></thead>
                        <tbody>
                        <?php if (empty($routeStats)): ?>
                            <tr><td colspan="4" class="text-center">Không có dữ liệu.</td></tr>
                        <?php else: ?>
                        <?php foreach ($routeStats as $rs): ?>
                            <tr>
                                <td style="font-size:13px;"><?php echo htmlspecialchars($rs['TenTuyen']); ?></td>
                                <td class="text-center"><?php echo (int)$rs['TongDon']; ?></td>
                                <td class="text-center" style="color:#06d6a0;font-weight:600;"><?php echo (int)$rs['HoanTat']; ?></td>
                                <td style="font-size:13px;"><?php echo number_format($rs['DoanhThu'], 0, ',', '.'); ?>đ</td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top khách hàng -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-crown" style="color:#f77f00;"></i> Top 5 khách hàng</h3></div>
                <div class="card-body p-0">
                    <table class="table" style="margin:0;">
                        <thead><tr><th>#</th><th>Khách hàng</th><th class="text-center">Đơn</th><th class="text-center">Hoàn tất</th></tr></thead>
                        <tbody>
                        <?php if (empty($topCustomers)): ?>
                            <tr><td colspan="4" class="text-center">Không có dữ liệu.</td></tr>
                        <?php else: ?>
                        <?php foreach ($topCustomers as $i => $tc): ?>
                            <tr>
                                <td>
                                    <?php if ($i < 3): ?>
                                    <span style="color:<?php echo ['#FFD700','#C0C0C0','#CD7F32'][$i]; ?>;font-size:18px;">🏆</span>
                                    <?php else: echo $i + 1; endif; ?>
                                </td>
                                <td class="font-medium"><?php echo htmlspecialchars($tc['HoTen']); ?></td>
                                <td class="text-center"><?php echo (int)$tc['TongDon']; ?></td>
                                <td class="text-center" style="color:#06d6a0;font-weight:600;"><?php echo (int)$tc['HoanTat']; ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bảng theo ngày -->
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt" style="color:#4361ee;"></i> Chi tiết theo ngày (30 ngày gần nhất)</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr>
                            <th>Ngày</th><th class="text-center">Tổng đơn</th><th class="text-center">Hoàn tất</th>
                            <th class="text-center">Hủy</th><th>Doanh thu</th>
                        </tr></thead>
                        <tbody>
                        <?php if (empty($dailyStats)): ?>
                            <tr><td colspan="5" class="text-center">Không có dữ liệu trong khoảng thời gian này.</td></tr>
                        <?php else: ?>
                        <?php foreach ($dailyStats as $day): ?>
                            <tr>
                                <td class="font-medium"><?php echo date('d/m/Y', strtotime($day['Ngay'])); ?></td>
                                <td class="text-center"><?php echo (int)$day['TongDon']; ?></td>
                                <td class="text-center" style="color:#06d6a0;font-weight:600;"><?php echo (int)$day['HoanTat']; ?></td>
                                <td class="text-center" style="color:#dc3545;"><?php echo (int)$day['DaHuy']; ?></td>
                                <td class="font-medium"><?php echo number_format($day['DoanhThu'], 0, ',', '.'); ?>đ</td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="assets/js/script.js"></script>
<script>
// Biểu đồ tròn
const ctx1 = document.getElementById('orderStatusChart').getContext('2d');
new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: ['Hoàn tất', 'Đang xử lý', 'Đã hủy', 'Hoàn hàng'],
        datasets: [{
            data: [
                <?php echo (int)($summary['DonHoanTat'] ?? 0); ?>,
                <?php echo (int)($summary['DangXuLy'] ?? 0); ?>,
                <?php echo (int)($summary['DonHuy'] ?? 0); ?>,
                <?php echo (int)($summary['DonHoanHang'] ?? 0); ?>
            ],
            backgroundColor: ['#06d6a0','#4361ee','#dc3545','#f77f00'],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: false,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 16 } }
        }
    }
});

// Biểu đồ cột doanh thu
const dailyData = <?php echo json_encode(array_reverse($dailyStats)); ?>;
const ctx2 = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: dailyData.map(d => {
            const dt = new Date(d.Ngay);
            return dt.getDate() + '/' + (dt.getMonth()+1);
        }),
        datasets: [{
            label: 'Doanh thu (đ)',
            data: dailyData.map(d => d.DoanhThu),
            backgroundColor: 'rgba(67,97,238,0.7)',
            borderColor: '#4361ee',
            borderWidth: 1,
            borderRadius: 4,
        }, {
            label: 'Số đơn hoàn tất',
            data: dailyData.map(d => d.HoanTat),
            type: 'line',
            borderColor: '#06d6a0',
            backgroundColor: 'rgba(6,214,160,0.1)',
            borderWidth: 2,
            tension: 0.3,
            yAxisID: 'y1',
            pointRadius: 3,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        if (ctx.datasetIndex === 0) return 'Doanh thu: ' + Number(ctx.raw).toLocaleString('vi-VN') + 'đ';
                        return 'Hoàn tất: ' + ctx.raw + ' đơn';
                    }
                }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => (v/1000).toFixed(0)+'k đ', font: {size:11} } },
            y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { font: {size:11} } }
        }
    }
});
</script>
</body>
</html>
