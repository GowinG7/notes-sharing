<?php require __DIR__ . '/includes/helpers.php';
$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';
    if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pw) < 6) {
        $err = 'Check your details (password min 6 chars).';
    } else {
        try {
            $st = db()->prepare('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)');
            $st->execute([$name, $email, password_hash($pw, PASSWORD_BCRYPT)]);
            $_SESSION['user_id'] = db()->lastInsertId();
            redirect(base_url() . '/');
        } catch (PDOException $ex) {
            $err = 'Email already registered.';
        }
    }
}
$page_title = 'Sign up';
require __DIR__ . '/includes/header.php'; ?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Create your account</h1>
                <?php if ($err): ?>
                    <div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name"
                            required>
                    </div>
                    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email"
                            name="email" required></div>
                    <div class="mb-3"><label class="form-label">Password</label><input class="form-control"
                            type="password" name="password" minlength="6" required></div>
                    <button class="btn btn-dark w-100">Sign up</button>
                    <p class="text-center small mt-3 mb-0">Already have an account? <a
                            href="<?= $BASE ?>/login.php">Login</a></p>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>