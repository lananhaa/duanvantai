<?php
require_once 'config/database.php';

class OrderModel {
    private $conn;
    private $table_name = 'DonHang';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '', $status = '', $customerId = null) {
        $query = "SELECT d.MaDonHang, d.NgayTao, d.TienHang, d.TongKhoiLuong, d.PhiVanChuyen,
                         d.PhiHoan, d.TongPhi, d.TrangThai AS TrangThaiDonHang, d.LyDoHuy,
                         k.HoTen AS TenKhachHang, dg.TenNguoiNhan,
                         dg.SoDienThoai AS SDTNhan, dn.DiaChi AS DiaChiNhan,
                         tn.TenTuyen
                  FROM {$this->table_name} d
                  LEFT JOIN KhachHang k ON d.MaKhachHang = k.MaKhachHang
                  LEFT JOIN DiemGiao dg ON d.MaDiemGiao = dg.MaDiemGiao
                  LEFT JOIN DiemNhan dn ON d.MaDiemNhan = dn.MaDiemNhan
                  LEFT JOIN TuyenGiao tn ON d.MaTuyenGiao = tn.MaTuyenGiao
                  WHERE 1 = 1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (CAST(d.MaDonHang AS CHAR) LIKE :keyword OR k.HoTen LIKE :keyword
                        OR dg.TenNguoiNhan LIKE :keyword OR dg.SoDienThoai LIKE :keyword
                        OR d.TrangThai LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $query .= ' AND d.TrangThai = :status';
            $params[':status'] = $status;
        }
        if ($customerId !== null) {
            $query .= ' AND d.MaKhachHang = :customer_id';
            $params[':customer_id'] = (int) $customerId;
        }
        $query .= ' ORDER BY d.MaDonHang DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT d.*, k.HoTen AS TenKhachHang, dg.TenNguoiNhan,
                         dg.SoDienThoai AS SDTNhan, dn.DiaChi AS DiaChiNhan,
                         dg.DiaChi AS DiaChiGiao, dg.TinhThanh AS TinhThanhGiao, dg.QuanHuyen AS QuanHuyenGiao, dg.PhuongXa AS PhuongXaGiao,
                         dn.SoDienThoai AS SDTGoi, dn.TinhThanh AS TinhThanhNhan, dn.QuanHuyen AS QuanHuyenNhan, dn.PhuongXa AS PhuongXaNhan,
                         tn.TenTuyen
                  FROM {$this->table_name} d
                  LEFT JOIN KhachHang k ON d.MaKhachHang = k.MaKhachHang
                  LEFT JOIN DiemGiao dg ON d.MaDiemGiao = dg.MaDiemGiao
                  LEFT JOIN DiemNhan dn ON d.MaDiemNhan = dn.MaDiemNhan
                  LEFT JOIN TuyenGiao tn ON d.MaTuyenGiao = tn.MaTuyenGiao
                  WHERE d.MaDonHang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => (int) $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            return null;
        }
        $detail = $this->conn->prepare("SELECT c.*, h.TenHangHoa, h.KhoiLuong AS KhoiLuongDonVi
                                        FROM ChiTietDonHang c
                                        INNER JOIN HangHoa h ON c.MaHangHoa = h.MaHangHoa
                                        WHERE c.MaDonHang = :id");
        $detail->execute([':id' => (int) $id]);
        $order['detail'] = $detail->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return $order;
    }

    public function getCustomerIdByAccount($accountId) {
        $stmt = $this->conn->prepare('SELECT MaKhachHang FROM KhachHang WHERE MaTaiKhoan = :account_id');
        $stmt->execute([':account_id' => (int) $accountId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function getFormOptions($customerId = null) {
        $customerQuery = 'SELECT MaKhachHang, HoTen FROM KhachHang';
        $customerParams = [];
        if ($customerId !== null) {
            $customerQuery .= ' WHERE MaKhachHang = :customer_id';
            $customerParams[':customer_id'] = (int) $customerId;
        }
        $customerQuery .= ' ORDER BY HoTen';
        $customerStatement = $this->conn->prepare($customerQuery);
        $customerStatement->execute($customerParams);
        return [
            'customers' => $customerStatement->fetchAll(PDO::FETCH_ASSOC),
            'pickups' => $this->conn->query("SELECT MaDiemNhan, DiaChi, KhuVuc FROM DiemNhan ORDER BY MaDiemNhan DESC")->fetchAll(PDO::FETCH_ASSOC),
            'deliveries' => $this->conn->query("SELECT MaDiemGiao, TenNguoiNhan, SoDienThoai, DiaChi, KhuVuc FROM DiemGiao ORDER BY MaDiemGiao DESC")->fetchAll(PDO::FETCH_ASSOC),
            'routes' => $this->conn->query("SELECT MaTuyenGiao, TenTuyen, KhuVucDi, KhuVucDen FROM TuyenGiao ORDER BY TenTuyen")->fetchAll(PDO::FETCH_ASSOC),
            'products' => $this->conn->query("SELECT MaHangHoa, TenHangHoa, KhoiLuong FROM HangHoa WHERE TrangThai = 'Dang su dung' ORDER BY TenHangHoa")->fetchAll(PDO::FETCH_ASSOC)
        ];
    }

    public function calculateShipping($routeId, $weight) {
        $route = $this->conn->prepare('SELECT KhuVucDi, KhuVucDen FROM TuyenGiao WHERE MaTuyenGiao = :id');
        $route->execute([':id' => (int) $routeId]);
        $routeData = $route->fetch(PDO::FETCH_ASSOC);
        if (!$routeData) {
            return ['id' => null, 'amount' => 0];
        }
        $fee = $this->conn->prepare("SELECT MaPhi, PhiCoBan, KhoiLuongDen, PhiVuotKhoiLuong
                                     FROM PhiVanChuyen
                                     WHERE TrangThai = 'Dang ap dung'
                                       AND KhuVucDi = :from_area AND KhuVucDen = :to_area
                                     ORDER BY KhoiLuongDen ASC");
        $fee->execute([
            ':from_area' => $routeData['KhuVucDi'],
            ':to_area' => $routeData['KhuVucDen']
        ]);
        $rules = $fee->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rules)) {
            return ['id' => null, 'amount' => 0];
        }
        $matchedRule = null;
        foreach ($rules as $rule) {
            $matchedRule = $rule;
            if ($weight <= $rule['KhoiLuongDen']) {
                break;
            }
        }
        $amount = (float) $matchedRule['PhiCoBan'];
        if ($weight > $matchedRule['KhoiLuongDen'] && (float) $matchedRule['PhiVuotKhoiLuong'] > 0) {
            $extraWeight = ceil($weight - $matchedRule['KhoiLuongDen']);
            $amount += $extraWeight * (float) $matchedRule['PhiVuotKhoiLuong'];
        }
        return ['id' => $matchedRule['MaPhi'], 'amount' => $amount];
    }

    public function findRouteByDistricts($pickup, $delivery) {
        $stmt = $this->conn->prepare("SELECT MaTuyenGiao FROM TuyenGiao WHERE (KhuVucDi LIKE :pickup OR KhuVucDi = :pickup) AND (KhuVucDen LIKE :delivery OR KhuVucDen = :delivery) LIMIT 1");
        $stmt->execute([':pickup' => '%' . trim($pickup) . '%', ':delivery' => '%' . trim($delivery) . '%']);
        $route = $stmt->fetch(PDO::FETCH_ASSOC);
        return $route ? (int) $route['MaTuyenGiao'] : null;
    }

    private function buildOrderData($data) {
        $weight = 0;
        $goods_total = 0;
        if (!empty($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as $prod) {
                $quantity = (int) ($prod['quantity'] ?? 0);
                $unitPrice = (float) ($prod['unit_price'] ?? 0);
                $prodWeight = (float) ($prod['product_weight'] ?? 0);
                $weight += $quantity * $prodWeight;
                $goods_total += $quantity * $unitPrice;
            }
        }
        $shipping = $this->calculateShipping($data['route_id'], $weight);
        return [
            'weight' => $weight,
            'goods_total' => $goods_total,
            'fee_id' => $shipping['id'],
            'shipping_fee' => $shipping['amount'],
            'total_fee' => $shipping['amount'] + (float) ($data['return_fee'] ?? 0)
        ];
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("INSERT INTO DiemNhan (DiaChi, TinhThanh, QuanHuyen, PhuongXa, SoDienThoai) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['pickup_address'], $data['pickup_province'], $data['pickup_district'], $data['pickup_ward'], $data['pickup_phone']]);
            $data['pickup_id'] = $this->conn->lastInsertId();

            $stmt = $this->conn->prepare("INSERT INTO DiemGiao (TenNguoiNhan, SoDienThoai, DiaChi, TinhThanh, QuanHuyen, PhuongXa) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['delivery_name'], $data['delivery_phone'], $data['delivery_address'], $data['delivery_province'], $data['delivery_district'], $data['delivery_ward']]);
            $data['delivery_id'] = $this->conn->lastInsertId();

            $product_ids = [];
            foreach ($data['products'] as $prod) {
                $stmt = $this->conn->prepare("INSERT INTO HangHoa (TenHangHoa, KhoiLuong) VALUES (?, ?)");
                $stmt->execute([$prod['name'], $prod['product_weight']]);
                $product_ids[] = $this->conn->lastInsertId();
            }
            $data['product_ids'] = $product_ids;

            $calculated = $this->buildOrderData($data);

            $order = $this->conn->prepare("INSERT INTO DonHang
                (MaKhachHang, MaDiemNhan, MaDiemGiao, MaTuyenGiao, MaPhi, TienHang,
                 TongKhoiLuong, PhiVanChuyen, PhiHoan, TongPhi, TrangThai, NguoiChiuPhi)
                VALUES (:customer, :pickup, :delivery, :route, :fee_id, :goods_total,
                        :weight, :shipping_fee, :return_fee, :total_fee, 'Cho xac nhan', 'Khach hang')");
            $order->execute([
                ':customer' => (int) $data['customer_id'], ':pickup' => (int) $data['pickup_id'],
                ':delivery' => (int) $data['delivery_id'], ':route' => (int) $data['route_id'],
                ':fee_id' => $calculated['fee_id'], ':goods_total' => $calculated['goods_total'],
                ':weight' => $calculated['weight'], ':shipping_fee' => $calculated['shipping_fee'],
                ':return_fee' => (float) ($data['return_fee'] ?? 0), ':total_fee' => $calculated['total_fee']
            ]);
            $this->insertDetail($this->conn->lastInsertId(), $data, $calculated);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Tạo đơn hàng thành công.'];
        } catch (Exception $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'Không thể tạo đơn hàng. Vui lòng kiểm tra dữ liệu.'];
        }
    }

    public function update($id, $data) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("INSERT INTO DiemNhan (DiaChi, TinhThanh, QuanHuyen, PhuongXa, SoDienThoai) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$data['pickup_address'], $data['pickup_province'], $data['pickup_district'], $data['pickup_ward'], $data['pickup_phone']]);
            $data['pickup_id'] = $this->conn->lastInsertId();

            $stmt = $this->conn->prepare("INSERT INTO DiemGiao (TenNguoiNhan, SoDienThoai, DiaChi, TinhThanh, QuanHuyen, PhuongXa) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['delivery_name'], $data['delivery_phone'], $data['delivery_address'], $data['delivery_province'], $data['delivery_district'], $data['delivery_ward']]);
            $data['delivery_id'] = $this->conn->lastInsertId();

            $product_ids = [];
            foreach ($data['products'] as $prod) {
                $stmt = $this->conn->prepare("INSERT INTO HangHoa (TenHangHoa, KhoiLuong) VALUES (?, ?)");
                $stmt->execute([$prod['name'], $prod['product_weight']]);
                $product_ids[] = $this->conn->lastInsertId();
            }
            $data['product_ids'] = $product_ids;

            $calculated = $this->buildOrderData($data);

            $order = $this->conn->prepare("UPDATE DonHang SET MaKhachHang = :customer, MaDiemNhan = :pickup,
                MaDiemGiao = :delivery, MaTuyenGiao = :route, MaPhi = :fee_id, TienHang = :goods_total,
                TongKhoiLuong = :weight, PhiVanChuyen = :shipping_fee, PhiHoan = :return_fee,
                TongPhi = :total_fee, TrangThai = :status, LyDoHuy = :cancel_reason WHERE MaDonHang = :id");
            $order->execute([
                ':customer' => (int) $data['customer_id'], ':pickup' => (int) $data['pickup_id'],
                ':delivery' => (int) $data['delivery_id'], ':route' => (int) $data['route_id'],
                ':fee_id' => $calculated['fee_id'], ':goods_total' => $calculated['goods_total'],
                ':weight' => $calculated['weight'], ':shipping_fee' => $calculated['shipping_fee'],
                ':return_fee' => (float) ($data['return_fee'] ?? 0), ':total_fee' => $calculated['total_fee'],
                ':status' => $data['status'], ':cancel_reason' => $data['cancel_reason'], ':id' => (int) $id
            ]);
            $this->conn->prepare('DELETE FROM ChiTietDonHang WHERE MaDonHang = :id')->execute([':id' => (int) $id]);
            $this->insertDetail($id, $data, $calculated);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật đơn hàng thành công.'];
        } catch (Exception $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'Không thể cập nhật đơn hàng.'];
        }
    }

    public function checkOrderCancellation(
        $orderId,
        $newStatus,
        $returnFee,
        $cancelReason = '',
        $customerId = null,
        $customerCanCancel = false
    ) {
        $existingOrder = $this->getById($orderId);
        if (!$existingOrder) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng.',
                'action' => 'none'
            ];
        }

        // Kiểm tra quyền của user đối với đơn hàng
        if ($customerId !== null) {
            if ((int) $existingOrder['MaKhachHang'] !== (int) $customerId) {
                return [
                    'success' => false,
                    'message' => 'Bạn không có quyền thao tác đơn hàng này.',
                    'action' => 'none'
                ];
            }

            if (!$customerCanCancel) {
                return [
                    'success' => false,
                    'message' => 'Khách hàng chỉ được hủy đơn khi ở trạng thái "Cho xac nhan".',
                    'action' => 'none'
                ];
            }
        }

        // Kiểm tra trạng thái đơn hàng
        $currentStatus = $existingOrder['TrangThai'];
        if ($newStatus === 'Da huy') {
            // Xác định trạng thái được phép hủy
            $allowedStatuses = ['Cho xac nhan', 'Da huy'];
            if (!in_array($currentStatus, $allowedStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Không thể hủy đơn hàng ở trạng thái "' . $currentStatus . '".',
                    'action' => 'none'
                ];
            }
        }

        // Tạo dữ liệu tạm thời cho tính toán
        $tempData = [
            'products' => [],
            'customer_id' => $existingOrder['MaKhachHang'],
            'return_fee' => (float) $returnFee
        ];

        foreach ($existingOrder['detail'] as $item) {
            $tempData['products'][] = [
                'product_id' => $item['MaHangHoa'],
                'quantity' => $item['SoLuong'],
                'unit_price' => $item['DonGia'],
                'product_weight' => $item['KhoiLuong']
            ];
        }

        $calculated = $this->buildOrderData($tempData);

        // Tính toán phí hoàn hàng nếu hợp lệ
        $calculatedReturnFee = (float) $returnFee;
        if ($calculatedReturnFee < 0) {
            $calculatedReturnFee = 0;
        }

        // Tính toán phí vận chuyển
        $calculatedShippingFee = (float) $existingOrder['PhiVanChuyen'];

        // Tính tổng phí
        $calculatedTotalFee = $calculated['goods_total'] + $calculatedShippingFee + $calculatedReturnFee;

        // Kiểm tra lý do hủy đơn (nếu là hủy)
        if ($newStatus === 'Da huy' && $cancelReason === '') {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập lý do hủy đơn.',
                'action' => 'none'
            ];
        }

        return [
            'success' => true,
            'message' => 'Đơn hàng hợp lệ để thay đổi trạng thái.',
            'action' => 'update',
            'order_data' => [
                'status' => $newStatus,
                'cancel_reason' => $cancelReason,
                'goods_total' => $calculated['goods_total'],
                'shipping_fee' => $calculatedShippingFee,
                'return_fee' => $calculatedReturnFee,
                'total_fee' => $calculatedTotalFee
            ]
        ];
    }

    private function insertDetail($orderId, $data, $calculated) {
        $detail = $this->conn->prepare("INSERT INTO ChiTietDonHang
            (MaDonHang, MaHangHoa, SoLuong, DonGia, KhoiLuong, ThanhTien)
            VALUES (:order_id, :product, :quantity, :unit_price, :weight, :subtotal)");
        foreach ($data['products'] as $index => $prod) {
            $quantity = (int) $prod['quantity'];
            $unitPrice = (float) $prod['unit_price'];
            $weight = (float) $prod['product_weight'] * $quantity;
            $detail->execute([
                ':order_id' => (int) $orderId, 
                ':product' => (int) $data['product_ids'][$index],
                ':quantity' => $quantity, 
                ':unit_price' => $unitPrice,
                ':weight' => $weight, 
                ':subtotal' => $quantity * $unitPrice
            ]);
        }
    }

    public function cancel($id, $reason) {
        $stmt = $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Da huy', LyDoHuy = :reason WHERE MaDonHang = :id AND TrangThai IN ('Cho xac nhan', 'Da xac nhan', 'Cho phan cong')");
        $stmt->execute([':reason' => $reason, ':id' => (int) $id]);
        $changed = $stmt->rowCount() > 0;
        return ['success' => $changed, 'message' => $changed ? 'Đã hủy đơn hàng.' : 'Đơn hàng không còn ở trạng thái được phép hủy.'];
    }

    public function delete($id) {
        try {
            $stmt = $this->conn->prepare('DELETE FROM DonHang WHERE MaDonHang = :id');
            $stmt->execute([':id' => (int) $id]);
            $changed = $stmt->rowCount() > 0;
            return ['success' => $changed, 'message' => $changed ? 'Đã xóa đơn hàng.' : 'Không tìm thấy đơn hàng.'];
        } catch (PDOException $exception) {
            return ['success' => false, 'message' => 'Không thể xóa đơn hàng đã có dữ liệu liên quan. Hãy dùng chức năng Hủy.'];
        }
    }
}
?>
