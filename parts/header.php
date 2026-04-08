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
    <link href="<?= h(asset_url('assets/css/style.css')) ?>" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= h(asset_url('assets/img/aifaesa.png')) ?>">
</head>

<body>
    <header class="header fixed-top d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <?php if ($user): ?>
                <button id="sidebarToggle" class="btn btn-sm btn-sidebar-toggle" title="Toggle sidebar" aria-label="Toggle sidebar">
                    <i class="bi bi-list fs-5"></i>
                </button>
            <?php endif; ?>
            <a href="index.php?page=<?= $user ? 'dashboard' : 'login' ?>"
                class="brand d-flex align-items-center text-decoration-none">
                <img src="<?= h(asset_url('assets/img/aifaesa.png')) ?>" alt="AIFAESA Logo" class="brand-logo me-2">
                <span>Lojistika</span>
            </a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if ($user): ?>
                <span class="badge text-bg-light text-uppercase"><?= h($user['role']) ?></span>
                <a class="small text-light user-name-link" href="index.php?page=change_password" title="Change password"><?= h($user['full_name']) ?></a>
            <?php endif; ?>
            <?php
            $currentPage = $_GET['page'] ?? 'dashboard';
            $returnUrl   = urlencode('index.php?page=' . $currentPage);
            $locale      = get_locale();
            ?>
            <div class="d-flex gap-1">
                <a href="index.php?action=set_lang&lang=en&return=<?= $returnUrl ?>"
                    class="btn btn-xs lang-btn <?= $locale === 'en' ? 'lang-btn-active' : '' ?>"
                    title="English">EN</a>
                <a href="index.php?action=set_lang&lang=tet&return=<?= $returnUrl ?>"
                    class="btn btn-xs lang-btn <?= $locale === 'tet' ? 'lang-btn-active' : '' ?>"
                    title="Tetum">TET</a>
            </div>
            <?php if ($user): ?>
                <a class="btn btn-sm btn-outline-light" href="index.php?action=logout"><?= lang('sys.sign_out') ?></a>
            <?php else: ?>
                <span class="small text-light"><?= lang('sys.please_sign_in') ?></span>
            <?php endif; ?>
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