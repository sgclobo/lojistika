<?php
$user = current_user();
if (!$user) {
    set_flash('warning', 'Please sign in to continue.');
    redirect('index.php?page=login');
}

$userId = (int) $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        set_flash('danger', 'All password fields are required.');
        redirect('index.php?page=change_password');
    }

    if (strlen($newPassword) < 8) {
        set_flash('danger', 'New password must have at least 8 characters.');
        redirect('index.php?page=change_password');
    }

    if ($newPassword !== $confirmPassword) {
        set_flash('danger', 'New password and confirmation do not match.');
        redirect('index.php?page=change_password');
    }

    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($currentPassword, (string) $row['password_hash'])) {
        set_flash('danger', 'Current password is incorrect.');
        redirect('index.php?page=change_password');
    }

    if (password_verify($newPassword, (string) $row['password_hash'])) {
        set_flash('warning', 'New password must be different from current password.');
        redirect('index.php?page=change_password');
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $updateStmt->bind_param('si', $newHash, $userId);
    $updateStmt->execute();
    $updateStmt->close();

    set_flash('success', 'Password changed successfully.');
    redirect('index.php?page=dashboard');
}
?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header fw-semibold">Change Password</div>
                <div class="card-body">
                    <p class="text-muted mb-4">Update your account password for <?= h($user['full_name']) ?>.</p>

                    <form method="post" class="row g-3" novalidate>
                        <input type="hidden" name="action" value="change_password">

                        <div class="col-12">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password" autocomplete="current-password" required>
                        </div>

                        <div class="col-md-6">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" minlength="8" autocomplete="new-password" required>
                        </div>

                        <div class="col-md-6">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" minlength="8" autocomplete="new-password" required>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Password</button>
                            <a href="index.php?page=dashboard" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
