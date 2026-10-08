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

    public function getSummary($dateFrom = '', $dateTo = '') {
        $where = '';
        $params = [];
        if ($dateFrom !== '') {
            $where .= ' AND DATE(d.NgayTao) >= :date_from';
            $params[':date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where .= ' AND DATE(d.NgayTao) <= :date_to';
            $params[':date_to'] = $dateTo;
        }
        $stmt = $this->conn->prepare(
            "SELECT 
                COUNT(*) AS TongSo,
                COALESCE(SUM(c.SoTienCOD), 0) AS TongTienCOD,
                COALESCE(SUM(CASE WHEN c.TrangThai = 'Da thu' THEN c.SoTienCOD ELSE 0 END), 0) AS DaThu,
                COALESCE(SUM(CASE WHEN c.TrangThai = 'Chua thu' THEN c.SoTienCOD ELSE 0 END), 0) AS ChuaThu,
                COALESCE(SUM(CASE WHEN c.TrangThai = 'Khong thu' THEN c.SoTienCOD ELSE 0 END), 0) AS KhongThu,
                COUNT(CASE WHEN c.TrangThai = 'Da thu' THEN 1 END) AS SoDaThu,
                COUNT(CASE WHEN c.TrangThai = 'Chua thu' THEN 1 END) AS SoChuaThu,
                COUNT(CASE WHEN c.TrangThai = 'Khong thu' THEN 1 END) AS SoKhongThu
             FROM COD c
             INNER JOIN DonHang d ON d.MaDonHang = c.MaDonHang
             WHERE 1=1 $where"
        );
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($codId, $status, $note, $accountId) {
        if (!in_array($status, ['Da thu', 'Chua thu', 'Khong thu'], true)) {
            return ['success' => false, 'message' => 'Trạng thái COD không hợp lệ.'];
        }
        try {
            $this->conn->beginTransaction();
            $current = $this->conn->prepare(
                "SELECT c.TrangThai AS TrangThaiCOD, d.TrangThai AS TrangThaiDon
                 FROM COD c INNER JOIN DonHang d ON d.MaDonHang = c.MaDonHang
                 WHERE c.MaCOD = :id FOR UPDATE"
            );
            $current->execute([':id' => (int)$codId]);
            $cod = $current->fetch(PDO::FETCH_ASSOC);
            if (!$cod) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy bản ghi COD.'];
            }
            if ($cod['TrangThaiCOD'] !== $status && $cod['TrangThaiCOD'] !== 'Chua thu') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'COD đã chốt không thể thay đổi trạng thái.'];
            }
            if ($status === 'Da thu' && !in_array($cod['TrangThaiDon'], ['Da giao hang', 'Hoan tat'], true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Chỉ được xác nhận đã thu COD sau khi đơn đã giao thành công.'];
            }
            if ($status === 'Khong thu' && !in_array($cod['TrangThaiDon'], ['Hoan hang', 'Da huy'], true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Chỉ chốt không thu COD khi đơn đã hoàn hàng hoặc bị hủy.'];
            }

            $this->conn->prepare(
                "UPDATE COD SET TrangThai = :status,
                 ThoiGianThu = CASE WHEN :is_collected = 1 THEN COALESCE(ThoiGianThu, NOW()) ELSE NULL END,
                 GhiChu = :note WHERE MaCOD = :id"
            )->execute([
                ':status'       => $status,
                ':is_collected' => $status === 'Da thu' ? 1 : 0,
                ':note'         => $note,
                ':id'           => (int)$codId,
            ]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật trạng thái COD thành công.'];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return ['success' => false, 'message' => 'Không thể cập nhật COD.'];
        }
    }
}
?>
