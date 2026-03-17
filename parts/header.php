<?php
$flash = get_flash();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<header class="header fixed-top d-flex align-items-center justify-content-between px-3">
    <a href="index.php?page=dashboard" class="brand d-flex align-items-center text-decoration-none">
        <i class="bi bi-box-seam me-2"></i>
        <span>LMS</span>
    </a>
    <div class="d-flex align-items-center gap-2">
        <span class="badge text-bg-light text-uppercase"><?= h($user['role']) ?></span>
        <span class="small text-light"><?= h($user['full_name']) ?></span>
    </div>
</header>

<?php if ($flash): ?>
<div class="container-fluid mt-header">
    <div class="alert alert-<?= h($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= h($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
