<?php
require_roles(['admin']);

$roles = ['admin', 'supervisor', 'warehouse', 'requester'];
$departments = available_departments();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Invalid user ID.');
    redirect('index.php?page=users');
}

$stmt = db()->prepare('SELECT id, full_name, email, role, departments, is_active FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$editUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$editUser) {
    set_flash('danger', 'User not found.');
    redirect('index.php?page=users');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_user') {
    $fullName   = trim((string) ($_POST['full_name'] ?? ''));
    $email      = strtolower(trim((string) ($_POST['email'] ?? '')));
    $role       = (string) ($_POST['role'] ?? '');
    $department = trim((string) ($_POST['departments'] ?? ''));
    $isActive   = (int) ($_POST['is_active'] ?? 1);
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $currentUserId = (int) current_user()['id'];

    if ($id === $currentUserId && $isActive !== 1) {
        set_flash('danger', 'You cannot deactivate your own account.');
        redirect('index.php?page=user_edit&id=' . $id);
    }

    if (!in_array($role, $roles, true)) {
        set_flash('danger', 'Invalid role selected.');
        redirect('index.php?page=user_edit&id=' . $id);
    }

    $forcedDepartment = default_department_for_role($role);
    if ($forcedDepartment !== '') {
        $department = $forcedDepartment;
    }

    if (
        $fullName === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $department === '' ||
        !in_array($department, $departments, true)
    ) {
        set_flash('danger', 'Please fill in all required fields correctly.');
        redirect('index.php?page=user_edit&id=' . $id);
    }

    $existsStmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
    $existsStmt->bind_param('si', $email, $id);
    $existsStmt->execute();
    $exists = $existsStmt->get_result()->fetch_assoc();
    $existsStmt->close();

    if ($exists) {
        set_flash('danger', 'This email is already used by another account.');
        redirect('index.php?page=user_edit&id=' . $id);
    }

    if ($newPassword !== '') {
        if (strlen($newPassword) < 8) {
            set_flash('danger', 'New password must be at least 8 characters.');
            redirect('index.php?page=user_edit&id=' . $id);
        }
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, role = ?, departments = ?, is_active = ?, password_hash = ? WHERE id = ?');
        $stmt->bind_param('ssssiis', $fullName, $email, $role, $department, $isActive, $passwordHash, $id);
    } else {
        $stmt = db()->prepare('UPDATE users SET full_name = ?, email = ?, role = ?, departments = ?, is_active = ? WHERE id = ?');
        $stmt->bind_param('ssssii', $fullName, $email, $role, $department, $isActive, $id);
    }

    $stmt->execute();
    $stmt->close();

    set_flash('success', 'User updated successfully.');
    redirect('index.php?page=users');
}
?>

<div class="container-fluid">
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="index.php?page=users" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
        <h5 class="mb-0 fw-semibold"><i class="bi bi-person-gear me-2"></i>Edit User</h5>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header fw-semibold">Edit: <?= h($editUser['full_name']) ?></div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="id" value="<?= (int) $editUser['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="full_name" required maxlength="120"
                                value="<?= h($editUser['full_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required maxlength="120"
                                value="<?= h($editUser['email']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="roleSelect" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= h($r) ?>" <?= $editUser['role'] === $r ? 'selected' : '' ?>><?= ucfirst(h($r)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3" id="departmentGroup">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select" name="departments" id="departmentSelect">
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= h($dept) ?>" <?= $editUser['departments'] === $dept ? 'selected' : '' ?>>
                                        <?= h($dept) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="is_active"
                                <?= (int) $editUser['id'] === (int) current_user()['id'] ? 'disabled' : '' ?>>
                                <option value="1" <?= (int) $editUser['is_active'] === 1 ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= (int) $editUser['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <?php if ((int) $editUser['id'] === (int) current_user()['id']): ?>
                                <input type="hidden" name="is_active" value="1">
                                <div class="form-text">You cannot deactivate your own account.</div>
                            <?php endif; ?>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label">New Password <span class="text-muted small">(leave blank to keep current)</span></label>
                            <input type="password" class="form-control" name="new_password" minlength="8" autocomplete="new-password">
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="index.php?page=users" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var roleSelect = document.getElementById('roleSelect');
        var deptGroup = document.getElementById('departmentGroup');
        var deptSelect = document.getElementById('departmentSelect');

        var adminWarehouseDept = 'Departamento de Administracao e Financas';

        function updateDeptField() {
            var role = roleSelect.value;
            if (role === 'admin' || role === 'supervisor' || role === 'warehouse') {
                for (var i = 0; i < deptSelect.options.length; i++) {
                    if (deptSelect.options[i].value === adminWarehouseDept) {
                        deptSelect.selectedIndex = i;
                        break;
                    }
                }
                deptSelect.disabled = true;
            } else {
                deptSelect.disabled = false;
            }
        }

        roleSelect.addEventListener('change', updateDeptField);
        updateDeptField();
    })();
</script>