<?php
$roles = ['admin', 'warehouse', 'requester'];
$departments = available_departments();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'register') {
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $role = (string) ($_POST['role'] ?? 'requester');
    $department = trim((string) ($_POST['departments'] ?? ''));

    if (!in_array($role, $roles, true)) {
        set_flash('danger', 'Invalid role selected.');
        redirect('index.php?page=register');
    }

    $forcedDepartment = default_department_for_role($role);
    if ($forcedDepartment !== '') {
        $department = $forcedDepartment;
    }

    if (
        $fullName === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8 ||
        $password !== $confirmPassword ||
        $department === '' ||
        !in_array($department, $departments, true)
    ) {
        set_flash('danger', 'Please complete the form correctly. Password must be at least 8 characters.');
        redirect('index.php?page=register');
    }

    $existsStmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $existsStmt->bind_param('s', $email);
    $existsStmt->execute();
    $exists = $existsStmt->get_result()->fetch_assoc();
    $existsStmt->close();

    if ($exists) {
        set_flash('danger', 'Email is already registered.');
        redirect('index.php?page=register');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $insertSql = 'INSERT INTO users (full_name, email, password_hash, role, departments, is_active) VALUES (?, ?, ?, ?, ?, 1)';
    $insertStmt = db()->prepare($insertSql);
    $insertStmt->bind_param('sssss', $fullName, $email, $passwordHash, $role, $department);
    $insertStmt->execute();
    $insertStmt->close();

    set_flash('success', 'User registered successfully. Please sign in.');
    redirect('index.php?page=login');
}
?>

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <h3 class="mb-2">Register New User</h3>
                <p class="text-muted mb-4">Create a user account for the logistics system.</p>

                <form method="post" action="index.php?page=register" class="row g-3" novalidate>
                    <input type="hidden" name="action" value="register">

                    <div class="col-md-6">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="fullName" name="full_name" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="col-md-6">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" data-register-role required>
                            <option value="admin">Admin</option>
                            <option value="warehouse">Warehouse</option>
                            <option value="requester" selected>Requester</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="departments" class="form-label">Department</label>
                        <select class="form-select" id="departments" name="departments" data-register-department required>
                            <option value="">Select department</option>
                            <?php foreach ($departments as $departmentName): ?>
                                <option value="<?= h($departmentName) ?>"><?= h($departmentName) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Admin and Warehouse are automatically assigned to Departamento de Administracao e Financas.</div>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Register User</button>
                        <a href="index.php?page=login" class="btn btn-outline-secondary">Back to Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
