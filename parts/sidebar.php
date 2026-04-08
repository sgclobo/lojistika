<?php $user = current_user(); ?>
<?php $canManageInventory = has_role(['admin', 'warehouse']); ?>

<aside id="sidebar" class="sidebar">
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard"><i class="bi bi-speedometer2 me-2"></i><?= lang('nav.dashboard') ?></a></li>
        <?php if ($canManageInventory): ?>
            <li class="nav-item"><a class="nav-link" href="index.php?page=categories"><i class="bi bi-tags me-2"></i><?= lang('nav.categories') ?></a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?page=products"><i class="bi bi-boxes me-2"></i><?= lang('nav.products') ?></a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?page=stock_in"><i class="bi bi-box-arrow-in-down me-2"></i><?= lang('nav.stock_in') ?></a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?page=stock_out"><i class="bi bi-box-arrow-up me-2"></i><?= lang('nav.stock_out') ?></a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="index.php?page=requisitions"><i class="bi bi-journal-text me-2"></i><?= lang('nav.requisitions') ?></a></li>
        <?php if ($canManageInventory): ?>
            <li class="nav-item"><a class="nav-link" href="index.php?page=suppliers"><i class="bi bi-truck me-2"></i><?= lang('nav.suppliers') ?></a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?page=reports"><i class="bi bi-bar-chart-line me-2"></i><?= lang('nav.reports') ?></a></li>
        <?php endif; ?>
        <?php if (has_role(['admin'])): ?>
            <li class="nav-item"><a class="nav-link" href="index.php?page=users"><i class="bi bi-people me-2"></i><?= lang('nav.users') ?></a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?page=activity_monitor"><i class="bi bi-activity me-2"></i><?= lang('nav.activity') ?></a></li>
        <?php endif; ?>
    </ul>

    <hr>
    <div class="px-2 small text-muted">
        <?= lang('sys.signed_in_as') ?>
        <div class="fw-semibold text-dark"><?= h($user['full_name'] ?? 'Unknown User') ?></div>
        <div class="text-uppercase"><?= h($user['role'] ?? 'unknown') ?></div>
    </div>
</aside>