<?php $page_title = 'Home';
require __DIR__ . '/includes/header.php'; ?>
<div class="hero mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <div class="hero-kicker"><i class="bi bi-lightning-charge-fill"></i> Study library</div>
            <h1 class="display-5">Study material, notes &amp; video lessons.</h1>
            <p class="lead mb-0 opacity-75">Clean, focused study material with a calmer blue-and-white interface,
                organised by
                subject for quick learning at your own pace.</p>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="value"><?= count($subjects) ?></span>
                    <span class="label">Subjects available</span>
                </div>
                <div class="hero-stat">
                    <span class="value">PDF/Notes + video</span>
                    <span class="label">One place for both formats</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="hero-panel d-inline-block text-start text-lg-start">
                <p class="small text-uppercase fw-semibold text-primary mb-2">Start here</p>
                <a href="#subjects" class="btn btn-primary btn-lg w-100"><i class="bi bi-collection-play me-1"></i>
                    Browse subjects</a>
            </div>
        </div>
    </div>
</div>

<div class="section-title" id="subjects">
    <div>
        <span class="eyebrow">Library</span>
        <h2>Browse subjects</h2>
    </div>
</div>
<div class="row g-3">
    <?php foreach ($subjects as $s): ?>
        <div class="col-md-6 col-lg-4">
            <a class="text-decoration-none text-reset" href="<?= $BASE ?>/subject.php?slug=<?= e($s['slug']) ?>">
                <div class="card subject-card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon me-3"><i class="bi bi-journal-bookmark-fill"></i></div>
                            <h3 class="h5 mb-0"><?= e($s['title']) ?></h3>
                        </div>
                        <p class="text-muted small mb-2"><?= e($s['description']) ?></p>
                        <span class="badge text-bg-primary"><?= (int) $s['lesson_count'] ?>
                            lesson<?= $s['lesson_count'] == 1 ? '' : 's' ?></span>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>