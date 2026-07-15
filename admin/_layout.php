<?php require_once __DIR__ . '/../includes/helpers.php';
require_admin();
$BASE = base_url();
$active = $active ?? '';
?><!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= isset($page_title) ? e($page_title) . ' — Admin' : 'Admin' ?> — GGmode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= $BASE ?>/assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-body-tertiary">
    <nav class="navbar navbar-dark bg-dark px-3">
        <a class="navbar-brand" href="<?= $BASE ?>/admin/"><i
                class="bi bi-shield-lock-fill text-warning me-2"></i>GGmode
            Admin</a>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-light btn-sm" href="<?= $BASE ?>/" target="_blank"><i
                    class="bi bi-box-arrow-up-right"></i> View site</a>
            <a class="btn btn-warning btn-sm" href="<?= $BASE ?>/admin/logout.php">Logout</a>
        </div>
    </nav>
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-2 admin-sidebar p-3">
                <ul class="nav flex-column">
                    <?php $items = [['', 'Dashboard', 'speedometer2'], ['subjects', 'Subjects', 'collection'], ['lessons', 'Lessons', 'journal-text'], ['users', 'Users', 'people']];
                    foreach ($items as [$k, $lbl, $ic]):
                        $href = $BASE . '/admin/' . ($k ? $k . '.php' : 'index.php'); ?>
                        <li class="nav-item"><a class="nav-link <?= $active === $k ? 'active' : '' ?>"
                                href="<?= $href ?>"><i class="bi bi-<?= $ic ?> me-2"></i><?= $lbl ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </aside>
            <main class="col-md-10 py-4 px-4"></main>