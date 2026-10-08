<?php
require_once 'config/database.php';

class VehicleModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '', $status = '', $driverId = null) {
        $query = "SELECT pt.*, tx.HoTen AS TenTaiXe
                  FROM PhuongTien pt
                  LEFT JOIN TaiXe tx ON tx.MaTaiXe = pt.MaTaiXe
                  WHERE 1=1";
        $params = [];
        if ($driverId !== null) {
            $query .= " AND pt.MaTaiXe = :driver_id";
            $params[':driver_id'] = (int)$driverId;
        }
        if ($keyword !== '') {
            $query .= " AND (pt.BienSo LIKE :kw OR pt.LoaiPhuongTien LIKE :kw OR pt.MoTa LIKE :kw OR tx.HoTen LIKE :kw)";
            $params[':kw'] = '%' . $keyword . '%';
        }
        if ($status !== '') {
            $query .= " AND pt.TrangThai = :status";
            $params[':status'] = $status;
        }
        $query .= " ORDER BY pt.MaPhuongTien DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDriverIdByAccount($accountId) {
        $stmt = $this->conn->prepare("SELECT MaTaiXe FROM TaiXe WHERE MaTaiKhoan = :account_id");
        $stmt->execute([':account_id' => (int)$accountId]);
        $driverId = $stmt->fetchColumn();
        return $driverId === false ? null : (int)$driverId;
    }

    public function getDriverOptions() {
        return $this->conn->query("SELECT MaTaiXe, HoTen FROM TaiXe ORDER BY HoTen")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDefaultStatus($driverId) {
        if ($driverId === null) {
            return 'San sang';
        }
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM PhuongTien WHERE MaTaiXe = :driver_id AND TrangThai = 'San sang'");
        $stmt->execute([':driver_id' => (int)$driverId]);
        return (int)$stmt->fetchColumn() === 0 ? 'San sang' : 'Du phong';
    }

    public function getById($id, $driverId = null) {
        $query = "SELECT * FROM PhuongTien WHERE MaPhuongTien = :id";
        $params = [':id' => (int)$id];
        if ($driverId !== null) {
            $query .= " AND MaTaiXe = :driver_id";
            $params[':driver_id'] = (int)$driverId;
        }
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function lockDriver($driverId) {
        if ($driverId === null) {
            return true;
        }
        $stmt = $this->conn->prepare("SELECT MaTaiXe FROM TaiXe WHERE MaTaiXe = :driver_id FOR UPDATE");
        $stmt->execute([':driver_id' => (int)$driverId]);
        return $stmt->fetchColumn() !== false;
    }

    private function lockDrivers($driverIds) {
        $driverIds = array_values(array_unique(array_filter(array_map('intval', $driverIds))));
        sort($driverIds);
        foreach ($driverIds as $driverId) {
            if (!$this->lockDriver($driverId)) {
                return false;
            }
        }
        return true;
    }

    private function hasOtherReadyVehicle($driverId, $vehicleId = null) {
        if ($driverId === null) {
            return false;
        }
        $sql = "SELECT MaPhuongTien FROM PhuongTien
                WHERE MaTaiXe = :driver_id AND TrangThai = 'San sang'";
        $params = [':driver_id' => (int)$driverId];
        if ($vehicleId !== null) {
            $sql .= " AND MaPhuongTien != :vehicle_id";
            $params[':vehicle_id'] = (int)$vehicleId;
        }
        $sql .= " LIMIT 1 FOR UPDATE";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    private function promoteReserveVehicle($driverId) {
        if ($driverId === null || $this->hasOtherReadyVehicle($driverId)) {
            return;
        }
        $stmt = $this->conn->prepare("SELECT MaPhuongTien FROM PhuongTien
            WHERE MaTaiXe = :driver_id AND TrangThai = 'Du phong'
            ORDER BY MaPhuongTien LIMIT 1 FOR UPDATE");
        $stmt->execute([':driver_id' => (int)$driverId]);
        $vehicleId = $stmt->fetchColumn();
        if ($vehicleId !== false) {
            $this->conn->prepare("UPDATE PhuongTien SET TrangThai = 'San sang' WHERE MaPhuongTien = :id")
                ->execute([':id' => (int)$vehicleId]);
        }
    }

    public function create($data, $ownerDriverId = null) {
        try {
            $this->conn->beginTransaction();
            if (!$this->lockDriver($ownerDriverId)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy tài xế được gán.'];
            }
            if ($data['status'] === 'San sang' && $this->hasOtherReadyVehicle($ownerDriverId)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Tài xế này đã có phương tiện sẵn sàng. Hãy dùng trạng thái Dự phòng.'];
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO PhuongTien (MaTaiXe, BienSo, LoaiPhuongTien, TaiTrong, TrangThai, MoTa)
                 VALUES (:driver_id, :bs, :loai, :tai, :tt, :mo)"
            );
            $stmt->execute([
                ':driver_id' => $ownerDriverId === null ? null : (int)$ownerDriverId,
                ':bs' => $data['bien_so'],
                ':loai' => $data['loai'],
                ':tai' => $data['tai_trong'],
                ':tt' => $data['status'],
                ':mo' => $data['mo_ta'],
            ]);
            $this->conn->commit();
            return ['success' => true, 'message' => 'Thêm phương tiện thành công.'];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => $e->getCode() === '23000'
                ? 'Biển số xe đã tồn tại hoặc tài xế được gán không hợp lệ.'
                : 'Không thể thêm phương tiện. Vui lòng thử lại.'];
        }
    }

    public function update($id, $data, $driverScopeId = null, $ownerDriverId = null) {
        $vehicle = $this->getById($id, $driverScopeId);
        if (!$vehicle) {
            return ['success' => false, 'message' => 'Không tìm thấy phương tiện.'];
        }

        try {
            $this->conn->beginTransaction();
            if ($driverScopeId !== null) {
                $ownerDriverId = (int)$driverScopeId;
            }
            $oldDriverId = $vehicle['MaTaiXe'] === null ? null : (int)$vehicle['MaTaiXe'];
            if (!$this->lockDrivers([$oldDriverId, $ownerDriverId])) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy tài xế được gán.'];
            }
            $current = $this->conn->prepare("SELECT MaTaiXe FROM PhuongTien WHERE MaPhuongTien = :id FOR UPDATE");
            $current->execute([':id' => (int)$id]);
            $currentDriverId = $current->fetchColumn();
            $currentDriverId = $currentDriverId === false || $currentDriverId === null ? null : (int)$currentDriverId;
            if ($currentDriverId !== $oldDriverId || ($driverScopeId !== null && $currentDriverId !== (int)$driverScopeId)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Phương tiện vừa được thay đổi. Vui lòng tải lại trang và thử lại.'];
            }
            if ($data['status'] === 'San sang' && $this->hasOtherReadyVehicle($ownerDriverId, $id)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Tài xế này đã có phương tiện sẵn sàng. Hãy dùng trạng thái Dự phòng.'];
            }

            $query = "UPDATE PhuongTien
                      SET MaTaiXe = :driver_id, BienSo = :bs, LoaiPhuongTien = :loai,
                          TaiTrong = :tai, TrangThai = :tt, MoTa = :mo
                      WHERE MaPhuongTien = :id";
            $params = [
                ':driver_id' => $ownerDriverId === null ? null : (int)$ownerDriverId,
                ':bs' => $data['bien_so'],
                ':loai' => $data['loai'],
                ':tai' => $data['tai_trong'],
                ':tt' => $data['status'],
                ':mo' => $data['mo_ta'],
                ':id' => (int)$id,
            ];
            if ($driverScopeId !== null) {
                $query .= " AND MaTaiXe = :scope_driver_id";
                $params[':scope_driver_id'] = (int)$driverScopeId;
            }
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            if ($stmt->rowCount() === 0 && !$this->getById($id, $driverScopeId)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Bạn không có quyền cập nhật phương tiện này.'];
            }
            if ($oldDriverId !== null && ($oldDriverId !== $ownerDriverId || $data['status'] !== 'San sang')) {
                $this->promoteReserveVehicle($oldDriverId);
            }
            $this->conn->commit();
            return ['success' => true, 'message' => 'Cập nhật phương tiện thành công.'];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => $e->getCode() === '23000'
                ? 'Biển số xe đã tồn tại hoặc tài xế được gán không hợp lệ.'
                : 'Không thể cập nhật phương tiện. Vui lòng thử lại.'];
        }
    }

    public function delete($id, $driverId = null) {
        try {
            $this->conn->beginTransaction();
            $vehicle = $this->conn->prepare("SELECT MaTaiXe, TrangThai FROM PhuongTien WHERE MaPhuongTien = :id");
            $vehicle->execute([':id' => (int)$id]);
            $row = $vehicle->fetch(PDO::FETCH_ASSOC);
            if (!$row || ($driverId !== null && (int)$row['MaTaiXe'] !== (int)$driverId)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy phương tiện.'];
            }
            $ownerDriverId = $row['MaTaiXe'] === null ? null : (int)$row['MaTaiXe'];
            if (!$this->lockDriver($ownerDriverId)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy tài xế của phương tiện.'];
            }
            $lockedVehicle = $this->conn->prepare("SELECT MaTaiXe, TrangThai FROM PhuongTien WHERE MaPhuongTien = :id FOR UPDATE");
            $lockedVehicle->execute([':id' => (int)$id]);
            $lockedRow = $lockedVehicle->fetch(PDO::FETCH_ASSOC);
            $lockedOwnerId = !$lockedRow || $lockedRow['MaTaiXe'] === null ? null : (int)$lockedRow['MaTaiXe'];
            if (!$lockedRow || $lockedOwnerId !== $ownerDriverId || ($driverId !== null && $lockedOwnerId !== (int)$driverId)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Phương tiện vừa được thay đổi. Vui lòng tải lại trang và thử lại.'];
            }
            $row = $lockedRow;
            $query = "DELETE FROM PhuongTien WHERE MaPhuongTien = :id";
            $params = [':id' => (int)$id];
            if ($driverId !== null) {
                $query .= " AND MaTaiXe = :driver_id";
                $params[':driver_id'] = (int)$driverId;
            }
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            if ($stmt->rowCount() !== 1) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Bạn không có quyền xóa phương tiện này.'];
            }
            if ($row['TrangThai'] === 'San sang') {
                $this->promoteReserveVehicle($ownerDriverId);
            }
            $this->conn->commit();
            return ['success' => true, 'message' => 'Xóa phương tiện thành công.'];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['success' => false, 'message' => 'Không thể xóa phương tiện hiện đang được sử dụng.'];
        }
    }
}
?>
