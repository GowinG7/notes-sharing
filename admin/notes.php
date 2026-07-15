<?php
require_once __DIR__ . '/includes/auth.php';
$active = 'notes';
$page_title = 'Notes';
$notes = $pdo->query("SELECT n.*, s.title AS subject_title FROM notes n JOIN subjects s ON s.id=n.subject_id ORDER BY n.created_at DESC")->fetchAll();
include __DIR__ . '/includes/header.php'; ?>
<div class="d-flex align-items-center mb-3">
    <h2 class="mb-0">Notes</h2>
    <a class="btn btn-dark ms-auto" href="<?= BASE_URL ?>/admin/note_edit.php">+ New note</a>
</div>
<div class="stat-card">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th>Title</th>
                <th>Subject</th>
                <th>Views</th>
                <th>Published</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notes as $n): ?>
                <tr>
                    <td><a href="<?= BASE_URL ?>/note.php?slug=<?= e($n['slug']) ?>" target="_blank">
                            <?= e($n['title']) ?>
                        </a></td>
                    <td class="text-muted small">
                        <?= e($n['subject_title']) ?>
                    </td>
                    <td>
                        <?= (int) $n['views'] ?>
                    </td>
                    <td>
                        <?= $n['published'] ? '<span class="badge bg-success">Live</span>' : '<span class="badge bg-secondary">Draft</span>' ?>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary"
                            href="<?= BASE_URL ?>/admin/note_edit.php?id=<?= $n['id'] ?>">Edit</a>
                        <form method="post" action="<?= BASE_URL ?>/admin/note_delete.php" style="display:inline"
                            onsubmit="return confirm('Delete this note?')">
                            <input type="hidden" name="id" value="<?= $n['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$notes): ?>
                <tr>
                    <td colspan="5" class="text-muted">No notes yet. Add your first one.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>