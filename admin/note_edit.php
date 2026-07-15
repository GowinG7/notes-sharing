<?php
require_once __DIR__ . '/includes/auth.php';
$active = 'notes';
$id = (int) ($_GET['id'] ?? 0);
$note = ['id' => 0, 'subject_id' => '', 'title' => '', 'summary' => '', 'body' => '', 'video_url' => '', 'pdf_file' => '', 'published' => 1];
if ($id) {
    $s = $pdo->prepare("SELECT * FROM notes WHERE id=?");
    $s->execute([$id]);
    $note = $s->fetch() ?: $note;
}
$page_title = $id ? 'Edit note' : 'New note';
$subjects = get_subjects($pdo);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subject_id = (int) ($_POST['subject_id'] ?? 0);
    $summary = trim($_POST['summary'] ?? '');
    $body = $_POST['body'] ?? '';
    $video = trim($_POST['video_url'] ?? '');
    $published = isset($_POST['published']) ? 1 : 0;

    if ($title === '')
        $errors[] = 'Title required.';
    if (!$subject_id)
        $errors[] = 'Subject required.';

    // PDF upload
    $pdf_file = $note['pdf_file'];
    if (!empty($_FILES['pdf']['name'])) {
        if ($_FILES['pdf']['error'] === UPLOAD_ERR_OK && $_FILES['pdf']['size'] < 20 * 1024 * 1024) {
            $ext = strtolower(pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf'])) {
                $fname = 'note_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = __DIR__ . '/../uploads/' . $fname;
                if (move_uploaded_file($_FILES['pdf']['tmp_name'], $dest)) {
                    $pdf_file = $fname;
                } else
                    $errors[] = 'PDF upload failed.';
            } else
                $errors[] = 'Only PDF files allowed.';
        } else
            $errors[] = 'PDF too large or upload error.';
    }

    if (!$errors) {
        if ($id) {
            $s = $pdo->prepare("UPDATE notes SET subject_id=?, title=?, summary=?, body=?, video_url=?, pdf_file=?, published=? WHERE id=?");
            $s->execute([$subject_id, $title, $summary, $body, $video, $pdf_file, $published, $id]);
        } else {
            $slug = slugify($title);
            $chk = $pdo->prepare("SELECT COUNT(*) FROM notes WHERE slug LIKE ?");
            $chk->execute([$slug . '%']);
            if ($chk->fetchColumn() > 0)
                $slug .= '-' . time();
            $s = $pdo->prepare("INSERT INTO notes (subject_id, title, slug, summary, body, video_url, pdf_file, published) VALUES (?,?,?,?,?,?,?,?)");
            $s->execute([$subject_id, $title, $slug, $summary, $body, $video, $pdf_file, $published]);
            $id = $pdo->lastInsertId();
        }
        header('Location: ' . BASE_URL . '/admin/notes.php');
        exit;
    }
    $note = array_merge($note, $_POST);
    $note['pdf_file'] = $pdf_file;
}
include __DIR__ . '/includes/header.php'; ?>
<h2><?= $id ? 'Edit note' : 'New note' ?></h2>
<?php foreach ($errors as $er): ?>
    <div class="alert alert-danger"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" class="stat-card">
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" value="<?= e($note['title']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Subject</label>
            <select class="form-select" name="subject_id" required>
                <option value="">— choose —</option>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $s['id'] == $note['subject_id'] ? 'selected' : '' ?>>
                        <?= e($s['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Summary (one line)</label>
            <input class="form-control" name="summary" value="<?= e($note['summary']) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Video URL (YouTube embed, e.g. https://www.youtube.com/embed/XXXX)</label>
            <input class="form-control" name="video_url" value="<?= e($note['video_url']) ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Body</label>
            <textarea id="body" name="body" rows="14"><?= e($note['body']) ?></textarea>
        </div>
        <div class="col-md-8">
            <label class="form-label">PDF attachment (optional, max 20MB)</label>
            <input type="file" class="form-control" name="pdf" accept="application/pdf">
            <?php if ($note['pdf_file']): ?>
                <div class="small text-muted mt-1">Current: <a href="<?= BASE_URL ?>/uploads/<?= e($note['pdf_file']) ?>"
                        target="_blank"><?= e($note['pdf_file']) ?></a></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="published" id="pub" <?= $note['published'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="pub">Published</label>
            </div>
        </div>
        <div class="col-12">
            <button class="btn btn-dark">Save</button>
            <a href="<?= BASE_URL ?>/admin/notes.php" class="btn btn-link">Cancel</a>
        </div>
    </div>
</form>
<script>
    tinymce.init({
        selector: '#body',
        height: 400,
        plugins: 'lists link image code table',
        toolbar: 'undo redo | h2 h3 | bold italic | bullist numlist | link image | code',
        menubar: false
    });
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>