<?php
session_start();

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

// Logout action
if ($action === 'logout') {
    require_once 'controllers/AuthController.php';
    $auth = new AuthController();
    $auth->logout();
}

// Router
if ($page === 'login') {
    require_once 'controllers/AuthController.php';
    $auth = new AuthController();
    $auth->login();
} else {
    // Other pages require login
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php?page=login");
        exit;
    }

    if ($page === 'dashboard' || $page === 'permissions') {
        require_once 'views/dashboard.php';
    } elseif ($page === 'customers') {
        require_once 'controllers/CustomerController.php';
        $controller = new CustomerController();
        $controller->index();
    } elseif ($page === 'orders') {
        require_once 'controllers/OrderController.php';
        $controller = new OrderController();
        $controller->index();
    } elseif ($page === 'assign') {
        require_once 'controllers/AssignController.php';
        $controller = new AssignController();
        $controller->index();
    } elseif ($page === 'driver-orders' || $page === 'tracking') {
        require_once 'controllers/DriverOrderController.php';
        $controller = new DriverOrderController();
        $controller->index();
    } else {
        echo "404 Not Found";
    }
}
?>
