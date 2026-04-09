<?php

declare(strict_types=1);

require_once __DIR__ . '/config/helpers.php';

// Language switch
if (isset($_GET['action']) && $_GET['action'] === 'set_lang') {
    set_locale((string) ($_GET['lang'] ?? 'en'));
    $returnPage = (string) ($_GET['return'] ?? '');
    if (preg_match('/^index\.php\?page=[a-zA-Z0-9_]+$/', $returnPage)) {
        redirect($returnPage);
    }
    redirect('index.php?page=dashboard');
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    log_activity('logout', 'User signed out');
    logout_user();
    set_flash('success', lang('msg.signed_out'));
    redirect('index.php?page=login');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $user = authenticate_user($email, $password);

    if ($user) {
        login_user($user);
        log_activity('login', 'User signed in');
        set_flash('success', lang('msg.login_welcome', $user['full_name']));
        redirect('index.php?page=dashboard');
    }

    set_flash('danger', lang('msg.login_invalid'));
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
        require_roles(['admin', 'supervisor', 'warehouse']);
        $pageTitle = 'Categories';
        $target = __DIR__ . '/modules/categories/index.php';
        break;
    case 'products':
        require_roles(['admin', 'supervisor', 'warehouse']);
        $pageTitle = 'Products';
        $target = __DIR__ . '/modules/products/index.php';
        break;
    case 'product_form':
        require_roles(['admin', 'supervisor', 'warehouse']);
        $pageTitle = 'Product Form';
        $target = __DIR__ . '/modules/products/form.php';
        break;
    case 'stock_in':
        require_roles(['admin', 'supervisor', 'warehouse']);
        $pageTitle = 'Stock In';
        $target = __DIR__ . '/modules/stock/stock_in.php';
        break;
    case 'stock_out':
        require_roles(['admin', 'supervisor', 'warehouse']);
        $pageTitle = 'Stock Out';
        $target = __DIR__ . '/modules/stock/stock_out.php';
        break;
    case 'requisitions':
        $pageTitle = 'Requisitions';
        $target = __DIR__ . '/modules/requisitions/index.php';
        break;
    case 'suppliers':
        require_roles(['admin', 'supervisor', 'warehouse']);
        $pageTitle = 'Suppliers';
        $target = __DIR__ . '/modules/suppliers/index.php';
        break;
    case 'reports':
        require_roles(['admin', 'supervisor', 'warehouse']);
        $pageTitle = 'Reports';
        $target = __DIR__ . '/modules/reports/index.php';
        break;
    case 'users':
        require_roles(['admin']);
        $pageTitle = 'User Management';
        $target = __DIR__ . '/modules/users/index.php';
        break;
    case 'user_view':
        require_roles(['admin']);
        $pageTitle = 'View User';
        $target = __DIR__ . '/modules/users/view.php';
        break;
    case 'user_edit':
        require_roles(['admin']);
        $pageTitle = 'Edit User';
        $target = __DIR__ . '/modules/users/edit.php';
        break;
    case 'activity_monitor':
        require_roles(['admin', 'supervisor']);
        $pageTitle = 'Activity Monitor';
        $target = __DIR__ . '/modules/activity/index.php';
        break;
    default:
        $pageTitle = 'Page Not Found';
        $target = null;
        break;
}

require __DIR__ . '/parts/header.php';

if (is_logged_in() && $page !== 'login') {
    $skipLogPages = ['dashboard'];
    if (!in_array($page, $skipLogPages, true)) {
        log_activity('page_access', 'Accessed page: ' . $page);
    }
}

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
