<?php
$pageTitle = 'Requisitions';
$user = current_user();
$userId = (int) $user['id'];
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        require_roles(['admin', 'warehouse', 'requester']);

        $department = trim($_POST['department'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');
        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity_requested'] ?? [];

        if ($department === '' || empty($productIds)) {
            set_flash('danger', 'Department and at least one item are required.');
            redirect('index.php?page=requisitions');
        }

        db()->begin_transaction();
        try {
            $reqNumber = generate_requisition_number();
            $status = 'pending';
            $stmt = db()->prepare('INSERT INTO requisitions (req_number, requester_id, department, purpose, status) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('sisss', $reqNumber, $userId, $department, $purpose, $status);
            $stmt->execute();
            $requisitionId = (int) db()->insert_id;
            $stmt->close();

            $itemStmt = db()->prepare('INSERT INTO requisition_items (requisition_id, product_id, quantity_requested) VALUES (?, ?, ?)');
            $validItems = 0;
            foreach ($productIds as $idx => $pidRaw) {
                $pid = (int) $pidRaw;
                $qty = (float) ($quantities[$idx] ?? 0);
                if ($pid > 0 && $qty > 0) {
                    $itemStmt->bind_param('iid', $requisitionId, $pid, $qty);
                    $itemStmt->execute();
                    $validItems++;
                }
            }
            $itemStmt->close();

            if ($validItems === 0) {
                throw new RuntimeException('No valid requisition items provided.');
            }

            db()->commit();
            set_flash('success', 'Requisition submitted successfully.');
        } catch (Throwable $e) {
            db()->rollback();
            set_flash('danger', 'Failed to submit requisition: ' . $e->getMessage());
        }

        redirect('index.php?page=requisitions');
    }

    if ($action === 'approve') {
        require_roles(['admin']);
        $requisitionId = (int) ($_POST['requisition_id'] ?? 0);

        if ($requisitionId <= 0) {
            set_flash('danger', 'Invalid requisition.');
            redirect('index.php?page=requisitions');
        }

        db()->begin_transaction();
        try {
            $statusStmt = db()->prepare('SELECT status, req_number FROM requisitions WHERE id = ? FOR UPDATE');
            $statusStmt->bind_param('i', $requisitionId);
            $statusStmt->execute();
            $req = $statusStmt->get_result()->fetch_assoc();
            $statusStmt->close();

            if (!$req || $req['status'] !== 'pending') {
                throw new RuntimeException('Only pending requisitions can be approved.');
            }

            $itemsStmt = db()->prepare('SELECT id, product_id, quantity_requested FROM requisition_items WHERE requisition_id = ?');
            $itemsStmt->bind_param('i', $requisitionId);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();
            $items = [];
            while ($row = $itemsResult->fetch_assoc()) {
                $items[] = $row;
            }
            $itemsStmt->close();

            if (count($items) === 0) {
                throw new RuntimeException('Requisition has no items.');
            }

            foreach ($items as $item) {
                $available = get_product_stock((int) $item['product_id']);
                if ($available < (float) $item['quantity_requested']) {
                    throw new RuntimeException('Insufficient stock for product ID ' . $item['product_id']);
                }
            }

            $updateItemStmt = db()->prepare('UPDATE requisition_items SET quantity_approved = ? WHERE id = ?');
            foreach ($items as $item) {
                $qtyApproved = (float) $item['quantity_requested'];
                $itemId = (int) $item['id'];
                $updateItemStmt->bind_param('di', $qtyApproved, $itemId);
                $updateItemStmt->execute();

                $remarks = 'Auto deduction for approved requisition ' . $req['req_number'];
                $ok = record_stock_movement(
                    (int) $item['product_id'],
                    'out',
                    $qtyApproved,
                    0,
                    'requisition',
                    $requisitionId,
                    $remarks,
                    $userId
                );

                if (!$ok) {
                    throw new RuntimeException('Failed to post stock movement during approval.');
                }
            }
            $updateItemStmt->close();

            $newStatus = 'approved';
            $approveStmt = db()->prepare('UPDATE requisitions SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?');
            $approveStmt->bind_param('sii', $newStatus, $userId, $requisitionId);
            $approveStmt->execute();
            $approveStmt->close();

            db()->commit();
            set_flash('success', 'Requisition approved and stock deducted.');
        } catch (Throwable $e) {
            db()->rollback();
            set_flash('danger', 'Approval failed: ' . $e->getMessage());
        }

        redirect('index.php?page=requisitions');
    }

    if ($action === 'reject') {
        require_roles(['admin']);
        $requisitionId = (int) ($_POST['requisition_id'] ?? 0);
        $reason = trim($_POST['rejection_reason'] ?? '');
        if ($requisitionId > 0) {
            $status = 'rejected';
            $stmt = db()->prepare('UPDATE requisitions SET status = ?, approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ? AND status = "pending"');
            $stmt->bind_param('sisi', $status, $userId, $reason, $requisitionId);
            $stmt->execute();
            $stmt->close();
            set_flash('warning', 'Requisition rejected.');
        }
        redirect('index.php?page=requisitions');
    }

    if ($action === 'deliver') {
        require_roles(['admin', 'warehouse']);
        $requisitionId = (int) ($_POST['requisition_id'] ?? 0);
        if ($requisitionId > 0) {
            $status = 'delivered';
            $stmt = db()->prepare('UPDATE requisitions SET status = ?, delivered_by = ?, delivered_at = NOW() WHERE id = ? AND status = "approved"');
            $stmt->bind_param('sii', $status, $userId, $requisitionId);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Requisition marked as delivered.');
        }
        redirect('index.php?page=requisitions');
    }
}

$products = db()->query('SELECT id, code, name FROM products WHERE is_active = 1 ORDER BY name ASC');

$where = '';
if ($role === 'requester') {
    $where = 'WHERE r.requester_id = ' . $userId;
}

$listSql = "SELECT r.*, u.full_name AS requester_name
            FROM requisitions r
            INNER JOIN users u ON u.id = r.requester_id
            {$where}
            ORDER BY r.id DESC
            LIMIT 30";
$requisitions = db()->query($listSql);

function status_badge(string $status): string
{
    $map = [
        'pending' => 'text-bg-warning',
        'approved' => 'text-bg-info',
        'rejected' => 'text-bg-danger',
        'delivered' => 'text-bg-success',
        'cancelled' => 'text-bg-secondary',
    ];

    return $map[$status] ?? 'text-bg-secondary';
}
?>

<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header fw-semibold">Create Requisition</div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-2">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose</label>
                            <textarea class="form-control" name="purpose" rows="2"></textarea>
                        </div>

                        <div id="requisition-items">
                            <div class="row g-2 req-item-row mb-2">
                                <div class="col-7">
                                    <select class="form-select" name="product_id[]" required>
                                        <option value="">Select product</option>
                                        <?php mysqli_data_seek($products, 0); while ($p = $products->fetch_assoc()): ?>
                                            <option value="<?= (int) $p['id'] ?>"><?= h($p['code']) ?> - <?= h($p['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="number" step="0.01" min="0.01" class="form-control" name="quantity_requested[]" placeholder="Qty" required>
                                </div>
                                <div class="col-2 d-flex">
                                    <button type="button" class="btn btn-outline-danger remove-item-row w-100">X</button>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="add-item-row">+ Add Item</button>
                        <button type="submit" class="btn btn-primary w-100">Submit Requisition</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header fw-semibold">Requisition Tracking</div>
                <div class="card-body table-responsive">
                    <input type="text" class="form-control form-control-sm mb-2" placeholder="Search table..." data-table-search="reqTable">
                    <table id="reqTable" class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th>Number</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($r = $requisitions->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($r['req_number']) ?></td>
                                <td><?= h($r['requester_name']) ?></td>
                                <td><?= h($r['department']) ?></td>
                                <td><span class="badge badge-status <?= status_badge($r['status']) ?>"><?= strtoupper(h($r['status'])) ?></span></td>
                                <td><?= h($r['created_at']) ?></td>
                                <td>
                                    <?php if ($r['status'] === 'pending' && has_role(['admin'])): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="requisition_id" value="<?= (int) $r['id'] ?>">
                                            <button class="btn btn-sm btn-outline-success">Approve</button>
                                        </form>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="requisition_id" value="<?= (int) $r['id'] ?>">
                                            <input type="hidden" name="rejection_reason" value="Rejected by admin">
                                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($r['status'] === 'approved' && has_role(['admin', 'warehouse'])): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="deliver">
                                            <input type="hidden" name="requisition_id" value="<?= (int) $r['id'] ?>">
                                            <button class="btn btn-sm btn-outline-primary">Mark Delivered</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
