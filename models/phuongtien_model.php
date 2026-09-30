<?php
require_once 'config/database.php';

class VehicleModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($keyword = '', $status = '') {
        $query = "SELECT pt.*, 
                         (SELECT COUNT(*) FROM TaiXe tx WHERE tx.MaPhuongTien = pt.MaPhuongTien) AS SoTaiXe
                  FROM PhuongTien pt WHERE 1=1";
        $params = [];
        if ($keyword !== '') {
            $query .= " AND (pt.BienSo LIKE :kw OR pt.LoaiPhuongTien LIKE :kw OR pt.MoTa LIKE :kw)";
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

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM PhuongTien WHERE MaPhuongTien = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        // Kiểm tra biển số trùng
        $check = $this->conn->prepare("SELECT MaPhuongTien FROM PhuongTien WHERE BienSo = :bs");
        $check->execute([':bs' => $data['bien_so']]);
        if ($check->fetchColumn()) return ['success' => false, 'message' => 'Biển số xe đã tồn tại.'];

        try {
            $this->conn->prepare(
                "INSERT INTO PhuongTien (BienSo, LoaiPhuongTien, TaiTrong, TrangThai, MoTa)
                 VALUES (:bs, :loai, :tai, :tt, :mo)"
            )->execute([
                ':bs'   => $data['bien_so'],
                ':loai' => $data['loai'],
                ':tai'  => $data['tai_trong'],
                ':tt'   => $data['status'],
                ':mo'   => $data['mo_ta'],
            ]);
            return ['success' => true, 'message' => 'Thêm phương tiện thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể thêm phương tiện.'];
        }
    }

    public function update($id, $data) {
        $pt = $this->getById($id);
        if (!$pt) return ['success' => false, 'message' => 'Không tìm thấy phương tiện.'];

        $check = $this->conn->prepare("SELECT MaPhuongTien FROM PhuongTien WHERE BienSo = :bs AND MaPhuongTien != :id");
        $check->execute([':bs' => $data['bien_so'], ':id' => (int)$id]);
        if ($check->fetchColumn()) return ['success' => false, 'message' => 'Biển số xe đã tồn tại.'];

        try {
            $this->conn->prepare(
                "UPDATE PhuongTien SET BienSo = :bs, LoaiPhuongTien = :loai, TaiTrong = :tai, TrangThai = :tt, MoTa = :mo WHERE MaPhuongTien = :id"
            )->execute([
                ':bs'   => $data['bien_so'],
                ':loai' => $data['loai'],
                ':tai'  => $data['tai_trong'],
                ':tt'   => $data['status'],
                ':mo'   => $data['mo_ta'],
                ':id'   => (int)$id,
            ]);
            return ['success' => true, 'message' => 'Cập nhật phương tiện thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể cập nhật phương tiện.'];
        }
    }

    public function delete($id) {
        try {
            $this->conn->prepare("DELETE FROM PhuongTien WHERE MaPhuongTien = :id")->execute([':id' => (int)$id]);
            return ['success' => true, 'message' => 'Xóa phương tiện thành công.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Không thể xóa phương tiện đang được sử dụng bởi tài xế.'];
        }
    }
}
?>
