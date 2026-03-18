<?php

declare(strict_types=1);

require_once __DIR__ . '/config/helpers.php';

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout_user();
    set_flash('success', 'You have been signed out.');
    redirect('index.php?page=login');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $user = authenticate_user($email, $password);

    if ($user) {
        login_user($user);
        set_flash('success', 'Welcome back, ' . $user['full_name'] . '.');
        redirect('index.php?page=dashboard');
    }

    set_flash('danger', 'Invalid email or password.');
    redirect('index.php?page=login');
}

$page = $_GET['page'] ?? '';

if ($page === '') {
    redirect('index.php?page=dashboard');
}
$pageTitle = 'Logistics Management System';
$target = null;

if ($page !== 'login') {
    require_login();
} elseif (is_logged_in()) {
    redirect('index.php?page=dashboard');
}

switch ($page) {
    case 'login':
        $pageTitle = 'Sign In';
        $target = __DIR__ . '/modules/auth/login.php';
        break;
    case 'register':
        require_roles(['admin']);
        require_register_users_permission();
        $pageTitle = 'Register User';
        $target = __DIR__ . '/modules/auth/register.php';
        break;
    case 'change_password':
        $pageTitle = 'Change Password';
        $target = __DIR__ . '/modules/auth/change_password.php';
        break;
    case 'dashboard':
        $pageTitle = 'Dashboard';
        $target = __DIR__ . '/modules/dashboard/index.php';
        break;
    case 'categories':
        require_roles(['admin', 'warehouse']);
        $pageTitle = 'Categories';
        $target = __DIR__ . '/modules/categories/index.php';
        break;
    case 'products':
        require_roles(['admin', 'warehouse']);
        $pageTitle = 'Products';
        $target = __DIR__ . '/modules/products/index.php';
        break;
    case 'product_form':
        require_roles(['admin', 'warehouse']);
        $pageTitle = 'Product Form';
        $target = __DIR__ . '/modules/products/form.php';
        break;
    case 'stock_in':
        require_roles(['admin', 'warehouse']);
        $pageTitle = 'Stock In';
        $target = __DIR__ . '/modules/stock/stock_in.php';
        break;
    case 'stock_out':
        require_roles(['admin', 'warehouse']);
        $pageTitle = 'Stock Out';
        $target = __DIR__ . '/modules/stock/stock_out.php';
        break;
    case 'requisitions':
        $pageTitle = 'Requisitions';
        $target = __DIR__ . '/modules/requisitions/index.php';
        break;
    case 'suppliers':
        require_roles(['admin', 'warehouse']);
        $pageTitle = 'Suppliers';
        $target = __DIR__ . '/modules/suppliers/index.php';
        break;
    case 'reports':
        require_roles(['admin', 'warehouse']);
        $pageTitle = 'Reports';
        $target = __DIR__ . '/modules/reports/index.php';
        break;
    default:
        $pageTitle = 'Page Not Found';
        $target = null;
        break;
}

require __DIR__ . '/parts/header.php';

if ($page !== 'login') {
    require __DIR__ . '/parts/sidebar.php';
    echo '<main id="main" class="main">';
    if ($target && file_exists($target)) {
        require $target;
    } else {
        echo '<div class="alert alert-danger">Page not found.</div>';
    }
    echo '</main>';
} else {
    echo '<main class="container mt-header py-4">';
    if ($target && file_exists($target)) {
        require $target;
    } else {
        echo '<div class="alert alert-danger">Page not found.</div>';
    }
    echo '</main>';
}

require __DIR__ . '/parts/footer.php';
