<?php
require_once 'models/phancong_model.php';

class AssignController {
    private AssignModel $model;

    public function __construct() {
        $this->model = new AssignModel();
    }

    public function index() {
        // Chỉ Điều phối (2) và Admin (4) được truy cập
        $role = (int)($_SESSION['role_id'] ?? 0);
        if (!in_array($role, [2, 4], true)) {
            http_response_code(403);
            echo '<div style="padding:40px;text-align:center;color:#991b1b;">
                    <h2>⛔ Từ chối truy cập</h2>
                    <p>Bạn không có quyền thực hiện chức năng Phân công tài xế.</p>
                    <a href="index.php?page=trangchu">← Quay lại Trang chủ</a>
                  </div>';
            exit;
        }

        $message     = '';
        $messageType = 'success';

        // ─── Xử lý hành động POST (phân công)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'phancong') {
                $maDonHang  = (int)($_POST['don_hang_id'] ?? 0);
                $maTaiXe    = (int)($_POST['tai_xe_id']   ?? 0);
                $ghiChu     = trim($_POST['ghi_chu']      ?? '');
                $maNhanVien = $this->model->getNhanVienId($_SESSION['user_id']);

                if ($maDonHang <= 0 || $maTaiXe <= 0) {
                    $message     = 'Vui lòng chọn đơn hàng và tài xế hợp lệ.';
                    $messageType = 'error';
                } elseif (!$maNhanVien && $role !== 4) {
                    $message     = 'Không tìm thấy thông tin nhân viên của bạn trong hệ thống.';
                    $messageType = 'error';
                } else {
                    // Admin có thể dùng MaNhanVien = 1 (mặc định) nếu không có bản ghi NhanVien
                    if (!$maNhanVien) $maNhanVien = 1;
                    $result      = $this->model->assign($maDonHang, $maTaiXe, $maNhanVien, $ghiChu);
                    $message     = $result['message'];
                    $messageType = $result['success'] ? 'success' : 'error';
                }
            }
        }

        // ─── Xử lý hành động GET (hủy phân công)
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'cancel_assign') {
            $result      = $this->model->cancel((int)$_GET['id']);
            $message     = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }

        // ─── Dữ liệu hiển thị
        $keyword       = trim($_GET['keyword'] ?? '');
        $driverKeyword = trim($_GET['driver_kw'] ?? '');
        $driverStatus  = trim($_GET['driver_status'] ?? '');

        $orders  = $this->model->getOrdersPending($keyword);
        $drivers = $this->model->getDrivers($driverKeyword, $driverStatus);

        require_once 'views/phancong.php';
    }
}
?>
