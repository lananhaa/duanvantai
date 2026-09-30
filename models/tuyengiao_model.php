<?php
require_once 'config/database.php';

class RouteModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '') {
        $query = "SELECT tn.*, 
                         (SELECT COUNT(*) FROM DonHang d WHERE d.MaTuyenGiao = tn.MaTuyenGiao) AS SoDonHang,
                         (SELECT COUNT(*) FROM PhiVanChuyen p WHERE p.KhuVucDi = tn.KhuVucDi AND p.KhuVucDen = tn.KhuVucDen AND p.TrangThai = 'Dang ap dung') AS SoMucPhi
                  FROM TuyenGiao tn WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (tn.TenTuyen LIKE :kw OR tn.KhuVucDi LIKE :kw OR tn.KhuVucDen LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        $query .= " ORDER BY tn.MaTuyenGiao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM TuyenGiao WHERE MaTuyenGiao = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFeesByRoute($khuVucDi, $khuVucDen) {
        $stmt = $this->conn->prepare(
            "SELECT * FROM PhiVanChuyen WHERE KhuVucDi = :di AND KhuVucDen = :den ORDER BY KhoiLuongDen ASC"
        );
        $stmt->execute([':di' => $khuVucDi, ':den' => $khuVucDen]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        try {
            $this->conn->prepare(
                "INSERT INTO TuyenGiao (TenTuyen, KhuVucDi, KhuVucDen, MoTa) VALUES (:ten, :di, :den, :mo)"
            )->execute([':ten' => $data['ten'], ':di' => $data['khu_vuc_di'], ':den' => $data['khu_vuc_den'], ':mo' => $data['mo_ta']]);
            return ['success' => true, 'message' => 'Thêm tuyến giao thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể thêm tuyến giao.'];
        }
    }

    public function update($id, $data) {
        if (!$this->getById($id)) return ['success' => false, 'message' => 'Không tìm thấy tuyến giao.'];
        try {
            $this->conn->prepare(
                "UPDATE TuyenGiao SET TenTuyen = :ten, KhuVucDi = :di, KhuVucDen = :den, MoTa = :mo WHERE MaTuyenGiao = :id"
            )->execute([':ten' => $data['ten'], ':di' => $data['khu_vuc_di'], ':den' => $data['khu_vuc_den'], ':mo' => $data['mo_ta'], ':id' => (int)$id]);
            return ['success' => true, 'message' => 'Cập nhật tuyến giao thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể cập nhật tuyến giao.'];
        }
    }

    public function delete($id) {
        try {
            $this->conn->prepare("DELETE FROM TuyenGiao WHERE MaTuyenGiao = :id")->execute([':id' => (int)$id]);
            return ['success' => true, 'message' => 'Xóa tuyến giao thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể xóa tuyến giao đang có đơn hàng hoặc phí vận chuyển liên quan.'];
        }
    }

    // ── Phí vận chuyển ──
    public function getAllFees($keyword = '', $status = '') {
        $query = "SELECT p.*, tn.TenTuyen FROM PhiVanChuyen p
                  LEFT JOIN TuyenGiao tn ON tn.KhuVucDi = p.KhuVucDi AND tn.KhuVucDen = p.KhuVucDen
                  WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (p.TenMucPhi LIKE :kw OR p.KhuVucDi LIKE :kw OR p.KhuVucDen LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $query .= " AND p.TrangThai = :status";
            $params[':status'] = $status;
        }
        $query .= " ORDER BY p.MaPhi DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeeById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM PhiVanChuyen WHERE MaPhi = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createFee($data) {
        try {
            $this->conn->prepare(
                "INSERT INTO PhiVanChuyen (TenMucPhi, KhuVucDi, KhuVucDen, KhoiLuongTu, KhoiLuongDen, PhiCoBan, PhiVuotKhoiLuong, TrangThai)
                 VALUES (:ten, :di, :den, :tu, :den_kl, :phi, :vuot, :tt)"
            )->execute([
                ':ten'    => $data['ten'],
                ':di'     => $data['khu_vuc_di'],
                ':den'    => $data['khu_vuc_den'],
                ':tu'     => $data['kl_tu'],
                ':den_kl' => $data['kl_den'],
                ':phi'    => $data['phi_co_ban'],
                ':vuot'   => $data['phi_vuot'],
                ':tt'     => $data['status'],
            ]);
            return ['success' => true, 'message' => 'Thêm mức phí thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể thêm mức phí.'];
        }
    }

    public function updateFee($id, $data) {
        if (!$this->getFeeById($id)) return ['success' => false, 'message' => 'Không tìm thấy mức phí.'];
        try {
            $this->conn->prepare(
                "UPDATE PhiVanChuyen SET TenMucPhi = :ten, KhuVucDi = :di, KhuVucDen = :den,
                 KhoiLuongTu = :tu, KhoiLuongDen = :den_kl, PhiCoBan = :phi, PhiVuotKhoiLuong = :vuot, TrangThai = :tt
                 WHERE MaPhi = :id"
            )->execute([
                ':ten'    => $data['ten'],
                ':di'     => $data['khu_vuc_di'],
                ':den'    => $data['khu_vuc_den'],
                ':tu'     => $data['kl_tu'],
                ':den_kl' => $data['kl_den'],
                ':phi'    => $data['phi_co_ban'],
                ':vuot'   => $data['phi_vuot'],
                ':tt'     => $data['status'],
                ':id'     => (int)$id,
            ]);
            return ['success' => true, 'message' => 'Cập nhật mức phí thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể cập nhật mức phí.'];
        }
    }

    public function deleteFee($id) {
        try {
            $this->conn->prepare("DELETE FROM PhiVanChuyen WHERE MaPhi = :id")->execute([':id' => (int)$id]);
            return ['success' => true, 'message' => 'Xóa mức phí thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể xóa mức phí đang được áp dụng.'];
        }
    }
}
?>
