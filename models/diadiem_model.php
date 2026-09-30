<?php
require_once 'config/database.php';

class LocationModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ── ĐIỂM NHẬN ──
    public function getAllPickups($keyword = '') {
        $query = "SELECT dn.*,
                         (SELECT COUNT(*) FROM DonHang d WHERE d.MaDiemNhan = dn.MaDiemNhan) AS SoDonHang
                  FROM DiemNhan dn WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (dn.DiaChi LIKE :kw OR dn.KhuVuc LIKE :kw OR dn.TinhThanh LIKE :kw OR dn.SoDienThoai LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        $query .= " ORDER BY dn.MaDiemNhan DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPickupById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM DiemNhan WHERE MaDiemNhan = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPickup($data) {
        try {
            $this->conn->prepare(
                "INSERT INTO DiemNhan (DiaChi, KhuVuc, TinhThanh, QuanHuyen, PhuongXa, SoDienThoai, GhiChu)
                 VALUES (:dc, :kv, :tt, :qh, :px, :sdt, :gc)"
            )->execute([
                ':dc' => $data['dia_chi'], ':kv' => $data['khu_vuc'], ':tt' => $data['tinh_thanh'],
                ':qh' => $data['quan_huyen'], ':px' => $data['phuong_xa'], ':sdt' => $data['sdt'], ':gc' => $data['ghi_chu'],
            ]);
            return ['success' => true, 'message' => 'Thêm điểm nhận thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể thêm điểm nhận.'];
        }
    }

    public function updatePickup($id, $data) {
        if (!$this->getPickupById($id)) return ['success' => false, 'message' => 'Không tìm thấy điểm nhận.'];
        try {
            $this->conn->prepare(
                "UPDATE DiemNhan SET DiaChi = :dc, KhuVuc = :kv, TinhThanh = :tt, QuanHuyen = :qh, PhuongXa = :px, SoDienThoai = :sdt, GhiChu = :gc WHERE MaDiemNhan = :id"
            )->execute([
                ':dc' => $data['dia_chi'], ':kv' => $data['khu_vuc'], ':tt' => $data['tinh_thanh'],
                ':qh' => $data['quan_huyen'], ':px' => $data['phuong_xa'], ':sdt' => $data['sdt'], ':gc' => $data['ghi_chu'], ':id' => (int)$id,
            ]);
            return ['success' => true, 'message' => 'Cập nhật điểm nhận thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể cập nhật điểm nhận.'];
        }
    }

    public function deletePickup($id) {
        try {
            $this->conn->prepare("DELETE FROM DiemNhan WHERE MaDiemNhan = :id")->execute([':id' => (int)$id]);
            return ['success' => true, 'message' => 'Xóa điểm nhận thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể xóa điểm nhận đang có đơn hàng.'];
        }
    }

    // ── ĐIỂM GIAO ──
    public function getAllDeliveries($keyword = '') {
        $query = "SELECT dg.*,
                         (SELECT COUNT(*) FROM DonHang d WHERE d.MaDiemGiao = dg.MaDiemGiao) AS SoDonHang
                  FROM DiemGiao dg WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (dg.TenNguoiNhan LIKE :kw OR dg.DiaChi LIKE :kw OR dg.KhuVuc LIKE :kw OR dg.SoDienThoai LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        $query .= " ORDER BY dg.MaDiemGiao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDeliveryById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM DiemGiao WHERE MaDiemGiao = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createDelivery($data) {
        try {
            $this->conn->prepare(
                "INSERT INTO DiemGiao (TenNguoiNhan, SoDienThoai, DiaChi, KhuVuc, TinhThanh, QuanHuyen, PhuongXa, GhiChu)
                 VALUES (:ten, :sdt, :dc, :kv, :tt, :qh, :px, :gc)"
            )->execute([
                ':ten' => $data['ten_nguoi_nhan'], ':sdt' => $data['sdt'], ':dc' => $data['dia_chi'],
                ':kv' => $data['khu_vuc'], ':tt' => $data['tinh_thanh'], ':qh' => $data['quan_huyen'],
                ':px' => $data['phuong_xa'], ':gc' => $data['ghi_chu'],
            ]);
            return ['success' => true, 'message' => 'Thêm điểm giao thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể thêm điểm giao.'];
        }
    }

    public function updateDelivery($id, $data) {
        if (!$this->getDeliveryById($id)) return ['success' => false, 'message' => 'Không tìm thấy điểm giao.'];
        try {
            $this->conn->prepare(
                "UPDATE DiemGiao SET TenNguoiNhan = :ten, SoDienThoai = :sdt, DiaChi = :dc, KhuVuc = :kv, TinhThanh = :tt, QuanHuyen = :qh, PhuongXa = :px, GhiChu = :gc WHERE MaDiemGiao = :id"
            )->execute([
                ':ten' => $data['ten_nguoi_nhan'], ':sdt' => $data['sdt'], ':dc' => $data['dia_chi'],
                ':kv' => $data['khu_vuc'], ':tt' => $data['tinh_thanh'], ':qh' => $data['quan_huyen'],
                ':px' => $data['phuong_xa'], ':gc' => $data['ghi_chu'], ':id' => (int)$id,
            ]);
            return ['success' => true, 'message' => 'Cập nhật điểm giao thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể cập nhật điểm giao.'];
        }
    }

    public function deleteDelivery($id) {
        try {
            $this->conn->prepare("DELETE FROM DiemGiao WHERE MaDiemGiao = :id")->execute([':id' => (int)$id]);
            return ['success' => true, 'message' => 'Xóa điểm giao thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể xóa điểm giao đang có đơn hàng.'];
        }
    }
}
?>
