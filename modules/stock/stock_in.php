<?php
require_roles(['admin', 'warehouse']);
$pageTitle = 'Stock In';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $quantity = (float) ($_POST['quantity'] ?? 0);
    $unitCost = (float) ($_POST['unit_cost'] ?? 0);
    $referenceType = $_POST['reference_type'] ?? 'purchase';
    $remarks = trim($_POST['remarks'] ?? '');
    $userId = (int) current_user()['id'];

    if ($productId <= 0 || $quantity <= 0) {
        set_flash('danger', 'Product and quantity are required.');
        redirect('index.php?page=stock_in');
    }

    if (!in_array($referenceType, ['purchase', 'donation', 'return', 'adjustment'], true)) {
        set_flash('danger', 'Invalid stock-in source selected.');
        redirect('index.php?page=stock_in');
    }

    $ok = record_stock_movement($productId, 'in', $quantity, $unitCost, $referenceType, null, $remarks, $userId);

    if ($ok) {
        set_flash('success', 'Stock added successfully.');
    } else {
        set_flash('danger', 'Failed to add stock movement.');
    }

    redirect('index.php?page=stock_in');
}

$products = db()->query('SELECT id, code, name FROM products WHERE is_active = 1 ORDER BY name ASC');

$movementsSql = "SELECT sm.created_at, p.code, p.name, sm.quantity, sm.unit_cost, sm.reference_type, u.full_name
                 FROM stock_movements sm
                 INNER JOIN products p ON p.id = sm.product_id
                 LEFT JOIN users u ON u.id = sm.performed_by
                 WHERE sm.movement_type = 'in'
                 ORDER BY sm.id DESC
                 LIMIT 20";
$movements = db()->query($movementsSql);
?>

<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-semibold">Record Stock In</div>
                <div class="card-body">
                    <form method="post" class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Product</label>
                            <select class="form-select" name="product_id" required>
                                <option value="">Select product</option>
                                <?php while ($p = $products->fetch_assoc()): ?>
                                    <option value="<?= (int) $p['id'] ?>"><?= h($p['code']) ?> - <?= h($p['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Quantity</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" name="quantity" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="unit_cost" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reference Type</label>
                            <select class="form-select" name="reference_type" required>
                                <option value="purchase">Purchase</option>
                                <option value="donation">Donation</option>
                                <option value="return">Return</option>
                                <option value="adjustment">Adjustment</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-success" type="submit">Post Stock In</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">Recent Stock In Movements</div>
                <div class="card-body table-responsive">
                    <input type="text" class="form-control form-control-sm mb-2" placeholder="Search table..." data-table-search="stockInTable">
                    <table id="stockInTable" class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Code</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Source</th>
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
                                <td><?= h((string) $row['unit_cost']) ?></td>
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
