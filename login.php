<?php require __DIR__ . '/includes/helpers.php';
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';
    $st = db()->prepare('SELECT * FROM users WHERE email=?');
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($pw, $u['password_hash'])) {
        $_SESSION['user_id'] = $u['id'];
        redirect(base_url() . '/');
    }
    $err = 'Invalid email or password.';
}
$page_title = 'Login';
require __DIR__ . '/includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Welcome back</h1>
                <?php if ($err): ?>
                    <div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email"
                            name="email" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input class="form-control"
                            type="password" name="password" required></div>
                    <button class="btn btn-dark w-100">Login</button>
                    <p class="text-center small mt-3 mb-0">No account? <a href="<?= $BASE ?>/register.php">Sign up</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>