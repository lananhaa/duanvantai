<?php
require_once 'config/database.php';

class DriverOrderModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getDriverIdByAccount($accountId) {
        $stmt = $this->conn->prepare('SELECT MaTaiXe FROM TaiXe WHERE MaTaiKhoan = :account_id LIMIT 1');
        $stmt->execute([':account_id' => (int) $accountId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function getCurrentLocation($driverId) {
        $stmt = $this->conn->prepare('SELECT KhuVucHienTai FROM TaiXe WHERE MaTaiXe = :driver_id');
        $stmt->execute([':driver_id' => (int) $driverId]);
        return $stmt->fetchColumn() ?: '';
    }

    public function getAssignedOrders($driverId, $keyword = '') {
        $query = "SELECT d.MaDonHang, d.NgayTao, d.TongKhoiLuong, d.TienHang, d.PhiVanChuyen,
                         d.TongPhi, d.TrangThai, d.LyDoHuy, d.LyDoHoan,
                         pc.MaPhanCong, pc.TrangThai AS TrangThaiPhanCong,
                         dn.DiaChi AS DiaChiNhan, dn.KhuVuc AS KhuVucNhan,
                         dg.TenNguoiNhan, dg.SoDienThoai AS SDTNhan,
                         dg.DiaChi AS DiaChiGiao, dg.KhuVuc AS KhuVucGiao,
                         cod.SoTienCOD, cod.TrangThai AS TrangThaiCOD,
                         cod.ThoiGianThu, cod.GhiChu AS GhiChuCOD,
                         (SELECT GROUP_CONCAT(CONCAT(h.TenHangHoa, ' × ', ct.SoLuong) SEPARATOR ', ')
                          FROM ChiTietDonHang ct INNER JOIN HangHoa h ON h.MaHangHoa = ct.MaHangHoa
                          WHERE ct.MaDonHang = d.MaDonHang) AS HangHoa
                  FROM PhanCong pc
                  INNER JOIN DonHang d ON d.MaDonHang = pc.MaDonHang
                  LEFT JOIN DiemNhan dn ON dn.MaDiemNhan = d.MaDiemNhan
                  LEFT JOIN DiemGiao dg ON dg.MaDiemGiao = d.MaDiemGiao
                  LEFT JOIN COD cod ON cod.MaDonHang = d.MaDonHang
                  WHERE pc.MaTaiXe = :driver_id AND pc.TrangThai <> 'Da huy'
                    AND pc.MaPhanCong = (
                        SELECT MAX(pc_latest.MaPhanCong)
                        FROM PhanCong pc_latest
                        WHERE pc_latest.MaDonHang = pc.MaDonHang
                          AND pc_latest.TrangThai <> 'Da huy'
                    )";
                $params = [':driver_id' => (int) $driverId];
        if ($keyword !== '') {
            $query .= " AND (CAST(d.MaDonHang AS CHAR) LIKE :keyword OR dg.TenNguoiNhan LIKE :keyword
                        OR dg.SoDienThoai LIKE :keyword OR dg.DiaChi LIKE :keyword
                        OR d.TrangThai LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }
        $query .= ' ORDER BY pc.ThoiGianPhanCong DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrdersForTracking($keyword = '', $customerAccountId = null) {
        $query = "SELECT d.MaDonHang, d.NgayTao, d.TongKhoiLuong, d.TienHang, d.PhiVanChuyen,
                         d.TongPhi, d.TrangThai, d.LyDoHuy, d.LyDoHoan,
                         pc.MaPhanCong, pc.MaTaiXe, pc.TrangThai AS TrangThaiPhanCong,
                         dn.DiaChi AS DiaChiNhan, dn.KhuVuc AS KhuVucNhan,
                         dg.TenNguoiNhan, dg.SoDienThoai AS SDTNhan,
                         dg.DiaChi AS DiaChiGiao, dg.KhuVuc AS KhuVucGiao,
                         cod.SoTienCOD, cod.TrangThai AS TrangThaiCOD,
                         cod.ThoiGianThu, cod.GhiChu AS GhiChuCOD,
                         k.HoTen AS TenKhachHang,
                         (SELECT GROUP_CONCAT(CONCAT(h.TenHangHoa, ' × ', ct.SoLuong) SEPARATOR ', ')
                          FROM ChiTietDonHang ct INNER JOIN HangHoa h ON h.MaHangHoa = ct.MaHangHoa
                          WHERE ct.MaDonHang = d.MaDonHang) AS HangHoa
                  FROM DonHang d
                  LEFT JOIN KhachHang k ON k.MaKhachHang = d.MaKhachHang
                  LEFT JOIN DiemNhan dn ON dn.MaDiemNhan = d.MaDiemNhan
                  LEFT JOIN DiemGiao dg ON dg.MaDiemGiao = d.MaDiemGiao
                  LEFT JOIN COD cod ON cod.MaDonHang = d.MaDonHang
                  LEFT JOIN PhanCong pc ON pc.MaDonHang = d.MaDonHang
                    AND pc.TrangThai <> 'Da huy'
                    AND pc.MaPhanCong = (SELECT MAX(pc_latest.MaPhanCong) FROM PhanCong pc_latest
                        WHERE pc_latest.MaDonHang = d.MaDonHang AND pc_latest.TrangThai <> 'Da huy')
                  WHERE 1 = 1";
        $params = [];
        if ($customerAccountId !== null) {
            $query .= ' AND k.MaTaiKhoan = :account_id';
            $params[':account_id'] = (int) $customerAccountId;
        }
        if ($keyword !== '') {
            $query .= " AND (CAST(d.MaDonHang AS CHAR) LIKE :keyword OR k.HoTen LIKE :keyword
                OR dg.TenNguoiNhan LIKE :keyword OR dg.SoDienThoai LIKE :keyword
                OR dg.DiaChi LIKE :keyword OR d.TrangThai LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }
        $query .= ' ORDER BY d.MaDonHang DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderHistory($orderId, $driverId = null) {
        $query = "SELECT ls.TrangThai, ls.ThoiGian, ls.GhiChu
            FROM LichSuTrangThai ls WHERE ls.MaDonHang = :order_id";
        $params = [':order_id' => (int) $orderId];
        if ($driverId !== null) {
            $query .= " AND EXISTS (SELECT 1 FROM PhanCong pc WHERE pc.MaDonHang = ls.MaDonHang
                AND pc.MaTaiXe = :driver_id AND pc.TrangThai <> 'Da huy'
                AND pc.MaPhanCong = (SELECT MAX(pc_latest.MaPhanCong) FROM PhanCong pc_latest
                    WHERE pc_latest.MaDonHang = pc.MaDonHang AND pc_latest.TrangThai <> 'Da huy'))";
            $params[':driver_id'] = (int) $driverId;
        }
        $query .= ' ORDER BY ls.ThoiGian DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelCustomerOrder($orderId, $accountId, $reason) {
        if (trim($reason) === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập lý do hủy đơn.'];
        }
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("SELECT d.TrangThai FROM DonHang d
                INNER JOIN KhachHang k ON k.MaKhachHang = d.MaKhachHang
                WHERE d.MaDonHang = :order_id AND k.MaTaiKhoan = :account_id FOR UPDATE");
            $stmt->execute([':order_id' => (int) $orderId, ':account_id' => (int) $accountId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            $allowed = ['Cho xac nhan', 'Da xac nhan', 'Cho phan cong'];
            if (!$order || !in_array($order['TrangThai'], $allowed, true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy đơn của bạn hoặc đơn không còn được phép hủy.'];
            }
            $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Da huy', LyDoHuy = :reason WHERE MaDonHang = :order_id")
                ->execute([':reason' => trim($reason), ':order_id' => (int) $orderId]);
            $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Da huy' WHERE MaDonHang = :order_id AND TrangThai NOT IN ('Hoan thanh', 'Da huy')")
                ->execute([':order_id' => (int) $orderId]);
            $this->conn->prepare("INSERT INTO LichSuTrangThai (MaDonHang, TrangThai, MaTaiKhoan, GhiChu)
                VALUES (:order_id, 'Da huy', :account_id, :note)")
                ->execute([':order_id' => (int) $orderId, ':account_id' => (int) $accountId, ':note' => trim($reason)]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã hủy đơn hàng.'];
        } catch (Exception $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Không thể hủy đơn hàng.'];
        }
    }

    private function getAssignedOrderForUpdate($orderId, $driverId) {
        $query = "SELECT pc.MaPhanCong, pc.MaTaiXe, pc.TrangThai AS TrangThaiPhanCong, d.TrangThai
            FROM PhanCong pc INNER JOIN DonHang d ON d.MaDonHang = pc.MaDonHang
            WHERE pc.MaDonHang = :order_id AND pc.MaTaiXe = :driver_id
              AND pc.TrangThai NOT IN ('Da huy', 'Hoan thanh')
                            AND pc.MaPhanCong = (SELECT MAX(pc_latest.MaPhanCong) FROM PhanCong pc_latest
                                    WHERE pc_latest.MaDonHang = pc.MaDonHang AND pc_latest.TrangThai <> 'Da huy')
            ORDER BY pc.MaPhanCong DESC LIMIT 1 FOR UPDATE";
        $params = [':order_id' => (int) $orderId];
        if ($driverId === null) {
            $query = str_replace(' AND pc.MaTaiXe = :driver_id', '', $query);
        } else {
            $params[':driver_id'] = (int) $driverId;
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($orderId, $driverId, $accountId, $newStatus, $note) {
        $transitions = [
            'Da phan cong' => ['Da nhan hang'],
            'Dang phan cong' => ['Da nhan hang'],
            'Da nhan hang' => ['Dang van chuyen'],
            'Dang van chuyen' => ['Dang giao hang'],
            'Dang giao hang' => ['Da giao hang', 'Giao khong thanh cong'],
            'Da giao hang' => ['Hoan tat']
        ];
        if ($newStatus === 'Giao khong thanh cong' && trim($note) === '') {
            return ['success' => false, 'message' => 'Vui lòng ghi lý do giao không thành công hoặc hoàn hàng.'];
        }

        try {
            $this->conn->beginTransaction();
            $order = $this->getAssignedOrderForUpdate($orderId, $driverId);
            if (!$order) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Đơn hàng không được phân công cho bạn hoặc đã kết thúc.'];
            }
            if (!in_array($newStatus, $transitions[$order['TrangThai']] ?? [], true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Trạng thái mới không hợp lệ theo tiến trình hiện tại của đơn.'];
            }

            $reason = trim($note);
            $update = $this->conn->prepare("UPDATE DonHang SET TrangThai = :status,
                LyDoHoan = CASE WHEN :is_return = 1 THEN :return_reason ELSE LyDoHoan END
                WHERE MaDonHang = :order_id");
            $isReturn = $newStatus === 'Hoan hang' ? 1 : 0;
            $update->execute([
                ':status' => $newStatus, ':is_return' => $isReturn,
                ':return_reason' => $reason, ':order_id' => (int) $orderId
            ]);

            $historyNote = $reason !== '' ? $reason : 'Tài xế cập nhật trạng thái đơn hàng.';
            $history = $this->conn->prepare('INSERT INTO LichSuTrangThai (MaDonHang, TrangThai, MaTaiKhoan, GhiChu) VALUES (:order_id, :status, :account_id, :note)');
            $history->execute([
                ':order_id' => (int) $orderId, ':status' => $newStatus,
                ':account_id' => (int) $accountId, ':note' => $historyNote
            ]);

            $assignedDriverId = (int) ($order['MaTaiXe'] ?? $driverId);
            if ($newStatus === 'Hoan tat') {
                $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Hoan thanh' WHERE MaPhanCong = :assignment_id")
                    ->execute([':assignment_id' => (int) $order['MaPhanCong']]);
                $remaining = $this->conn->prepare("SELECT COUNT(*) FROM PhanCong
                    WHERE MaTaiXe = :driver_id AND TrangThai NOT IN ('Da huy', 'Hoan thanh', 'Hoan tat', 'Hoan hang')");
                $remaining->execute([':driver_id' => $assignedDriverId]);
                $driverStatus = (int) $remaining->fetchColumn() > 0 ? 'Dang giao' : 'San sang';
                $this->conn->prepare('UPDATE TaiXe SET TrangThai = :status WHERE MaTaiXe = :driver_id')
                    ->execute([':status' => $driverStatus, ':driver_id' => $assignedDriverId]);
            } else {
                $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Dang thuc hien' WHERE MaPhanCong = :assignment_id")
                    ->execute([':assignment_id' => (int) $order['MaPhanCong']]);
                $this->conn->prepare("UPDATE TaiXe SET TrangThai = 'Dang giao' WHERE MaTaiXe = :driver_id")
                    ->execute([':driver_id' => $assignedDriverId]);
            }
            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã cập nhật trạng thái đơn hàng.'];
        } catch (Exception $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Không thể cập nhật trạng thái đơn hàng.'];
        }
    }

    public function updateLocation($driverId, $location) {
        $stmt = $this->conn->prepare('UPDATE TaiXe SET KhuVucHienTai = :location, ThoiGianCapNhatViTri = NOW() WHERE MaTaiXe = :driver_id');
        $stmt->execute([':location' => trim($location), ':driver_id' => (int) $driverId]);
        return ['success' => $stmt->rowCount() > 0, 'message' => 'Đã cập nhật khu vực hiện tại.'];
    }

    public function updateCod($orderId, $driverId, $status, $note) {
        if (!in_array($status, ['Da thu', 'Chua thu', 'Khong thu'], true)) {
            return ['success' => false, 'message' => 'Trạng thái COD không hợp lệ.'];
        }
        if ($status === 'Khong thu' && trim($note) === '') {
            return ['success' => false, 'message' => 'Vui lòng ghi lý do không thu được COD.'];
        }
        $query = "SELECT d.MaDonHang FROM PhanCong pc
                        INNER JOIN DonHang d ON d.MaDonHang = pc.MaDonHang
                        WHERE pc.MaDonHang = :order_id AND pc.MaTaiXe = :driver_id
                            AND pc.TrangThai <> 'Da huy' AND d.TrangThai IN ('Da giao hang', 'Hoan tat')
                            AND pc.MaPhanCong = (SELECT MAX(pc_latest.MaPhanCong) FROM PhanCong pc_latest
                                    WHERE pc_latest.MaDonHang = pc.MaDonHang AND pc_latest.TrangThai <> 'Da huy') LIMIT 1";
        $params = [':order_id' => (int) $orderId];
        if ($driverId === null) {
            $query = str_replace(' AND pc.MaTaiXe = :driver_id', '', $query);
        } else {
            $params[':driver_id'] = (int) $driverId;
        }
        $assigned = $this->conn->prepare($query);
        $assigned->execute($params);
        if (!$assigned->fetchColumn()) {
            return ['success' => false, 'message' => 'Đơn hàng không được phân công cho bạn.'];
        }
        $stmt = $this->conn->prepare("UPDATE COD SET TrangThai = :status,
            ThoiGianThu = CASE WHEN :collected = 1 THEN NOW() ELSE NULL END,
            GhiChu = :note WHERE MaDonHang = :order_id");
        $stmt->execute([
            ':status' => $status, ':collected' => $status === 'Da thu' ? 1 : 0,
            ':note' => trim($note), ':order_id' => (int) $orderId
        ]);
        return ['success' => $stmt->rowCount() > 0, 'message' => $stmt->rowCount() > 0 ? 'Đã cập nhật thông tin COD.' : 'Đơn hàng chưa có bản ghi COD hoặc thông tin không thay đổi.'];
    }
}
?>