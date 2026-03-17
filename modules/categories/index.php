<?php
require_roles(['admin']);
$pageTitle = 'Categories';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $userId = (int) current_user()['id'];

    if ($name === '') {
        set_flash('danger', 'Category name is required.');
        redirect('index.php?page=categories');
    }

    if ($id > 0) {
        $stmt = db()->prepare('UPDATE categories SET name = ?, description = ?, updated_by = ? WHERE id = ?');
        $stmt->bind_param('ssii', $name, $description, $userId, $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Category updated successfully.');
    } else {
        $stmt = db()->prepare('INSERT INTO categories (name, description, created_by, updated_by) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssii', $name, $description, $userId, $userId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Category created successfully.');
    }

    redirect('index.php?page=categories');
}

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = db()->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Category deleted successfully.');
    redirect('index.php?page=categories');
}

$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = db()->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$categories = db()->query('SELECT * FROM categories ORDER BY name ASC');
?>

<div class="container-fluid">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header fw-semibold"><?= $edit ? 'Edit Category' : 'Add Category' ?></div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required value="<?= h($edit['name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= h($edit['description'] ?? '') ?></textarea>
                        </div>
                        <button class="btn btn-primary" type="submit">Save Category</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header fw-semibold">Category List</div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-sm align-middle">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $categories->fetch_assoc()): ?>
                            <tr>
                                <td><?= h($row['name']) ?></td>
                                <td><?= h($row['description']) ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-secondary" href="index.php?page=categories&edit=<?= (int) $row['id'] ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" data-confirm="Delete this category?" href="index.php?page=categories&delete=<?= (int) $row['id'] ?>">Delete</a>
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
