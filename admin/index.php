<?php $active = '';
require __DIR__ . '/_layout.php';
$traffic = traffic_totals();
$stats = [
    'Subjects' => db()->query('SELECT COUNT(*) c FROM subjects')->fetch()['c'],
    'Lessons' => db()->query('SELECT COUNT(*) c FROM lessons')->fetch()['c'],
    'Users' => db()->query('SELECT COUNT(*) c FROM users')->fetch()['c'],
    'Files' => db()->query('SELECT COUNT(*) c FROM files')->fetch()['c'],
];
$subjects = db()->query('SELECT s.*, (SELECT COUNT(*) FROM lessons l WHERE l.subject_id = s.id) AS lesson_count FROM subjects s ORDER BY sort_order, title')->fetchAll();
$recent = db()->query('SELECT l.*, s.title AS subj FROM lessons l JOIN subjects s ON s.id=l.subject_id ORDER BY l.created_at DESC LIMIT 6')->fetchAll();
$page_title = 'Dashboard'; ?>
<h1 class="h3 mb-4">Dashboard</h1>
<div class="row g-3 mb-4">
    <?php $stats = array_merge($stats, [
        'Views' => $traffic['views'],
        'Likes' => $traffic['likes'],
        'Downloads' => $traffic['downloads'],
    ]);
    $icons = ['Subjects' => 'collection', 'Lessons' => 'journal-text', 'Users' => 'people', 'Files' => 'folder2-open', 'Views' => 'eye', 'Likes' => 'heart', 'Downloads' => 'download'];
    foreach ($stats as $k => $v): ?>
        <div class="col-md-3 col-lg-2-4">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small text-uppercase"><?= $k ?></div>
                            <div class="h3 mb-0"><?= (int) $v ?></div>
                        </div>
                        <i class="bi bi-<?= $icons[$k] ?> text-warning" style="font-size:2rem"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 mb-0">Subjects overview</h2>
            <a href="subjects.php" class="btn btn-sm btn-outline-primary">Manage subjects</a>
        </div>
        <?php if (!$subjects): ?>
            <p class="text-muted mb-0">No subjects yet.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($subjects as $subject): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="border rounded-4 p-3 h-100 bg-white shadow-sm">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold mb-1"><?= e($subject['title']) ?></div>
                                    <div class="small text-muted mb-0"><?= e($subject['slug']) ?></div>
                                </div>
                                <span class="badge text-bg-primary rounded-pill"><?= (int) $subject['lesson_count'] ?>
                                    lessons</span>
                            </div>
                            <?php if (!empty($subject['description'])): ?>
                                <div class="small text-muted mt-2"><?= e($subject['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Recent lessons</h2>
        <?php if (!$recent): ?>
            <p class="text-muted mb-0">No lessons yet. <a href="lessons.php">Add one</a>.</p>
        <?php else: ?>
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Views</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($recent as $r): ?>
                        <tr>
                            <td><?= e($r['title']) ?></td>
                            <td><?= e($r['subj']) ?></td>
                            <td><?= (int) $r['views'] ?></td>
                            <td class="text-muted small"><?= e($r['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/_layout_end.php'; ?>