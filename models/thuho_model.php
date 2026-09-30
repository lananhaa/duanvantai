<?php
require_once 'config/database.php';

class CodModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '', $status = '', $dateFrom = '', $dateTo = '') {
        $query = "SELECT c.MaCOD, c.MaDonHang, c.SoTienCOD, c.TrangThai AS TrangThaiCOD,
                         c.ThoiGianThu, c.GhiChu,
                         d.TrangThai AS TrangThaiDon, d.NgayTao,
                         k.HoTen AS TenKhachHang, k.SoDienThoai AS SDTKhach,
                         dg.TenNguoiNhan, dg.SoDienThoai AS SDTNhan, dg.DiaChi AS DiaChiGiao,
                         tx.HoTen AS TenTaiXe
                  FROM COD c
                  INNER JOIN DonHang d ON d.MaDonHang = c.MaDonHang
                  LEFT JOIN KhachHang k ON k.MaKhachHang = d.MaKhachHang
                  LEFT JOIN DiemGiao dg ON dg.MaDiemGiao = d.MaDiemGiao
                  LEFT JOIN PhanCong pc ON pc.MaDonHang = d.MaDonHang
                    AND pc.TrangThai <> 'Da huy'
                    AND pc.MaPhanCong = (SELECT MAX(p2.MaPhanCong) FROM PhanCong p2 WHERE p2.MaDonHang = d.MaDonHang AND p2.TrangThai <> 'Da huy')
                  LEFT JOIN TaiXe tx ON tx.MaTaiXe = pc.MaTaiXe
                  WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (CAST(c.MaDonHang AS CHAR) LIKE :kw OR k.HoTen LIKE :kw OR dg.TenNguoiNhan LIKE :kw OR tx.HoTen LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $query .= " AND c.TrangThai = :status";
            $params[':status'] = $status;
        }
        if ($dateFrom !== '') {
            $query .= " AND DATE(d.NgayTao) >= :date_from";
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $query .= " AND DATE(d.NgayTao) <= :date_to";
            $params[':date_to'] = $dateTo;
        }
        $query .= " ORDER BY c.MaCOD DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSummary() {
        $stmt = $this->conn->query(
            "SELECT 
                COUNT(*) AS TongSo,
                SUM(SoTienCOD) AS TongTienCOD,
                SUM(CASE WHEN TrangThai = 'Da thu' THEN SoTienCOD ELSE 0 END) AS DaThu,
                SUM(CASE WHEN TrangThai = 'Chua thu' THEN SoTienCOD ELSE 0 END) AS ChuaThu,
                SUM(CASE WHEN TrangThai = 'Khong thu' THEN SoTienCOD ELSE 0 END) AS KhongThu,
                COUNT(CASE WHEN TrangThai = 'Da thu' THEN 1 END) AS SoDaThu,
                COUNT(CASE WHEN TrangThai = 'Chua thu' THEN 1 END) AS SoChuaThu,
                COUNT(CASE WHEN TrangThai = 'Khong thu' THEN 1 END) AS SoKhongThu
             FROM COD"
        );
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($codId, $status, $note, $accountId) {
        if (!in_array($status, ['Da thu', 'Chua thu', 'Khong thu'], true)) {
            return ['success' => false, 'message' => 'Trạng thái COD không hợp lệ.'];
        }
        try {
            $this->conn->prepare(
                "UPDATE COD SET TrangThai = :status,
                 ThoiGianThu = CASE WHEN :is_collected = 1 THEN NOW() ELSE NULL END,
                 GhiChu = :note WHERE MaCOD = :id"
            )->execute([
                ':status'       => $status,
                ':is_collected' => $status === 'Da thu' ? 1 : 0,
                ':note'         => $note,
                ':id'           => (int)$codId,
            ]);
            return ['success' => true, 'message' => 'Cập nhật trạng thái COD thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể cập nhật COD.'];
        }
    }
}
?>
