<?php $active = 'lessons';
require __DIR__ . '/_layout.php';
function save_pdf()
{
    if (empty($_FILES['pdf']['name']))
        return null;
    if ($_FILES['pdf']['error'] !== 0)
        return null;
    $dir = __DIR__ . '/../uploads/pdfs';
    if (!is_dir($dir))
        mkdir($dir, 0775, true);
    $name = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['pdf']['name']);
    move_uploaded_file($_FILES['pdf']['tmp_name'], "$dir/$name");
    return "uploads/pdfs/$name";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'create') {
        $pdf = save_pdf();
        db()->prepare('INSERT INTO lessons(subject_id,slug,title,summary,body_html,pdf_path,video_url) VALUES(?,?,?,?,?,?,?)')
            ->execute([(int) $_POST['subject_id'], slugify($_POST['slug'] ?: $_POST['title']), $_POST['title'], $_POST['summary'], $_POST['body_html'], $pdf, $_POST['video_url'] ?: null]);
    } elseif ($a === 'update') {
        $pdf = save_pdf();
        if ($pdf)
            db()->prepare('UPDATE lessons SET subject_id=?,slug=?,title=?,summary=?,body_html=?,pdf_path=?,video_url=? WHERE id=?')
                ->execute([(int) $_POST['subject_id'], slugify($_POST['slug']), $_POST['title'], $_POST['summary'], $_POST['body_html'], $pdf, $_POST['video_url'] ?: null, (int) $_POST['id']]);
        else
            db()->prepare('UPDATE lessons SET subject_id=?,slug=?,title=?,summary=?,body_html=?,video_url=? WHERE id=?')
                ->execute([(int) $_POST['subject_id'], slugify($_POST['slug']), $_POST['title'], $_POST['summary'], $_POST['body_html'], $_POST['video_url'] ?: null, (int) $_POST['id']]);
    } elseif ($a === 'delete') {
        db()->prepare('DELETE FROM lessons WHERE id=?')->execute([(int) $_POST['id']]);
    }
    redirect('lessons.php');
}
$subs = db()->query('SELECT * FROM subjects ORDER BY title')->fetchAll();
$rows = db()->query('SELECT l.*, s.title AS subj FROM lessons l JOIN subjects s ON s.id=l.subject_id ORDER BY l.created_at DESC')->fetchAll();
$page_title = 'Lessons'; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h1 class="h3 mb-1">Lessons</h1>
        <p class="text-muted mb-0 small">Create, edit and manage lesson content, PDFs and YouTube links.</p>
    </div>
    <button class="btn btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#newLesson"><i
            class="bi bi-plus-lg"></i> New
        lesson</button>
</div>
<div class="card shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 admin-table">
            <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Media</th>
                    <th>Views</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= e($r['title']) ?></div>
                            <div class="small text-muted"><?= e($r['slug']) ?></div>
                        </td>
                        <td><?= e($r['subj']) ?></td>
                        <td>
                            <?php if ($r['pdf_path']): ?><span class="badge text-bg-danger">PDF</span><?php endif; ?>
                            <?php if ($r['video_url']): ?><span class="badge text-bg-primary">Video</span><?php endif; ?>
                        </td>
                        <td><?= (int) $r['views'] ?></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                data-bs-target="#el<?= $r['id'] ?>"><i class="bi bi-pencil"></i></button>
                            <form method="post" class="d-inline" data-confirm="Delete this lesson?">
                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php foreach ($rows as $r): ?>
    <div class="modal fade" id="el<?= $r['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <form method="post" enctype="multipart/form-data" class="modal-content lesson-modal">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Edit lesson</h5>
                        <div class="small text-muted">Update content, media and PDF for this lesson.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label class="form-label">Title</label>
                            <input class="form-control form-control-lg" name="title" value="<?= e($r['title']) ?>" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Subject</label>
                            <select class="form-select form-select-lg" name="subject_id">
                                <?php foreach ($subs as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $r['subject_id'] ? 'selected' : '' ?>>
                                        <?= e($s['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input class="form-control" name="slug" value="<?= e($r['slug']) ?>">
                            <div class="form-text">URL-safe name used in the lesson link.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">YouTube URL</label>
                            <input class="form-control" name="video_url" value="<?= e($r['video_url']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Summary</label>
                            <input class="form-control" name="summary" value="<?= e($r['summary']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Body (HTML allowed)</label>
                            <textarea class="form-control" name="body_html" rows="8"><?= e($r['body_html']) ?></textarea>
                            <div class="form-text">Use trusted HTML only: paragraphs, lists, headings, links.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Replace PDF (optional)</label>
                            <input class="form-control" type="file" name="pdf" accept="application/pdf">
                            <?php if ($r['pdf_path']): ?>
                                <div class="small text-muted mt-2">Current: <?= e($r['pdf_path']) ?></div>
                            <?php endif; ?>
                        </div>
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

<div class="modal fade" id="newLesson">
    <div class="modal-dialog modal-lg">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New lesson</h5><button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input type="hidden" name="action"
                    value="create">
                <div class="row g-2">
                    <div class="col-md-8"><label class="form-label">Title</label><input class="form-control"
                            name="title" required></div>
                    <div class="col-md-4"><label class="form-label">Subject</label><select class="form-select"
                            name="subject_id"><?php foreach ($subs as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['title']) ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Slug (optional)</label><input class="form-control"
                            name="slug" placeholder="auto"></div>
                    <div class="col-md-6"><label class="form-label">YouTube URL</label><input class="form-control"
                            name="video_url" placeholder="https://youtube.com/watch?v=..."></div>
                    <div class="col-12"><label class="form-label">Summary</label><input class="form-control"
                            name="summary"></div>
                    <div class="col-12"><label class="form-label">Body (HTML allowed)</label><textarea
                            class="form-control" name="body_html" rows="6"></textarea></div>
                    <div class="col-12"><label class="form-label">PDF (optional)</label><input class="form-control"
                            type="file" name="pdf" accept="application/pdf"></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button
                    class="btn btn-warning">Create</button></div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/_layout_end.php'; ?>