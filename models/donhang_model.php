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
                         k.SoDienThoai AS SDTKhachHang, k.Email AS EmailKhachHang,
                         k.DiaChi AS DiaChiKhachHang, tk.TenDangNhap AS TaiKhoanKhachHang,
                         dg.SoDienThoai AS SDTNhan, dn.DiaChi AS DiaChiNhan,
                         dg.DiaChi AS DiaChiGiao, dg.TinhThanh AS TinhThanhGiao, dg.QuanHuyen AS QuanHuyenGiao, dg.PhuongXa AS PhuongXaGiao,
                         dg.KhuVuc AS KhuVucGiao, dg.GhiChu AS GhiChuGiao,
                         dn.SoDienThoai AS SDTGoi, dn.TinhThanh AS TinhThanhNhan, dn.QuanHuyen AS QuanHuyenNhan, dn.PhuongXa AS PhuongXaNhan,
                         dn.KhuVuc AS KhuVucNhan, dn.GhiChu AS GhiChuNhan,
                         tn.TenTuyen, tn.KhuVucDi, tn.KhuVucDen, tn.MoTa AS MoTaTuyen,
                         phi.TenMucPhi, phi.KhoiLuongTu, phi.KhoiLuongDen, phi.PhiCoBan, phi.PhiVuotKhoiLuong,
                         cod.SoTienCOD, cod.TrangThai AS TrangThaiCOD, cod.ThoiGianThu AS ThoiGianThuCOD, cod.GhiChu AS GhiChuCOD,
                         pc.MaPhanCong, pc.ThoiGianPhanCong, pc.TrangThai AS TrangThaiPhanCong, pc.GhiChu AS GhiChuPhanCong,
                         nv.HoTen AS TenNhanVienPhanCong, tx.MaTaiXe, tx.HoTen AS TenTaiXe, tx.SoDienThoai AS SDTTaiXe,
                         pt.BienSo, pt.LoaiPhuongTien, tkTx.TenDangNhap AS TaiKhoanTaiXe
                  FROM {$this->table_name} d
                  LEFT JOIN KhachHang k ON d.MaKhachHang = k.MaKhachHang
                  LEFT JOIN TaiKhoan tk ON k.MaTaiKhoan = tk.MaTaiKhoan
                  LEFT JOIN DiemGiao dg ON d.MaDiemGiao = dg.MaDiemGiao
                  LEFT JOIN DiemNhan dn ON d.MaDiemNhan = dn.MaDiemNhan
                  LEFT JOIN TuyenGiao tn ON d.MaTuyenGiao = tn.MaTuyenGiao
                  LEFT JOIN PhiVanChuyen phi ON d.MaPhi = phi.MaPhi
                  LEFT JOIN COD cod ON cod.MaDonHang = d.MaDonHang
                  LEFT JOIN PhanCong pc ON pc.MaDonHang = d.MaDonHang
                    AND pc.MaPhanCong = (SELECT MAX(pc2.MaPhanCong) FROM PhanCong pc2 WHERE pc2.MaDonHang = d.MaDonHang)
                  LEFT JOIN NhanVien nv ON pc.MaNhanVien = nv.MaNhanVien
                  LEFT JOIN TaiXe tx ON tx.MaTaiXe = pc.MaTaiXe
                  LEFT JOIN PhuongTien pt ON pt.MaTaiXe = tx.MaTaiXe AND pt.TrangThai = 'San sang'
                  LEFT JOIN TaiKhoan tkTx ON tx.MaTaiKhoan = tkTx.MaTaiKhoan
                  WHERE d.MaDonHang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => (int) $id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            return null;
        }
        $detail = $this->conn->prepare("SELECT c.*, h.TenHangHoa, h.KhoiLuong AS KhoiLuongDonVi,
                                               h.LoaiHang, h.DonViTinh, h.MoTa AS MoTaHangHoa
                                        FROM ChiTietDonHang c
                                        INNER JOIN HangHoa h ON c.MaHangHoa = h.MaHangHoa
                                        WHERE c.MaDonHang = :id
                                        ORDER BY c.MaChiTiet ASC");
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
            'khachhang' => $customerStatement->fetchAll(PDO::FETCH_ASSOC),
            'pickups' => $this->conn->query("SELECT MaDiemNhan, DiaChi, KhuVuc FROM DiemNhan ORDER BY MaDiemNhan DESC")->fetchAll(PDO::FETCH_ASSOC),
            'deliveries' => $this->conn->query("SELECT MaDiemGiao, TenNguoiNhan, SoDienThoai, DiaChi, KhuVuc FROM DiemGiao ORDER BY MaDiemGiao DESC")->fetchAll(PDO::FETCH_ASSOC),
            'tuyengiao' => $this->conn->query("SELECT MaTuyenGiao, TenTuyen, KhuVucDi, KhuVucDen FROM TuyenGiao ORDER BY TenTuyen")->fetchAll(PDO::FETCH_ASSOC),
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

            $pickupArea = trim($data['pickup_district'] ?: $data['pickup_province']);
            $deliveryArea = trim($data['delivery_district'] ?: $data['delivery_province']);
            $stmt = $this->conn->prepare("INSERT INTO DiemNhan (DiaChi, KhuVuc, TinhThanh, QuanHuyen, PhuongXa, SoDienThoai) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['pickup_address'], $pickupArea, $data['pickup_province'], $data['pickup_district'], $data['pickup_ward'], $data['pickup_phone']]);
            $data['pickup_id'] = $this->conn->lastInsertId();

            $stmt = $this->conn->prepare("INSERT INTO DiemGiao (TenNguoiNhan, SoDienThoai, DiaChi, KhuVuc, TinhThanh, QuanHuyen, PhuongXa) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['delivery_name'], $data['delivery_phone'], $data['delivery_address'], $deliveryArea, $data['delivery_province'], $data['delivery_district'], $data['delivery_ward']]);
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
            $orderId = (int) $this->conn->lastInsertId();
            $this->insertDetail($orderId, $data, $calculated);
            $accountId = (int) ($data['account_id'] ?? ($_SESSION['user_id'] ?? 0));
            $this->insertHistory($orderId, 'Cho xac nhan', $accountId, 'Khách hàng tạo đơn hàng.');
            $codAmount = (float) ($data['cod_amount'] ?? $calculated['goods_total']);
            if ($codAmount > 0) {
                $cod = $this->conn->prepare("INSERT INTO COD (MaDonHang, SoTienCOD, TrangThai, GhiChu) VALUES (:order_id, :amount, 'Chua thu', 'Thu hộ khi giao hàng')");
                $cod->execute([':order_id' => $orderId, ':amount' => $codAmount]);
            }
            $this->conn->commit();
            return ['success' => true, 'message' => 'Tạo đơn hàng thành công. Phí vận chuyển: ' . number_format($calculated['shipping_fee'], 0, ',', '.') . 'đ'];
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

            $pickupArea = trim($data['pickup_district'] ?: $data['pickup_province']);
            $deliveryArea = trim($data['delivery_district'] ?: $data['delivery_province']);
            $stmt = $this->conn->prepare("INSERT INTO DiemNhan (DiaChi, KhuVuc, TinhThanh, QuanHuyen, PhuongXa, SoDienThoai) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['pickup_address'], $pickupArea, $data['pickup_province'], $data['pickup_district'], $data['pickup_ward'], $data['pickup_phone']]);
            $data['pickup_id'] = $this->conn->lastInsertId();

            $stmt = $this->conn->prepare("INSERT INTO DiemGiao (TenNguoiNhan, SoDienThoai, DiaChi, KhuVuc, TinhThanh, QuanHuyen, PhuongXa) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['delivery_name'], $data['delivery_phone'], $data['delivery_address'], $deliveryArea, $data['delivery_province'], $data['delivery_district'], $data['delivery_ward']]);
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

    public function cancel($id, $reason, $accountId = null) {
        $reason = trim($reason);
        if ($reason === '') {
            return ['success' => false, 'message' => 'Vui lòng nhập lý do hủy đơn.'];
        }
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("SELECT TrangThai FROM DonHang WHERE MaDonHang = :id FOR UPDATE");
            $stmt->execute([':id' => (int) $id]);
            $status = $stmt->fetchColumn();
            if (!$status || !in_array($status, ['Cho xac nhan', 'Da xac nhan', 'Cho phan cong'], true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Đơn hàng không còn ở trạng thái được phép hủy.'];
            }
            $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Da huy', LyDoHuy = :reason WHERE MaDonHang = :id")
                ->execute([':reason' => $reason, ':id' => (int) $id]);
            $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Da huy' WHERE MaDonHang = :id AND TrangThai NOT IN ('Hoan thanh', 'Da huy')")
                ->execute([':id' => (int) $id]);
            $this->insertHistory((int) $id, 'Da huy', $accountId ?: ($_SESSION['user_id'] ?? null), $reason);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã hủy đơn hàng.'];
        } catch (Exception $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Không thể hủy đơn hàng.'];
        }
    }

    public function receive($id, $accountId) {
        return $this->advanceStatus($id, 'Cho xac nhan', 'Da xac nhan', $accountId, 'Điều phối tiếp nhận đơn hàng.');
    }

    public function queueForAssign($id, $accountId) {
        return $this->advanceStatus($id, 'Da xac nhan', 'Cho phan cong', $accountId, 'Đơn sẵn sàng phân công tài xế.');
    }

    public function approveForAssign($id, $accountId) {
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("SELECT TrangThai FROM DonHang WHERE MaDonHang = :id FOR UPDATE");
            $stmt->execute([':id' => (int) $id]);
            $status = $stmt->fetchColumn();
            if (!$status || !in_array($status, ['Cho xac nhan', 'Da xac nhan'], true)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Chỉ duyệt đơn đang chờ xác nhận hoặc đã xác nhận.'];
            }

            $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Cho phan cong' WHERE MaDonHang = :id")
                ->execute([':id' => (int) $id]);
            $this->insertHistory((int) $id, 'Cho phan cong', $accountId, 'Đơn hàng đã được duyệt và chuyển sang chờ phân công.');
            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã duyệt đơn và chuyển sang chờ phân công.'];
        } catch (Exception $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Không thể duyệt đơn hàng.'];
        }
    }

    public function retryFailedDelivery($id, $accountId) {
        return $this->advanceStatus($id, 'Giao khong thanh cong', 'Dang giao hang', $accountId, 'Điều phối yêu cầu giao lại.');
    }

    public function completeReturn($id, $reason, $returnFee, $accountId) {
        $reason = trim($reason);
        if ($reason === '') {
            return ['success' => false, 'message' => 'Vui lòng ghi lý do hoàn hàng.'];
        }
        $returnFee = max(0, (float) $returnFee);
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("SELECT TrangThai, PhiVanChuyen FROM DonHang WHERE MaDonHang = :id FOR UPDATE");
            $stmt->execute([':id' => (int) $id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order || $order['TrangThai'] !== 'Giao khong thanh cong') {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Chỉ hoàn hàng khi đơn đang ở trạng thái giao không thành công.'];
            }
            $total = (float) $order['PhiVanChuyen'] + $returnFee;
            $this->conn->prepare("UPDATE DonHang SET TrangThai = 'Hoan hang', LyDoHoan = :reason, PhiHoan = :fee, TongPhi = :total WHERE MaDonHang = :id")
                ->execute([':reason' => $reason, ':fee' => $returnFee, ':total' => $total, ':id' => (int) $id]);
            $this->conn->prepare("UPDATE PhanCong SET TrangThai = 'Hoan hang' WHERE MaDonHang = :id AND TrangThai NOT IN ('Da huy')")
                ->execute([':id' => (int) $id]);
            $this->conn->prepare("UPDATE COD SET TrangThai = 'Khong thu', GhiChu = :note WHERE MaDonHang = :id")
                ->execute([':note' => 'Không thu do hoàn hàng. ' . $reason, ':id' => (int) $id]);
            $this->insertHistory((int) $id, 'Hoan hang', $accountId, $reason);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã ghi nhận hoàn hàng.'];
        } catch (Exception $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Không thể hoàn hàng.'];
        }
    }

    public function getHistory($orderId) {
        $stmt = $this->conn->prepare("SELECT ls.TrangThai, ls.ThoiGian, ls.GhiChu, tk.TenDangNhap
            FROM LichSuTrangThai ls
            LEFT JOIN TaiKhoan tk ON tk.MaTaiKhoan = ls.MaTaiKhoan
            WHERE ls.MaDonHang = :id ORDER BY ls.ThoiGian ASC, ls.MaLichSu ASC");
        $stmt->execute([':id' => (int) $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchOrders($keyword = '', $status = '', $customerAccountId = null, $driverId = null) {
        $query = "SELECT d.MaDonHang, d.NgayTao, d.TongKhoiLuong, d.TienHang, d.PhiVanChuyen, d.PhiHoan,
                         d.TongPhi, d.TrangThai, d.LyDoHuy, d.LyDoHoan,
                         k.HoTen AS TenKhachHang, k.SoDienThoai AS SDTKhach,
                         dn.DiaChi AS DiaChiNhan, dn.KhuVuc AS KhuVucNhan,
                         dg.TenNguoiNhan, dg.SoDienThoai AS SDTNhan, dg.DiaChi AS DiaChiGiao, dg.KhuVuc AS KhuVucGiao,
                         tx.HoTen AS TenTaiXe, tx.KhuVucHienTai,
                         cod.SoTienCOD, cod.TrangThai AS TrangThaiCOD
                  FROM DonHang d
                  LEFT JOIN KhachHang k ON k.MaKhachHang = d.MaKhachHang
                  LEFT JOIN DiemNhan dn ON dn.MaDiemNhan = d.MaDiemNhan
                  LEFT JOIN DiemGiao dg ON dg.MaDiemGiao = d.MaDiemGiao
                  LEFT JOIN COD cod ON cod.MaDonHang = d.MaDonHang
                  LEFT JOIN PhanCong pc ON pc.MaDonHang = d.MaDonHang AND pc.TrangThai <> 'Da huy'
                    AND pc.MaPhanCong = (SELECT MAX(p2.MaPhanCong) FROM PhanCong p2 WHERE p2.MaDonHang = d.MaDonHang AND p2.TrangThai <> 'Da huy')
                  LEFT JOIN TaiXe tx ON tx.MaTaiXe = pc.MaTaiXe
                  WHERE 1 = 1";
        $params = [];
        if ($customerAccountId !== null) {
            $query .= ' AND k.MaTaiKhoan = :account_id';
            $params[':account_id'] = (int) $customerAccountId;
        }
        if ($driverId !== null) {
            $query .= ' AND pc.MaTaiXe = :driver_id';
            $params[':driver_id'] = (int) $driverId;
        }
        if ($keyword !== '') {
            $query .= " AND (CAST(d.MaDonHang AS CHAR) LIKE :keyword OR k.HoTen LIKE :keyword
                OR dg.TenNguoiNhan LIKE :keyword OR dg.SoDienThoai LIKE :keyword OR k.SoDienThoai LIKE :keyword)";
            $params[':keyword'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $query .= ' AND d.TrangThai = :status';
            $params[':status'] = $status;
        }
        $query .= ' ORDER BY d.MaDonHang DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFailedDeliveries() {
        return $this->searchOrders('', 'Giao khong thanh cong');
    }

    private function advanceStatus($id, $from, $to, $accountId, $note) {
        try {
            $this->conn->beginTransaction();
            $stmt = $this->conn->prepare("SELECT TrangThai FROM DonHang WHERE MaDonHang = :id FOR UPDATE");
            $stmt->execute([':id' => (int) $id]);
            $status = $stmt->fetchColumn();
            if ($status !== $from) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Trạng thái đơn không còn phù hợp để thao tác này.'];
            }
            $this->conn->prepare("UPDATE DonHang SET TrangThai = :status WHERE MaDonHang = :id")
                ->execute([':status' => $to, ':id' => (int) $id]);
            $this->insertHistory((int) $id, $to, $accountId, $note);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Đã cập nhật trạng thái: ' . $to];
        } catch (Exception $exception) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Không thể cập nhật trạng thái đơn hàng.'];
        }
    }

    private function insertHistory($orderId, $status, $accountId, $note) {
        $stmt = $this->conn->prepare("INSERT INTO LichSuTrangThai (MaDonHang, TrangThai, MaTaiKhoan, GhiChu)
            VALUES (:order_id, :status, :account_id, :note)");
        $stmt->execute([
            ':order_id' => (int) $orderId,
            ':status' => $status,
            ':account_id' => $accountId ? (int) $accountId : null,
            ':note' => $note
        ]);
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
