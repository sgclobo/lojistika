<?php

declare(strict_types=1);

require_once __DIR__ . '/config/helpers.php';

if (isset($_GET['switch_role'])) {
    $role = $_GET['switch_role'];
    $map = [
        'admin' => ['id' => 1, 'full_name' => 'System Admin', 'role' => 'admin'],
        'warehouse' => ['id' => 2, 'full_name' => 'Warehouse Officer', 'role' => 'warehouse'],
        'requester' => ['id' => 3, 'full_name' => 'Department Requester', 'role' => 'requester'],
    ];

    if (isset($map[$role])) {
        $_SESSION['user'] = $map[$role];
        set_flash('success', 'Switched role to ' . $role . ' (demo mode).');
    }

    redirect('index.php?page=dashboard');
}

$page = $_GET['page'] ?? 'dashboard';
$pageTitle = 'Logistics Management System';
$target = null;

switch ($page) {
    case 'dashboard':
        $pageTitle = 'Dashboard';
        $target = __DIR__ . '/modules/dashboard/index.php';
        break;
    case 'categories':
        $pageTitle = 'Categories';
        $target = __DIR__ . '/modules/categories/index.php';
        break;
    case 'products':
        $pageTitle = 'Products';
        $target = __DIR__ . '/modules/products/index.php';
        break;
    case 'product_form':
        $pageTitle = 'Product Form';
        $target = __DIR__ . '/modules/products/form.php';
        break;
    case 'stock_in':
        $pageTitle = 'Stock In';
        $target = __DIR__ . '/modules/stock/stock_in.php';
        break;
    case 'stock_out':
        $pageTitle = 'Stock Out';
        $target = __DIR__ . '/modules/stock/stock_out.php';
        break;
    case 'requisitions':
        $pageTitle = 'Requisitions';
        $target = __DIR__ . '/modules/requisitions/index.php';
        break;
    case 'suppliers':
        $pageTitle = 'Suppliers';
        $target = __DIR__ . '/modules/suppliers/index.php';
        break;
    case 'reports':
        $pageTitle = 'Reports';
        $target = __DIR__ . '/modules/reports/index.php';
        break;
    default:
        $pageTitle = 'Page Not Found';
        $target = null;
        break;
}

require __DIR__ . '/parts/header.php';
require __DIR__ . '/parts/sidebar.php';

echo '<main id="main" class="main">';
if ($target && file_exists($target)) {
    require $target;
} else {
    echo '<div class="alert alert-danger">Page not found.</div>';
}
echo '</main>';

require __DIR__ . '/parts/footer.php';
