<?php
require_roles(['admin', 'warehouse']);
$pageTitle = 'Stock Out';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = (float) ($_POST['quantity'] ?? 0);
    $referenceType = $_POST['reference_type'] ?? 'distribution';
    $remarks = trim($_POST['remarks'] ?? '');
    $userId = (int) current_user()['id'];

    if ($productId <= 0 || $quantity <= 0) {
        set_flash('danger', 'Product and quantity are required.');
        redirect('index.php?page=stock_out');
    }

    if (!in_array($referenceType, ['distribution', 'adjustment'], true)) {
        set_flash('danger', 'Invalid stock-out type selected.');
        redirect('index.php?page=stock_out');
    }

    $ok = record_stock_movement($productId, 'out', $quantity, 0, $referenceType, null, $remarks, $userId);

    if ($ok) {
        set_flash('success', 'Stock out recorded successfully.');
    } else {
        set_flash('danger', 'Stock out failed. Check available stock.');
    }

    redirect('index.php?page=stock_out');
}

$productsSql = "SELECT p.id, p.code, p.name,
                COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE -sm.quantity END), 0) AS balance
                FROM products p
                LEFT JOIN stock_movements sm ON sm.product_id = p.id
                WHERE p.is_active = 1
                GROUP BY p.id, p.code, p.name
                ORDER BY p.name ASC";
$products = db()->query($productsSql);

$movementsSql = "SELECT sm.created_at, p.code, p.name, sm.quantity, sm.reference_type, u.full_name
                 FROM stock_movements sm
                 INNER JOIN products p ON p.id = sm.product_id
                 LEFT JOIN users u ON u.id = sm.performed_by
                 WHERE sm.movement_type = 'out'
                 ORDER BY sm.id DESC
                 LIMIT 20";
$movements = db()->query($movementsSql);
?>

<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-semibold">Record Stock Out</div>
                <div class="card-body">
                    <form method="post" class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select product</option>
                                <?php while ($p = $products->fetch_assoc()): ?>
                                    <option value="<?= (int) $p['id'] ?>">
                                        <?= h($p['code']) ?> - <?= h($p['name']) ?> (Stock: <?= h((string) $p['balance']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="quantity" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reference Type</label>
                            <select class="form-select" name="reference_type" required>
                                <option value="distribution">Distribution</option>
                                <option value="adjustment">Adjustment</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-warning" type="submit">Post Stock Out</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">Recent Stock Out Movements</div>
                <div class="card-body table-responsive">
                    <input type="text" class="form-control form-control-sm mb-2" placeholder="Search table..." data-table-search="stockOutTable">
                    <table id="stockOutTable" class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Type</th>
                            <th>By</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $movements->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($row['created_at']) ?></td>
                                <td><?= h($row['code']) ?></td>
                                <td><?= h($row['name']) ?></td>
                                <td><?= h((string) $row['quantity']) ?></td>
                                <td><?= h($row['reference_type']) ?></td>
                                <td><?= h($row['full_name']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
