<?php $user = current_user(); ?>
<?php $canManageInventory = has_role(['admin', 'warehouse']); ?>

<aside id="sidebar" class="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <?php if ($canManageInventory): ?>
        <li class="nav-item"><a class="nav-link" href="index.php?page=categories"><i class="bi bi-tags me-2"></i>Categories</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?page=products"><i class="bi bi-boxes me-2"></i>Products</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?page=stock_in"><i class="bi bi-box-arrow-in-down me-2"></i>Stock In</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?page=stock_out"><i class="bi bi-box-arrow-up me-2"></i>Stock Out</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="index.php?page=requisitions"><i class="bi bi-journal-text me-2"></i>Requisitions</a></li>
        <?php if ($canManageInventory): ?>
        <li class="nav-item"><a class="nav-link" href="index.php?page=suppliers"><i class="bi bi-truck me-2"></i>Suppliers</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?page=reports"><i class="bi bi-bar-chart-line me-2"></i>Reports</a></li>
        <?php endif; ?>
    </ul>

    <hr>
    <div class="px-2 small text-muted">
        Signed in as
        <div class="fw-semibold text-dark"><?= h($user['full_name'] ?? 'Unknown User') ?></div>
        <div class="text-uppercase"><?= h($user['role'] ?? 'unknown') ?></div>
    </div>
</aside>
