<?php
require_once 'config/database.php';

class StatisticModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // ── Thống kê tổng quan ──
    public function getSummaryStats($dateFrom = '', $dateTo = '') {
        $where = $this->buildDateWhere('d.NgayTao', $dateFrom, $dateTo);
        $params = $this->buildDateParams($dateFrom, $dateTo);

        $stmt = $this->conn->prepare(
            "SELECT 
                COUNT(*) AS TongDon,
                COUNT(CASE WHEN d.TrangThai = 'Hoan tat' THEN 1 END) AS DonHoanTat,
                COUNT(CASE WHEN d.TrangThai = 'Da huy' THEN 1 END) AS DonHuy,
                COUNT(CASE WHEN d.TrangThai = 'Hoan hang' THEN 1 END) AS DonHoanHang,
                COUNT(CASE WHEN d.TrangThai NOT IN ('Hoan tat','Da huy','Hoan hang') THEN 1 END) AS DangXuLy,
                COALESCE(SUM(d.TongKhoiLuong), 0) AS TongKhoiLuong
             FROM DonHang d WHERE 1=1 $where"
        );
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRevenueSummaryStats($dateFrom = '', $dateTo = '') {
        $where = $this->buildDateWhere('completed.ThoiGian', $dateFrom, $dateTo);
        $params = $this->buildDateParams($dateFrom, $dateTo);
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS DonHoanTat,
                    COALESCE(SUM(d.PhiVanChuyen), 0) AS DoanhThuPhi,
                    COALESCE(SUM(d.PhiVanChuyen), 0) AS TongDoanhThu
             FROM DonHang d
             INNER JOIN (
                 SELECT MaDonHang, MAX(ThoiGian) AS ThoiGian
                 FROM LichSuTrangThai
                 WHERE TrangThai = 'Hoan tat'
                 GROUP BY MaDonHang
             ) completed ON completed.MaDonHang = d.MaDonHang
             WHERE 1=1 $where"
        );
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Thống kê theo ngày ──
    public function getDailyStats($dateFrom = '', $dateTo = '') {
        $where = $this->buildDateWhere('d.NgayTao', $dateFrom, $dateTo);
        $params = $this->buildDateParams($dateFrom, $dateTo);
        $stmt = $this->conn->prepare(
            "SELECT DATE(d.NgayTao) AS Ngay,
                    COUNT(*) AS TongDon,
                    COUNT(CASE WHEN d.TrangThai = 'Hoan tat' THEN 1 END) AS HoanTat,
                    COUNT(CASE WHEN d.TrangThai = 'Da huy' THEN 1 END) AS DaHuy
             FROM DonHang d WHERE 1=1 $where
             GROUP BY DATE(d.NgayTao)
             ORDER BY Ngay DESC LIMIT 30"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRevenueDailyStats($dateFrom = '', $dateTo = '') {
        $where = $this->buildDateWhere('completed.ThoiGian', $dateFrom, $dateTo);
        $params = $this->buildDateParams($dateFrom, $dateTo);
        $stmt = $this->conn->prepare(
            "SELECT DATE(completed.ThoiGian) AS Ngay,
                    COUNT(*) AS HoanTat,
                    COALESCE(SUM(d.PhiVanChuyen), 0) AS DoanhThu
             FROM DonHang d
             INNER JOIN (
                 SELECT MaDonHang, MAX(ThoiGian) AS ThoiGian
                 FROM LichSuTrangThai
                 WHERE TrangThai = 'Hoan tat'
                 GROUP BY MaDonHang
             ) completed ON completed.MaDonHang = d.MaDonHang
             WHERE 1=1 $where
             GROUP BY DATE(completed.ThoiGian)
             ORDER BY Ngay DESC LIMIT 30"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Thống kê theo tài xế ──
    public function getDriverStats($dateFrom = '', $dateTo = '') {
        $wherePc = '';
        $params = [];
        if ($dateFrom !== '') { $wherePc .= " AND DATE(d.NgayTao) >= :date_from"; $params[':date_from'] = $dateFrom; }
        if ($dateTo !== '')   { $wherePc .= " AND DATE(d.NgayTao) <= :date_to";   $params[':date_to']   = $dateTo; }

        $stmt = $this->conn->prepare(
            "SELECT tx.MaTaiXe, tx.HoTen AS TenTaiXe, tx.KhuVucHienTai, tx.TrangThai AS TrangThaiTaiXe,
                    COUNT(pc.MaPhanCong) AS TongPhanCong,
                    COUNT(CASE WHEN d.TrangThai = 'Hoan tat' THEN 1 END) AS HoanTat,
                    COUNT(CASE WHEN d.TrangThai = 'Da huy' THEN 1 END) AS DaHuy,
                    COUNT(CASE WHEN d.TrangThai = 'Hoan hang' THEN 1 END) AS HoanHang
             FROM TaiXe tx
             LEFT JOIN PhanCong pc ON pc.MaTaiXe = tx.MaTaiXe
             LEFT JOIN DonHang d ON d.MaDonHang = pc.MaDonHang
             WHERE 1=1 $wherePc
             GROUP BY tx.MaTaiXe
             ORDER BY HoanTat DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Thống kê theo tuyến giao ──
    public function getRouteStats($dateFrom = '', $dateTo = '') {
        $where = $this->buildDateWhere('d.NgayTao', $dateFrom, $dateTo);
        $params = $this->buildDateParams($dateFrom, $dateTo);
        $stmt = $this->conn->prepare(
            "SELECT tn.MaTuyenGiao, tn.TenTuyen, tn.KhuVucDi, tn.KhuVucDen,
                    COUNT(d.MaDonHang) AS TongDon,
                    COUNT(CASE WHEN d.TrangThai = 'Hoan tat' THEN 1 END) AS HoanTat
             FROM TuyenGiao tn
             LEFT JOIN DonHang d ON d.MaTuyenGiao = tn.MaTuyenGiao
             WHERE 1=1 $where
             GROUP BY tn.MaTuyenGiao
             ORDER BY TongDon DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Thống kê COD ──
    public function getCodStats($dateFrom = '', $dateTo = '') {
        $where = $this->buildDateWhere('d.NgayTao', $dateFrom, $dateTo);
        $params = $this->buildDateParams($dateFrom, $dateTo);
        $stmt = $this->conn->prepare(
            "SELECT 
                COUNT(*) AS TongSoCOD,
                SUM(c.SoTienCOD) AS TongTienCOD,
                SUM(CASE WHEN c.TrangThai = 'Da thu' THEN c.SoTienCOD ELSE 0 END) AS DaThu,
                SUM(CASE WHEN c.TrangThai = 'Chua thu' THEN c.SoTienCOD ELSE 0 END) AS ChuaThu,
                SUM(CASE WHEN c.TrangThai = 'Khong thu' THEN c.SoTienCOD ELSE 0 END) AS KhongThu
             FROM COD c
             INNER JOIN DonHang d ON d.MaDonHang = c.MaDonHang
             WHERE 1=1 $where"
        );
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Top khách hàng ──
    public function getTopCustomers($limit = 5, $dateFrom = '', $dateTo = '') {
        $where = $this->buildDateWhere('d.NgayTao', $dateFrom, $dateTo);
        $params = $this->buildDateParams($dateFrom, $dateTo);
        $stmt = $this->conn->prepare(
            "SELECT k.MaKhachHang, k.HoTen, k.SoDienThoai,
                    COUNT(d.MaDonHang) AS TongDon,
                    COUNT(CASE WHEN d.TrangThai = 'Hoan tat' THEN 1 END) AS HoanTat,
                    COALESCE(SUM(d.TongPhi), 0) AS TongPhi
             FROM KhachHang k
             LEFT JOIN DonHang d ON d.MaKhachHang = k.MaKhachHang
             WHERE 1=1 $where
             GROUP BY k.MaKhachHang
             ORDER BY TongDon DESC
             LIMIT :lim"
        );
        $params[':lim'] = (int)$limit;
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, $key === ':lim' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildDateWhere($col, $from, $to) {
        $w = '';
        if ($from !== '') $w .= " AND DATE($col) >= :date_from";
        if ($to   !== '') $w .= " AND DATE($col) <= :date_to";
        return $w;
    }

    private function buildDateParams($from, $to) {
        $p = [];
        if ($from !== '') $p[':date_from'] = $from;
        if ($to   !== '') $p[':date_to']   = $to;
        return $p;
    }
}
?>
