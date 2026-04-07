<?php
require_roles(['admin']);

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Invalid user ID.');
    redirect('index.php?page=users');
}

$stmt = db()->prepare('SELECT id, full_name, email, role, departments, is_active, created_at, updated_at FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$viewUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$viewUser) {
    set_flash('danger', 'User not found.');
    redirect('index.php?page=users');
}
?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="index.php?page=users" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <h5 class="mb-0 fw-semibold"><i class="bi bi-person-circle me-2"></i>User Details</h5>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold">Profile Information</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Full Name</dt>
                        <dd class="col-sm-8"><?= h($viewUser['full_name']) ?></dd>

                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8"><?= h($viewUser['email']) ?></dd>

                        <dt class="col-sm-4 text-muted">Role</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-uppercase
                                <?= $viewUser['role'] === 'admin' ? 'text-bg-danger' : ($viewUser['role'] === 'warehouse' ? 'text-bg-warning' : 'text-bg-secondary') ?>">
                                <?= h($viewUser['role']) ?>
                            </span>
                        </dd>

                        <dt class="col-sm-4 text-muted">Department</dt>
                        <dd class="col-sm-8"><?= h($viewUser['departments']) ?></dd>

                        <dt class="col-sm-4 text-muted">Status</dt>
                        <dd class="col-sm-8">
                            <?php if ((int) $viewUser['is_active'] === 1): ?>
                                <span class="badge text-bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-4 text-muted">Registered</dt>
                        <dd class="col-sm-8"><?= h(date('d/m/Y H:i', strtotime($viewUser['created_at']))) ?></dd>

                        <dt class="col-sm-4 text-muted">Last Updated</dt>
                        <dd class="col-sm-8 mb-0"><?= h(date('d/m/Y H:i', strtotime($viewUser['updated_at']))) ?></dd>
                    </dl>
                </div>
                <div class="card-footer d-flex gap-2">
                    <a class="btn btn-sm btn-primary" href="index.php?page=user_edit&id=<?= (int) $viewUser['id'] ?>"><i class="bi bi-pencil me-1"></i>Edit</a>
                    <?php if ((int) $viewUser['id'] !== (int) current_user()['id']): ?>
                        <a class="btn btn-sm btn-outline-danger"
                            data-confirm="Delete user &quot;<?= h($viewUser['full_name']) ?>&quot;? This action cannot be undone."
                            href="index.php?page=users&delete=<?= (int) $viewUser['id'] ?>">
                            <i class="bi bi-trash me-1"></i>Delete
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>