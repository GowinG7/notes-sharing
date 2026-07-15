<?php require_once __DIR__ . '/../../includes/helpers.php';
$active = $active ?? ''; ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title ?? 'Admin') ?> · <?= e(SITE_NAME) ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:wght@600&display=swap"
        rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>

<body class="admin-body">
    <div class="admin-topbar">
        <span class="brand"><?= e(SITE_NAME) ?> Admin</span>
        <span class="ms-auto">Signed in as <strong><?= e($_SESSION['admin_username'] ?? '') ?></strong></span>
        <a href="<?= BASE_URL ?>/" target="_blank">View site</a>
        <a href="<?= BASE_URL ?>/admin/logout.php">Log out</a>
    </div>
    <nav class="admin-nav">
        <a href="<?= BASE_URL ?>/admin/" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/subjects.php" class="<?= $active === 'subjects' ? 'active' : '' ?>">Subjects</a>
        <a href="<?= BASE_URL ?>/admin/notes.php" class="<?= $active === 'notes' ? 'active' : '' ?>">Notes</a>
    </nav>
    <div class="admin-content"></div>