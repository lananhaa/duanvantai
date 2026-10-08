<?php
require_once 'config/database.php';

class AssignModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ─── Lấy danh sách đơn hàng cần phân công (Điều phối hoặc Admin)
    public function getOrdersPending($keyword = '') {
        $query = "SELECT d.MaDonHang, d.NgayTao, d.TrangThai, d.TongKhoiLuong, d.TongPhi,
                         k.HoTen AS TenKhachHang,
                         dg.TenNguoiNhan, dg.SoDienThoai AS SDTNhan,
                         dg.DiaChi AS DiaChiGiao, dg.KhuVuc AS KhuVucGiao,
                         dn.KhuVuc AS KhuVucNhan,
                         tn.TenTuyen,
                         pc.MaTaiXe AS TaiXeDaPhanCong,
                         tx.HoTen AS TenTaiXe
                  FROM DonHang d
                  LEFT JOIN KhachHang k  ON d.MaKhachHang = k.MaKhachHang
                  LEFT JOIN DiemGiao dg  ON d.MaDiemGiao  = dg.MaDiemGiao
                  LEFT JOIN DiemNhan dn  ON d.MaDiemNhan  = dn.MaDiemNhan
                  LEFT JOIN TuyenGiao tn ON d.MaTuyenGiao = tn.MaTuyenGiao
                  LEFT JOIN PhanCong pc  ON d.MaDonHang   = pc.MaDonHang
                                          AND pc.TrangThai NOT IN ('Da huy', 'Hoan thanh', 'Hoan hang', 'Hoan tat')
                                                                                    AND pc.MaPhanCong = (SELECT MAX(pc_latest.MaPhanCong)
                                                                                            FROM PhanCong pc_latest
                                                                                            WHERE pc_latest.MaDonHang = d.MaDonHang
                                                                                                AND pc_latest.TrangThai NOT IN ('Da huy', 'Hoan thanh', 'Hoan hang', 'Hoan tat'))
                  LEFT JOIN TaiXe tx     ON pc.MaTaiXe    = tx.MaTaiXe
                  WHERE d.TrangThai IN ('Cho phan cong', 'Da phan cong', 'Giao khong thanh cong')";
        $params = [];
        if (!empty($keyword)) {
            $query .= " AND (CAST(d.MaDonHang AS CHAR) LIKE :kw OR k.HoTen LIKE :kw
                         OR dg.TenNguoiNhan LIKE :kw OR dg.SoDienThoai LIKE :kw
                         OR dg.DiaChi LIKE :kw OR dg.KhuVuc LIKE :kw OR dn.DiaChi LIKE :kw OR dn.KhuVuc LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        $query .= " ORDER BY d.NgayTao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Lấy danh sách tài xế kèm phương tiện
    public function getDrivers($keyword = '', $status = '') {
        $query = "SELECT tx.MaTaiXe, tx.MaPhuongTien, tx.HoTen, tx.SoDienThoai, tx.KhuVucHienTai,
                         tx.TrangThai, tk.TrangThai AS TrangThaiTaiKhoan,
                         pt.BienSo, pt.LoaiPhuongTien, pt.TaiTrong, pt.TrangThai AS TrangThaiXe,
                         (SELECT COUNT(*) FROM PhanCong WHERE MaTaiXe = tx.MaTaiXe
                          AND TrangThai NOT IN ('Hoan thanh', 'Da huy', 'Hoan hang', 'Hoan tat')) AS SoDonDangGiao
                  FROM TaiXe tx
                  INNER JOIN TaiKhoan tk ON tk.MaTaiKhoan = tx.MaTaiKhoan
                  LEFT JOIN PhuongTien pt ON tx.MaPhuongTien = pt.MaPhuongTien
                  WHERE 1=1";
        $params = [];
        if (!empty($keyword)) {
            $query .= " AND (tx.HoTen LIKE :kw OR tx.SoDienThoai LIKE :kw OR tx.SoBangLai LIKE :kw
                         OR tx.KhuVucHienTai LIKE :kw OR tx.DiaChi LIKE :kw OR pt.BienSo LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        if (!empty($status)) {
            $query .= " AND tx.TrangThai = :status";
            $params[':status'] = $status;
        }
        $query .= " ORDER BY tx.TrangThai ASC, tx.HoTen ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Lấy MaNhanVien từ TaiKhoan của nhân viên đang đăng nhập
    public function getNhanVienId($maTaiKhoan) {
        $stmt = $this->conn->prepare("SELECT MaNhanVien FROM NhanVien WHERE MaTaiKhoan = :id LIMIT 1");
        $stmt->execute([':id' => (int)$maTaiKhoan]);
        return $stmt->fetchColumn() ?: null;
    }

    // ─── Thực hiện phân công
    public function assign($maDonHang, $maTaiXe, $maNhanVien, $ghiChu = '') {
        try {
            $this->conn->beginTransaction();

            $orderStatement = $this->conn->prepare("SELECT MaDonHang, TrangThai, TongKhoiLuong
                FROM DonHang WHERE MaDonHang = :id FOR UPDATE");
            $orderStatement->execute([':id' => (int)$maDonHang]);
            $order = $orderStatement->fetch(PDO::FETCH_ASSOC);
            if (!$order || !in_array($order['TrangThai'], ['Cho phan cong', 'Da phan cong', 'Giao khong thanh cong'], true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Đơn hàng không tồn tại hoặc không còn ở trạng thái được phân công.'];
            }

            $driverStatement = $this->conn->prepare("SELECT tx.MaTaiXe, tx.TrangThai,
                    tx.MaPhuongTien, tk.TrangThai AS TrangThaiTaiKhoan,
                    pt.TaiTrong, pt.TrangThai AS TrangThaiPhuongTien
                FROM TaiXe tx
                INNER JOIN TaiKhoan tk ON tk.MaTaiKhoan = tx.MaTaiKhoan
                LEFT JOIN PhuongTien pt ON pt.MaPhuongTien = tx.MaPhuongTien
                WHERE tx.MaTaiXe = :id FOR UPDATE");
            $driverStatement->execute([':id' => (int)$maTaiXe]);
            $driver = $driverStatement->fetch(PDO::FETCH_ASSOC);
            if (!$driver || $driver['TrangThai'] !== 'San sang' || $driver['TrangThaiTaiKhoan'] !== 'Hoat dong') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Tài xế không tồn tại, chưa sẵn sàng hoặc tài khoản đã khóa.'];
            }
            if (!$driver['MaPhuongTien'] || $driver['TrangThaiPhuongTien'] === 'Bao tri') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Tài xế chưa có phương tiện hợp lệ hoặc phương tiện đang bảo trì.'];
            }
            if ((float)$driver['TaiTrong'] < (float)$order['TongKhoiLuong']) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Tải trọng phương tiện không đủ cho đơn hàng này.'];
            }

            $oldDriversStatement = $this->conn->prepare("SELECT DISTINCT MaTaiXe FROM PhanCong
                WHERE MaDonHang = :id AND TrangThai NOT IN ('Hoan thanh', 'Da huy', 'Hoan hang', 'Hoan tat')");
            $oldDriversStatement->execute([':id' => (int)$maDonHang]);
            $oldDriverIds = $oldDriversStatement->fetchAll(PDO::FETCH_COLUMN);

            // Hủy phân công cũ nếu có
            $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Da huy'
                                  WHERE MaDonHang = :id AND TrangThai NOT IN ('Hoan thanh','Da huy','Hoan hang','Hoan tat')")
                       ->execute([':id' => (int)$maDonHang]);

            // Tạo phân công mới
            $stmt = $this->conn->prepare("INSERT INTO PhanCong (MaDonHang, MaTaiXe, MaNhanVien, TrangThai, GhiChu)
                                          VALUES (:order, :driver, :staff, 'Dang phan cong', :note)");
            $stmt->execute([
                ':order'  => (int)$maDonHang,
                ':driver' => (int)$maTaiXe,
                ':staff'  => $maNhanVien ? (int)$maNhanVien : null,
                ':note'   => $ghiChu
            ]);

            // Cập nhật trạng thái đơn hàng
            $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Da phan cong' WHERE MaDonHang = :id")
                       ->execute([':id' => (int)$maDonHang]);

            foreach ($oldDriverIds as $oldDriverId) {
                $this->refreshDriverStatus((int)$oldDriverId);
            }
            $this->conn->prepare("UPDATE TaiXe SET TrangThai = 'Dang giao' WHERE MaTaiXe = :id")
                       ->execute([':id' => (int)$maTaiXe]);

            // Ghi lịch sử trạng thái
            $this->conn->prepare("INSERT INTO LichSuTrangThai (MaDonHang, TrangThai, MaTaiKhoan, GhiChu)
                                  VALUES (:order, 'Da phan cong', :account, :note)")
                       ->execute([
                           ':order'   => (int)$maDonHang,
                           ':account' => (int)$_SESSION['user_id'],
                           ':note'    => 'Phân công cho tài xế #' . $maTaiXe . '. ' . $ghiChu
                       ]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Phân công tài xế thành công!'];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['success' => false, 'message' => 'Lỗi khi phân công: ' . $e->getMessage()];
        }
    }

    // ─── Hủy phân công
    public function cancel($maDonHang) {
        try {
            $this->conn->beginTransaction();
            $orderStatement = $this->conn->prepare("SELECT TrangThai FROM DonHang WHERE MaDonHang = :id FOR UPDATE");
            $orderStatement->execute([':id' => (int)$maDonHang]);
            $orderStatus = $orderStatement->fetchColumn();
            if (!$orderStatus || !in_array($orderStatus, ['Cho phan cong', 'Da phan cong', 'Giao khong thanh cong'], true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Đơn hàng không còn ở trạng thái có thể hủy phân công.'];
            }
            $activeAssignments = $this->conn->prepare("SELECT DISTINCT MaTaiXe FROM PhanCong
                WHERE MaDonHang = :id AND TrangThai NOT IN ('Hoan thanh', 'Da huy', 'Hoan hang', 'Hoan tat')");
            $activeAssignments->execute([':id' => (int)$maDonHang]);
            $driverIds = $activeAssignments->fetchAll(PDO::FETCH_COLUMN);
            if (!$driverIds) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Đơn hàng hiện không có phân công đang hoạt động.'];
            }
            $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Da huy'
                                  WHERE MaDonHang = :id AND TrangThai NOT IN ('Hoan thanh','Da huy','Hoan hang','Hoan tat')")
                       ->execute([':id' => (int)$maDonHang]);
            $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Cho phan cong' WHERE MaDonHang = :id")
                       ->execute([':id' => (int)$maDonHang]);
            foreach ($driverIds as $driverId) {
                $this->refreshDriverStatus((int)$driverId);
            }
            $this->conn->prepare("INSERT INTO LichSuTrangThai (MaDonHang, TrangThai, MaTaiKhoan, GhiChu)
                                  VALUES (:order, 'Cho phan cong', :account, 'Hủy phân công')")
                       ->execute([':order' => (int)$maDonHang, ':account' => (int)$_SESSION['user_id']]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã hủy phân công đơn hàng #' . $maDonHang];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()];
        }
    }

    private function refreshDriverStatus($driverId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM PhanCong
            WHERE MaTaiXe = :id AND TrangThai NOT IN ('Hoan thanh', 'Da huy', 'Hoan hang', 'Hoan tat')");
        $stmt->execute([':id' => (int)$driverId]);
        if ((int)$stmt->fetchColumn() === 0) {
            $this->conn->prepare("UPDATE TaiXe SET TrangThai = 'San sang' WHERE MaTaiXe = :id AND TrangThai = 'Dang giao'")
                ->execute([':id' => (int)$driverId]);
        }
    }
}
?>
