<?php
$pageTitle = 'Dashboard';

$sqlTotals = "SELECT
    (SELECT COUNT(*) FROM products WHERE is_active = 1) AS total_products,
    (SELECT COUNT(*) FROM requisitions WHERE status = 'pending') AS pending_requisitions";
$totals = db()->query($sqlTotals)->fetch_assoc();

$lowStockSql = "SELECT p.id, p.code, p.name, p.min_stock,
    COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE -sm.quantity END), 0) AS current_stock
    FROM products p
    LEFT JOIN stock_movements sm ON sm.product_id = p.id
    WHERE p.is_active = 1
    GROUP BY p.id, p.code, p.name, p.min_stock
    HAVING current_stock <= p.min_stock
    ORDER BY current_stock ASC
    LIMIT 10";
$lowStockItems = db()->query($lowStockSql);

$recentMovementsSql = "SELECT sm.created_at, p.name AS product_name, sm.movement_type, sm.quantity, sm.reference_type, u.full_name
    FROM stock_movements sm
    INNER JOIN products p ON p.id = sm.product_id
    LEFT JOIN users u ON u.id = sm.performed_by
    ORDER BY sm.id DESC
    LIMIT 10";
$recentMovements = db()->query($recentMovementsSql);
?>

<div class="container-fluid">
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="text-muted"><?= lang('dash.total_products') ?></h6>
                    <h3><?= (int) $totals['total_products'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="text-muted"><?= lang('dash.low_stock_alerts') ?></h6>
                    <h3><?= (int) $lowStockItems->num_rows ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <h6 class="text-muted"><?= lang('dash.pending_requisitions') ?></h6>
                    <h3><?= (int) $totals['pending_requisitions'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold"><?= lang('dash.low_stock_items') ?></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th><?= lang('lbl.code') ?></th>
                                <th><?= lang('lbl.product') ?></th>
                                <th><?= lang('lbl.current_stock') ?></th>
                                <th><?= lang('lbl.min') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($lowStockItems->num_rows === 0): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted"><?= lang('dash.no_low_stock') ?></td>
                                </tr>
                            <?php else: ?>
                                <?php while ($item = $lowStockItems->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= h($item['code']) ?></td>
                                        <td><?= h($item['name']) ?></td>
                                        <td><span class="badge text-bg-danger"><?= h((string) $item['current_stock']) ?></span></td>
                                        <td><?= h((string) $item['min_stock']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold"><?= lang('dash.recent_movements') ?></div>
                <div class="card-body table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th><?= lang('lbl.date') ?></th>
                                <th><?= lang('lbl.product') ?></th>
                                <th><?= lang('lbl.type') ?></th>
                                <th><?= lang('lbl.qty') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recentMovements->fetch_assoc()): ?>
                                <tr>
                                    <td><?= h($row['created_at']) ?></td>
                                    <td><?= h($row['product_name']) ?></td>
                                    <td>
                                        <span class="badge <?= $row['movement_type'] === 'in' ? 'text-bg-success' : 'text-bg-warning' ?>">
                                            <?= strtoupper(h($row['movement_type'])) ?>
                                        </span>
                                    </td>
                                    <td><?= h((string) $row['quantity']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>