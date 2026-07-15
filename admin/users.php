<?php $active = 'users';
require __DIR__ . '/_layout.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'delete')
        db()->prepare('DELETE FROM users WHERE id=?')->execute([(int) $_POST['id']]);
    redirect('users.php');
}
$rows = db()->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$page_title = 'Users'; ?>
<h1 class="h3 mb-3">Registered users</h1>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No users yet.</td>
                    </tr><?php endif; ?>
                <?php foreach ($rows as $u): ?>
                    <tr>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td class="small text-muted"><?= e($u['created_at']) ?></td>
                        <td class="text-end">
                            <form method="post" class="d-inline" data-confirm="Delete user?"><input type="hidden"
                                    name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="action"
                                    value="delete"><input type="hidden" name="id" value="<?= $u['id'] ?>"><button
                                    class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/_layout_end.php'; ?>