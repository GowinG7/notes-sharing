<div class="offcanvas-lg offcanvas-start" id="sideNav">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Subjects</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sideNav"></button>
    </div>
    <div class="offcanvas-body p-lg-0">
        <nav class="sidebar">
            <a href="<?= BASE_URL ?>/" class="sidebar-home">Home</a>
            <div class="sidebar-heading">Subjects</div>
            <ul class="sidebar-list">
                <?php foreach ($subjects as $s): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/subject.php?slug=<?= e($s['slug']) ?>"><?= e($s['title']) ?></a>
                        <?php $notes = get_notes_by_subject($pdo, $s['id']);
                        if ($notes): ?>
                            <ul>
                                <?php foreach ($notes as $n): ?>
                                    <li><a href="<?= BASE_URL ?>/note.php?slug=<?= e($n['slug']) ?>"><?= e($n['title']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</div>