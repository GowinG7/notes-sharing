<?php require_once __DIR__ . '/helpers.php';
$BASE = base_url();
$u = current_user();
$subjects = all_subjects(); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= isset($page_title) ? e($page_title) . ' — GGmode' : 'GGmode — Study notes & video lessons' ?></title>
    <meta name="description"
        content="GGmode — clean, focused study material: notes, PDFs and video lessons organised by subject.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= $BASE ?>/assets/css/style.css" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= $BASE ?>/"><i
                    class="bi bi-mortarboard-fill me-2 text-warning"></i>GGmode</a>
            <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span
                    class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= $BASE ?>/">Home</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Subjects</a>
                        <ul class="dropdown-menu">
                            <?php foreach ($subjects as $s): ?>
                                <li><a class="dropdown-item"
                                        href="<?= $BASE ?>/subject.php?slug=<?= e($s['slug']) ?>"><?= e($s['title']) ?>
                                        <span class="text-muted small">(<?= (int) $s['lesson_count'] ?>)</span></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="<?= $BASE ?>/about.php">About</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if ($u): ?>
                        <li class="nav-item"><span class="nav-link"><i
                                    class="bi bi-person-circle me-1"></i><?= e($u['name']) ?></span></li>
                        <li class="nav-item"><a class="nav-link" href="<?= $BASE ?>/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= $BASE ?>/login.php">Login</a></li>
                        <li class="nav-item"><a class="btn btn-light btn-sm ms-lg-2 mt-2 mt-lg-0 text-primary fw-semibold"
                                href="<?= $BASE ?>/register.php">Sign up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="flex-grow-1 py-4">
        <div class="container"></div>