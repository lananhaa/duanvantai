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
                  LEFT JOIN PhanCong pc  ON d.MaDonHang   = pc.MaDonHang AND pc.TrangThai != 'Da huy'
                  LEFT JOIN TaiXe tx     ON pc.MaTaiXe    = tx.MaTaiXe
                  WHERE d.TrangThai NOT IN ('Da giao hang', 'Hoan tat', 'Da huy')";
        $params = [];
        if (!empty($keyword)) {
            $query .= " AND (CAST(d.MaDonHang AS CHAR) LIKE :kw OR k.HoTen LIKE :kw
                         OR dg.TenNguoiNhan LIKE :kw OR dg.KhuVuc LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        $query .= " ORDER BY d.NgayTao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Lấy danh sách tài xế kèm phương tiện
    public function getDrivers($keyword = '', $status = '') {
        $query = "SELECT tx.MaTaiXe, tx.HoTen, tx.SoDienThoai, tx.KhuVucHienTai,
                         tx.TrangThai,
                         pt.BienSo, pt.LoaiPhuongTien, pt.TaiTrong, pt.TrangThai AS TrangThaiXe,
                         (SELECT COUNT(*) FROM PhanCong WHERE MaTaiXe = tx.MaTaiXe
                          AND TrangThai NOT IN ('Hoan thanh', 'Da huy')) AS SoDonDangGiao
                  FROM TaiXe tx
                  LEFT JOIN PhuongTien pt ON tx.MaPhuongTien = pt.MaPhuongTien
                  WHERE 1=1";
        $params = [];
        if (!empty($keyword)) {
            $query .= " AND (tx.HoTen LIKE :kw OR tx.SoDienThoai LIKE :kw OR tx.KhuVucHienTai LIKE :kw)";
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

            // Hủy phân công cũ nếu có
            $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Da huy'
                                  WHERE MaDonHang = :id AND TrangThai NOT IN ('Hoan thanh','Da huy')")
                       ->execute([':id' => (int)$maDonHang]);

            // Tạo phân công mới
            $stmt = $this->conn->prepare("INSERT INTO PhanCong (MaDonHang, MaTaiXe, MaNhanVien, TrangThai, GhiChu)
                                          VALUES (:order, :driver, :staff, 'Dang phan cong', :note)");
            $stmt->execute([
                ':order'  => (int)$maDonHang,
                ':driver' => (int)$maTaiXe,
                ':staff'  => (int)$maNhanVien,
                ':note'   => $ghiChu
            ]);

            // Cập nhật trạng thái đơn hàng
            $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Da phan cong' WHERE MaDonHang = :id")
                       ->execute([':id' => (int)$maDonHang]);

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
            $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Da huy'
                                  WHERE MaDonHang = :id AND TrangThai NOT IN ('Hoan thanh','Da huy')")
                       ->execute([':id' => (int)$maDonHang]);
            $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Cho phan cong' WHERE MaDonHang = :id")
                       ->execute([':id' => (int)$maDonHang]);
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
}
?>
