<?php $active = 'subjects';
require __DIR__ . '/_layout.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'create') {
        $t = trim($_POST['title']);
        $slug = slugify($_POST['slug'] ?: $t);
        db()->prepare('INSERT INTO subjects(slug,title,description,sort_order) VALUES(?,?,?,?)')->execute([$slug, $t, $_POST['description'] ?? '', (int) ($_POST['sort_order'] ?? 0)]);
    } elseif ($a === 'update') {
        db()->prepare('UPDATE subjects SET title=?,slug=?,description=?,sort_order=? WHERE id=?')->execute([$_POST['title'], slugify($_POST['slug']), $_POST['description'], (int) $_POST['sort_order'], (int) $_POST['id']]);
    } elseif ($a === 'delete') {
        db()->prepare('DELETE FROM subjects WHERE id=?')->execute([(int) $_POST['id']]);
    }
    redirect('subjects.php');
}
$rows = db()->query('SELECT s.*, (SELECT COUNT(*) FROM lessons l WHERE l.subject_id=s.id) lc FROM subjects s ORDER BY sort_order,title')->fetchAll();
$page_title = 'Subjects'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h1 class="h3 mb-1">Subjects</h1>
        <p class="text-muted mb-0 small">Create and organize your subject groups for lessons and notes.</p>
    </div>
    <button class="btn btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#newSubject"><i
            class="bi bi-plus-lg"></i> New
        subject</button>
</div>
<div class="card shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 admin-table">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Lessons</th>
                    <th>Order</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= e($r['title']) ?></div>
                            <div class="small text-muted"><?= e($r['description']) ?></div>
                        </td>
                        <td><code><?= e($r['slug']) ?></code></td>
                        <td><?= (int) $r['lc'] ?></td>
                        <td><?= (int) $r['sort_order'] ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                data-bs-target="#edit<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
                            <form method="post" class="d-inline" data-confirm="Delete this subject and ALL its lessons?">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden"
                                    name="action" value="delete"><input type="hidden" name="id"
                                    value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger"><i
                                        class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach ($rows as $r): ?>
    <div class="modal fade" id="edit<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" class="modal-content lesson-modal">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Edit subject</h5>
                        <div class="small text-muted">Update the subject title, slug and sort order.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input class="form-control form-control-lg" name="title" value="<?= e($r['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input class="form-control" name="slug" value="<?= e($r['slug']) ?>" required>
                        <div class="form-text">Used in the subject URL.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?= e($r['description']) ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Sort order</label>
                        <input class="form-control" type="number" name="sort_order" value="<?= (int) $r['sort_order'] ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-warning px-4">Save changes</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<div class="modal fade" id="newSubject">
    <div class="modal-dialog">
        <form method="post" class="modal-content lesson-modal">
            <div class="modal-header">
                <h5 class="modal-title">New subject</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="action"
                    value="create">
                <div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="title"
                        required>
                </div>
                <div class="mb-2"><label class="form-label">Slug (optional)</label><input class="form-control"
                        name="slug" placeholder="auto"></div>
                <div class="mb-2"><label class="form-label">Description</label><textarea class="form-control"
                        name="description" rows="2"></textarea></div>
                <div class="mb-2"><label class="form-label">Sort order</label><input class="form-control" type="number"
                        name="sort_order" value="0"></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button
                    class="btn btn-warning">Create</button></div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/_layout_end.php'; ?>