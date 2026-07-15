<?php require_once __DIR__ . '/../includes/helpers.php';
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = db()->prepare('SELECT * FROM admins WHERE username=?');
    $st->execute([$_POST['username'] ?? '']);
    $a = $st->fetch();
    if ($a && password_verify($_POST['password'] ?? '', $a['password_hash'])) {
        $_SESSION['admin_id'] = $a['id'];
        redirect(base_url() . '/admin/');
    }
    $err = 'Invalid credentials.';
} ?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin login — GGmode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-dark text-white d-flex align-items-center" style="min-height:100vh">
    <div class="container" style="max-width:400px">
        <div class="text-center mb-4"><i class="bi bi-shield-lock-fill text-warning" style="font-size:3rem"></i>
            <h1 class="h4 mt-2">GGmode Admin</h1>
        </div>
        <div class="card">
            <div class="card-body p-4 text-dark">
                <?php if ($err): ?>
                    <div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <div class="mb-3"><label class="form-label">Username</label><input class="form-control"
                            name="username" required autofocus></div>
                    <div class="mb-3"><label class="form-label">Password</label><input class="form-control"
                            type="password" name="password" required></div>
                    <button class="btn btn-dark w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>