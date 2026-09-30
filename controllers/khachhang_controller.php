<?php
require_once 'models/khachhang_model.php';

class CustomerController {
    public function index() {
        if (($_SESSION['role_id'] ?? 0) != 4 && ($_SESSION['role_id'] ?? 0) != 2) {
            echo "Bạn không có quyền truy cập trang này.";
            exit;
        }

        $customerModel = new CustomerModel();

        $message = '';
        $messageType = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'name' => trim($_POST['name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];
            $id = (int) ($_POST['id'] ?? 0);
            if ($data['username'] === '' || $data['name'] === '' || ($id === 0 && $data['password'] === '')) {
                $message = 'Vui lòng nhập tên đăng nhập, họ tên và mật khẩu khi thêm mới.';
                $messageType = 'error';
            } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) && $data['email'] !== '') {
                $message = 'Email không đúng định dạng.';
                $messageType = 'error';
            } else {
                $result = $id > 0 ? $customerModel->update($id, $data) : $customerModel->create($data);
                $message = $result['message'];
                $messageType = $result['success'] ? 'success' : 'error';
            }
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $result = $customerModel->delete($_GET['id']);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }

        if ($message !== '' && $_SERVER['REQUEST_METHOD'] === 'POST' && $messageType === 'success') {
            header('Location: index.php?page=khachhang');
            exit;
        }

        // Xử lý tìm kiếm
        $keyword = $_GET['keyword'] ?? '';
        
        $stmt = $customerModel->getAll($keyword);
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once 'views/khachhang.php';
    }
}
?>
