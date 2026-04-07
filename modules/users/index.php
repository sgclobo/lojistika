<?php
require_roles(['admin']);

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $currentUserId = (int) current_user()['id'];

    if ($deleteId === $currentUserId) {
        set_flash('danger', 'You cannot delete your own account.');
        redirect('index.php?page=users');
    }

    $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'User deleted successfully.');
    redirect('index.php?page=users');
}

$users = db()->query('SELECT id, full_name, email, role, is_active, created_at FROM users ORDER BY full_name ASC');
?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-people me-2"></i>Users</h5>
        <?php if (can_register_users()): ?>
            <a class="btn btn-sm btn-primary" href="index.php?page=register">
                <i class="bi bi-person-plus me-1"></i>Create New User
            </a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">ID</th>
                        <th>Name</th>
                        <th>Username (Email)</th>
                        <th style="width:110px">Role</th>
                        <th style="width:100px">Status</th>
                        <th class="text-end" style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $found = false; while ($row = $users->fetch_assoc()): $found = true; ?>
                        <tr>
                            <td class="text-muted"><?= (int) $row['id'] ?></td>
                            <td><?= h($row['full_name']) ?></td>
                            <td><?= h($row['email']) ?></td>
                            <td>
                                <span class="badge text-uppercase
                                    <?= $row['role'] === 'admin' ? 'text-bg-danger' : ($row['role'] === 'warehouse' ? 'text-bg-warning' : 'text-bg-secondary') ?>">
                                    <?= h($row['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ((int) $row['is_active'] === 1): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="index.php?page=user_edit&id=<?= (int) $row['id'] ?>"
                                   title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if ((int) $row['id'] !== (int) current_user()['id']): ?>
                                    <a class="btn btn-sm btn-outline-danger"
                                       data-confirm="Delete user &quot;<?= h($row['full_name']) ?>&quot;? This action cannot be undone."
                                       href="index.php?page=users&delete=<?= (int) $row['id'] ?>"
                                       title="Delete"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if (!$found): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0 fw-semibold"><i class="bi bi-people me-2"></i>User Management</h5>
        <?php if (can_register_users()): ?>
            <a class="btn btn-sm btn-primary" href="index.php?page=register"><i class="bi bi-person-plus me-1"></i>Add User</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    while ($row = $users->fetch_assoc()):
                        $i++;
                    ?>
                        <tr>
                            <td class="text-muted"><?= $i ?></td>
                            <td><?= h($row['full_name']) ?></td>
                            <td><?= h($row['email']) ?></td>
                            <td><span class="badge text-uppercase
                            <?= $row['role'] === 'admin' ? 'text-bg-danger' : ($row['role'] === 'warehouse' ? 'text-bg-warning' : 'text-bg-secondary') ?>">
                                    <?= h($row['role']) ?>
                                </span></td>
                            <td class="text-truncate" style="max-width:200px"><?= h($row['departments']) ?></td>
                            <td>
                                <?php if ((int) $row['is_active'] === 1): ?>
                                    <span class="badge text-bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= h(date('d/m/Y', strtotime($row['created_at']))) ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-info" href="index.php?page=user_view&id=<?= (int) $row['id'] ?>" title="View"><i class="bi bi-eye"></i></a>
                                <a class="btn btn-sm btn-outline-secondary" href="index.php?page=user_edit&id=<?= (int) $row['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                                <?php if ((int) $row['id'] !== (int) current_user()['id']): ?>
                                    <a class="btn btn-sm btn-outline-danger"
                                        data-confirm="Delete user &quot;<?= h($row['full_name']) ?>&quot;? This action cannot be undone."
                                        href="index.php?page=users&delete=<?= (int) $row['id'] ?>"
                                        title="Delete"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($i === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>