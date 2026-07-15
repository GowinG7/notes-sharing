<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$slug = $_GET['slug'] ?? '';
$note = get_note($pdo, $slug);
if (!$note) {
    http_response_code(404);
    die('Note not found');
}
bump_views($pdo, $note['id']);
$page_title = $note['title'];
include __DIR__ . '/includes/header.php'; ?>
<nav class="crumbs">
    <a href="<?= BASE_URL ?>/">Home</a> /
    <a href="<?= BASE_URL ?>/subject.php?slug=<?= e($note['subject_slug']) ?>"><?= e($note['subject_title']) ?></a> /
    <?= e($note['title']) ?>
</nav>
<article class="note">
    <h1 class="page-title"><?= e($note['title']) ?></h1>
    <?php if ($note['summary']): ?>
        <p class="lead"><?= e($note['summary']) ?></p><?php endif; ?>

    <?php if ($note['video_url']): ?>
        <div class="video-wrap ratio ratio-16x9 my-4">
            <iframe src="<?= e($note['video_url']) ?>" allowfullscreen frameborder="0"></iframe>
        </div>
    <?php endif; ?>

    <div class="note-body"><?= $note['body'] /* trusted admin HTML */ ?></div>

    <div class="d-flex flex-wrap gap-2 mt-4">
        <a class="btn btn-outline-primary"
            href="<?= BASE_URL ?>/react.php?type=note&id=<?= (int) $note['id'] ?>&action=like"><i
                class="bi bi-heart me-1"></i> Like (<?= (int) ($note['likes'] ?? 0) ?>)</a>
        <?php if ($note['pdf_file']): ?>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/download.php?type=note&id=<?= (int) $note['id'] ?>"
                target="_blank">Download PDF (<?= (int) ($note['downloads'] ?? 0) ?>)</a>
        <?php endif; ?>
    </div>
</article>
<?php include __DIR__ . '/includes/footer.php'; ?>