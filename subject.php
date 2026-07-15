<?php require __DIR__ . '/includes/helpers.php';
$slug = $_GET['slug'] ?? '';
$st = db()->prepare('SELECT * FROM subjects WHERE slug=?');
$st->execute([$slug]);
$subject = $st->fetch();
if (!$subject) {
    http_response_code(404);
    echo 'Subject not found';
    exit;
}
$st = db()->prepare('SELECT * FROM lessons WHERE subject_id=? ORDER BY created_at DESC');
$st->execute([$subject['id']]);
$lessons = $st->fetchAll();
$page_title = $subject['title'];
require __DIR__ . '/includes/header.php'; ?>
<div class="row g-4">
    <aside class="col-lg-3 sidebar">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small mb-2">Subjects</h6>
                <div class="list-group list-group-flush">
                    <?php foreach (all_subjects() as $s): ?>
                        <a class="list-group-item list-group-item-action <?= $s['id'] == $subject['id'] ? 'active' : '' ?>"
                            href="<?= $BASE ?>/subject.php?slug=<?= e($s['slug']) ?>"><?= e($s['title']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </aside>
    <section class="col-lg-9">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= $BASE ?>/">Home</a></li>
                <li class="breadcrumb-item active"><?= e($subject['title']) ?></li>
            </ol>
        </nav>
        <h1 class="h3"><?= e($subject['title']) ?></h1>
        <p class="text-muted"><?= e($subject['description']) ?></p>
        <?php if (!$lessons): ?>
            <div class="alert alert-light border">No lessons yet.</div>
        <?php else: ?>
            <div class="list-group shadow-sm">
                <?php foreach ($lessons as $l): ?>
                    <a href="<?= $BASE ?>/lesson.php?subject=<?= e($subject['slug']) ?>&slug=<?= e($l['slug']) ?>"
                        class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= e($l['title']) ?></div>
                                <div class="small text-muted"><?= e($l['summary']) ?></div>
                            </div>
                            <div class="text-muted small">
                                <?php if ($l['pdf_path']): ?><span class="badge text-bg-danger me-1"><i
                                            class="bi bi-file-earmark-pdf"></i>
                                        PDF</span><?php endif; ?>
                                <?php if ($l['video_url']): ?><span class="badge text-bg-primary me-1"><i
                                            class="bi bi-play-circle"></i>
                                        Video</span><?php endif; ?>
                                <i class="bi bi-chevron-right"></i>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>