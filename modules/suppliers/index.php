<?php
require_roles(['admin']);
$pageTitle = 'Suppliers';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $contactPerson = trim($_POST['contact_person'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $userId = (int) current_user()['id'];

    if ($name === '') {
        set_flash('danger', 'Supplier name is required.');
        redirect('index.php?page=suppliers');
    }

    if ($id > 0) {
        $sql = 'UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, email = ?, address = ?, updated_by = ? WHERE id = ?';
        $stmt = db()->prepare($sql);
        $stmt->bind_param('sssssii', $name, $contactPerson, $phone, $email, $address, $userId, $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Supplier updated.');
    } else {
        $sql = 'INSERT INTO suppliers (name, contact_person, phone, email, address, created_by, updated_by) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = db()->prepare($sql);
        $stmt->bind_param('sssssii', $name, $contactPerson, $phone, $email, $address, $userId, $userId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Supplier created.');
    }

    redirect('index.php?page=suppliers');
}

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = db()->prepare('DELETE FROM suppliers WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Supplier deleted.');
    redirect('index.php?page=suppliers');
}

$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = db()->prepare('SELECT * FROM suppliers WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$suppliers = db()->query('SELECT * FROM suppliers ORDER BY name ASC');
?>

<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-semibold"><?= $edit ? 'Edit Supplier' : 'Add Supplier' ?></div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
                        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" required value="<?= h($edit['name'] ?? '') ?>"></div>
                        <div class="mb-2"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" value="<?= h($edit['contact_person'] ?? '') ?>"></div>
                        <div class="mb-2"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= h($edit['phone'] ?? '') ?>"></div>
                        <div class="mb-2"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= h($edit['email'] ?? '') ?>"></div>
                        <div class="mb-3"><label class="form-label">Address</label><textarea class="form-control" rows="2" name="address"><?= h($edit['address'] ?? '') ?></textarea></div>
                        <button class="btn btn-primary" type="submit">Save Supplier</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">Suppliers</div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-sm align-middle">
                        <thead><tr><th>Name</th><th>Contact</th><th>Phone</th><th>Email</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php while ($row = $suppliers->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($row['name']) ?></td>
                                <td><?= h($row['contact_person']) ?></td>
                                <td><?= h($row['phone']) ?></td>
                                <td><?= h($row['email']) ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-secondary" href="index.php?page=suppliers&edit=<?= (int) $row['id'] ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" data-confirm="Delete this supplier?" href="index.php?page=suppliers&delete=<?= (int) $row['id'] ?>">Delete</a>
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
