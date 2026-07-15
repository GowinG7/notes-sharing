<?php require __DIR__ . '/includes/helpers.php';
$subj = $_GET['subject'] ?? '';
$slug = $_GET['slug'] ?? '';
$st = db()->prepare('SELECT l.*, s.title AS subject_title, s.slug AS subject_slug FROM lessons l JOIN subjects s ON s.id=l.subject_id WHERE s.slug=? AND l.slug=?');
$st->execute([$subj, $slug]);
$lesson = $st->fetch();
if (!$lesson) {
    http_response_code(404);
    echo 'Lesson not found';
    exit;
}
db()->prepare('UPDATE lessons SET views=views+1 WHERE id=?')->execute([$lesson['id']]);
$liked = has_liked_lesson((int) $lesson['id']);
$page_title = $lesson['title'];
require __DIR__ . '/includes/header.php'; ?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= $BASE ?>/">Home</a></li>
        <li class="breadcrumb-item"><a
                href="<?= $BASE ?>/subject.php?slug=<?= e($lesson['subject_slug']) ?>"><?= e($lesson['subject_title']) ?></a>
        </li>
        <li class="breadcrumb-item active"><?= e($lesson['title']) ?></li>
    </ol>
</nav>
<article class="card shadow-sm">
    <div class="card-body p-4 p-lg-5">
        <h1 class="h2 mb-2"><?= e($lesson['title']) ?></h1>
        <p class="text-muted"><?= e($lesson['summary']) ?></p>
        <?php if ($lesson['video_url']):
            $embed = youtube_embed($lesson['video_url']); ?>
            <div class="ratio ratio-16x9 my-4 rounded overflow-hidden shadow-sm">
                <iframe src="<?= e($embed) ?>" title="Video" allowfullscreen loading="lazy"></iframe>
            </div>
        <?php endif; ?>
        <?php if ($lesson['pdf_path']): ?>
            <div class="ratio ratio-16x9 my-4 rounded overflow-hidden border shadow-sm bg-white">
                <iframe src="<?= $BASE ?>/download.php?type=lesson&id=<?= (int) $lesson['id'] ?>&preview=1"
                    title="PDF preview" loading="lazy"></iframe>
            </div>
        <?php endif; ?>
        <div class="lesson-body"><?= $lesson['body_html'] ?></div>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <?php if ($liked): ?>
                <button type="button" class="btn btn-outline-primary js-lesson-like" data-like-url="<?= $BASE ?>/react.php"
                    data-lesson-id="<?= (int) $lesson['id'] ?>" data-liked="1"
                    data-likes="<?= (int) ($lesson['likes'] ?? 0) ?>">
                    <i class="bi bi-heart-fill me-1"></i>
                    <span class="js-like-label">Unlike</span> (<span
                        class="js-like-count"><?= (int) ($lesson['likes'] ?? 0) ?></span>)
                </button>
            <?php else: ?>
                <button type="button" class="btn btn-outline-primary js-lesson-like" data-like-url="<?= $BASE ?>/react.php"
                    data-lesson-id="<?= (int) $lesson['id'] ?>" data-liked="0"
                    data-likes="<?= (int) ($lesson['likes'] ?? 0) ?>">
                    <i class="bi bi-heart me-1"></i>
                    <span class="js-like-label">Like</span> (<span
                        class="js-like-count"><?= (int) $lesson['likes'] ?></span>)
                </button>
            <?php endif; ?>
            <?php if ($lesson['pdf_path']): ?>
                <a class="btn btn-primary"
                    href="<?= $BASE ?>/download.php?type=lesson&id=<?= (int) $lesson['id'] ?>&download=1" download><i
                        class="bi bi-file-earmark-pdf me-1"></i> Download PDF (<?= (int) ($lesson['downloads'] ?? 0) ?>)</a>
            <?php endif; ?>
        </div>
    </div>
</article>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const likeButton = document.querySelector('.js-lesson-like');
        if (!likeButton) return;

        const renderButton = (liked, likes) => {
            likeButton.dataset.liked = liked ? '1' : '0';
            likeButton.dataset.likes = String(likes);
            likeButton.innerHTML = liked
                ? '<i class="bi bi-heart-fill me-1"></i><span class="js-like-label">Unlike</span> (<span class="js-like-count">' + likes + '</span>)'
                : '<i class="bi bi-heart me-1"></i><span class="js-like-label">Like</span> (<span class="js-like-count">' + likes + '</span>)';
        };

        likeButton.addEventListener('click', async () => {
            if (likeButton.dataset.busy === '1') return;
            likeButton.dataset.busy = '1';

            const lessonId = likeButton.dataset.lessonId;
            const currentlyLiked = likeButton.dataset.liked === '1';
            const action = currentlyLiked ? 'unlike' : 'like';

            try {
                const response = await fetch(likeButton.dataset.likeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new URLSearchParams({ type: 'lesson', id: lessonId, action }),
                });

                const data = await response.json();
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'Request failed');
                }

                renderButton(data.liked, data.likes);
            } catch (error) {
                console.error(error);
                alert('Could not update the like state. Please try again.');
            } finally {
                likeButton.dataset.busy = '0';
            }
        });
    });
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>