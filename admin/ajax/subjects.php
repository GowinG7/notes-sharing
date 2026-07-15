<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
$a = $_GET['a'] ?? '';
try {
    if ($a === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        if ($title === '')
            throw new Exception('Title required');
        $desc = trim($_POST['description'] ?? '');
        $sort = (int) ($_POST['sort_order'] ?? 0);
        if ($id) {
            $s = $pdo->prepare("UPDATE subjects SET title=?, description=?, sort_order=? WHERE id=?");
            $s->execute([$title, $desc, $sort, $id]);
        } else {
            $slug = slugify($title);
            // ensure unique
            $chk = $pdo->prepare("SELECT COUNT(*) FROM subjects WHERE slug LIKE ?");
            $chk->execute([$slug . '%']);
            if ($chk->fetchColumn() > 0)
                $slug .= '-' . time();
            $s = $pdo->prepare("INSERT INTO subjects (title, slug, description, sort_order) VALUES (?,?,?,?)");
            $s->execute([$title, $slug, $desc, $sort]);
        }
        echo json_encode(['ok' => true]);
        exit;
    }
    if ($a === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM subjects WHERE id=?")->execute([$id]);
        echo json_encode(['ok' => true]);
        exit;
    }
    throw new Exception('Unknown action');
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
