<?php
require_roles(['admin', 'warehouse']);
$pageTitle = 'Reports';

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');

$consumptionSql = "SELECT r.department, p.name AS product_name,
                   SUM(ri.quantity_approved) AS total_consumed
                   FROM requisitions r
                   INNER JOIN requisition_items ri ON ri.requisition_id = r.id
                   INNER JOIN products p ON p.id = ri.product_id
                   WHERE r.status IN ('approved', 'delivered')
                     AND DATE(r.created_at) BETWEEN ? AND ?
                   GROUP BY r.department, p.name
                   ORDER BY r.department, total_consumed DESC";
$stmt = db()->prepare($consumptionSql);
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$consumption = $stmt->get_result();
$stmt->close();

$historySql = "SELECT sm.created_at, p.code, p.name, sm.movement_type, sm.quantity, sm.reference_type, u.full_name
               FROM stock_movements sm
               INNER JOIN products p ON p.id = sm.product_id
               LEFT JOIN users u ON u.id = sm.performed_by
               WHERE DATE(sm.created_at) BETWEEN ? AND ?
               ORDER BY sm.id DESC
               LIMIT 200";
$stmt = db()->prepare($historySql);
$stmt->bind_param('ss', $from, $to);
$stmt->execute();
$history = $stmt->get_result();
$stmt->close();

$stockSql = "SELECT p.code, p.name, p.unit, p.min_stock,
            COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE -sm.quantity END), 0) AS current_stock
            FROM products p
            LEFT JOIN stock_movements sm ON sm.product_id = p.id
            GROUP BY p.id, p.code, p.name, p.unit, p.min_stock
            ORDER BY p.name ASC";
$stockSnapshot = db()->query($stockSql);
?>

<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2" method="get">
                <input type="hidden" name="page" value="reports">
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input type="date" class="form-control" name="from" value="<?= h($from) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input type="date" class="form-control" name="to" value="<?= h($to) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit">Apply</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold">Consumption by Department</div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Product</th>
                                <th>Consumed Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($consumption->num_rows === 0): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No consumption data in selected range.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php while ($row = $consumption->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($row['department']) ?></td>
                                <td><?= h($row['product_name']) ?></td>
                                <td><?= h((string) $row['total_consumed']) ?></td>
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
                <div class="card-header fw-semibold">Current Stock Snapshot</div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Product</th>
                                <th>Unit</th>
                                <th>Stock</th>
                                <th>Min</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stockSnapshot->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($row['code']) ?></td>
                                <td><?= h($row['name']) ?></td>
                                <td><?= h($row['unit']) ?></td>
                                <td>
                                    <span
                                        class="badge <?= (float) $row['current_stock'] <= (float) $row['min_stock'] ? 'text-bg-danger' : 'text-bg-success' ?>">
                                        <?= h((string) $row['current_stock']) ?>
                                    </span>
                                </td>
                                <td><?= h((string) $row['min_stock']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header fw-semibold">Movement Logs</div>
                <div class="card-body table-responsive">
                    <input type="text" class="form-control form-control-sm mb-2" placeholder="Search table..."
                        data-table-search="movementTable">
                    <table id="movementTable" class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Code</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Reference</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $history->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($row['created_at']) ?></td>
                                <td><?= h($row['code']) ?></td>
                                <td><?= h($row['name']) ?></td>
                                <td><?= strtoupper(h($row['movement_type'])) ?></td>
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