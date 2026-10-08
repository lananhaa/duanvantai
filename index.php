<?php
session_start();

$page = $_GET['page'] ?? 'trangchu';
$action = $_GET['action'] ?? '';

// Logout action
if ($action === 'logout') {
    require_once 'controllers/dangnhap_controller.php';
    $auth = new AuthController();
    $auth->logout();
}

// Router
if ($page === 'dangnhap') {
    require_once 'controllers/dangnhap_controller.php';
    $auth = new AuthController();
    $auth->login();
} else {
    // Other pages require login
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=dangnhap");
        exit;
    }

    if ($page === 'trangchu' || $page === 'phanquyen') {
        require_once 'views/trangchu.php';
    } elseif ($page === 'khachhang') {
        require_once 'controllers/khachhang_controller.php';
        $controller = new CustomerController();
        $controller->index();
    } elseif ($page === 'donhang') {
        require_once 'controllers/donhang_controller.php';
        $controller = new OrderController();
        $controller->index();
    } elseif ($page === 'taodonhang') {
        require_once 'controllers/donhang_controller.php';
        $controller = new OrderController();
        $controller->create();
    } elseif ($page === 'phancong') {
        require_once 'controllers/phancong_controller.php';
        $controller = new AssignController();
        $controller->index();
    } elseif ($page === 'donhangtaixe' || $page === 'donhangtaixe') {
        require_once 'controllers/donhangtaixe_controller.php';
        $controller = new DriverOrderController();
        $controller->index();
    } elseif ($page === 'taixe') {
        require_once 'controllers/taixe_controller.php';
        $controller = new DriverController();
        $controller->index();
    } elseif ($page === 'phuongtien') {
        require_once 'controllers/phuongtien_controller.php';
        $controller = new VehicleController();
        $controller->index();
    } elseif ($page === 'tuyengiao') {
        require_once 'controllers/tuyengiao_controller.php';
        $controller = new RouteController();
        $controller->index();
    } elseif ($page === 'phivanchuyen') {
        require_once 'controllers/phivanchuyen_controler.php';
        $controller = new PhivanchuyenControler();
        $controller->index();
    }elseif ($page === 'diadiem') {
        require_once 'controllers/diadiem_controller.php';
        $controller = new LocationController();
        $controller->index();
    } elseif ($page === 'thuho') {
        require_once 'controllers/thuho_controller.php';
        $controller = new CodController();
        $controller->index();
    } elseif ($page === 'thongke') {
        require_once 'controllers/thongke_controller.php';
        $controller = new StatisticController();
        $controller->index();
    } elseif ($page === 'taikhoan') {
        require_once 'controllers/taikhoan_controller.php';
        $controller = new AccountController();
        $controller->index();
    } elseif ($page === 'nhanvien') {
        require_once 'controllers/nhanvien_controller.php';
        $controller = new EmployeeController();
        $controller->index();
    } elseif ($page === 'hoso') {
        require_once 'controllers/hoso_controller.php';
        $controller = new ProfileController();
        $controller->index();
    } else {
        echo "404 Not Found";
    }
}
?>
