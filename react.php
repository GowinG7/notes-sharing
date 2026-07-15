<?php
require_once __DIR__ . '/includes/helpers.php';

$type = $_POST['type'] ?? $_GET['type'] ?? '';
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$action = $_POST['action'] ?? $_GET['action'] ?? 'like';
$isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

$sendJson = function (array $payload, int $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
};

if (!in_array($action, ['like', 'unlike', 'download'], true) || $id <= 0) {
    if ($isAjax) {
        $sendJson(['ok' => false, 'message' => 'Bad request'], 400);
    }

    http_response_code(400);
    exit('Bad request');
}

if ($type === 'lesson') {
    if ($action === 'like') {
        $liked = record_lesson_like($id);
        if ($isAjax) {
            $count = (int) db()->query('SELECT likes FROM lessons WHERE id = ' . (int) $id)->fetchColumn();
            $sendJson(['ok' => true, 'liked' => true, 'likes' => $count]);
        }
    } elseif ($action === 'unlike') {
        $st = db()->prepare('DELETE FROM lesson_likes WHERE lesson_id = ? AND visitor_key = ?');
        $st->execute([$id, visitor_key()]);

        if ($st->rowCount() > 0) {
            db()->prepare('UPDATE lessons SET likes = CASE WHEN likes > 0 THEN likes - 1 ELSE 0 END WHERE id = ?')->execute([$id]);
            db()->prepare('UPDATE lesson_reactions SET likes = CASE WHEN likes > 0 THEN likes - 1 ELSE 0 END WHERE lesson_id = ?')->execute([$id]);
        }
        if ($isAjax) {
            $count = (int) db()->query('SELECT likes FROM lessons WHERE id = ' . (int) $id)->fetchColumn();
            $sendJson(['ok' => true, 'liked' => false, 'likes' => $count]);
        }
    } else {
        increment_lesson_reaction($id, 'downloads');
    }
} elseif ($type === 'note') {
    increment_note_reaction($id, $action === 'like' ? 'likes' : 'downloads');
} else {
    if ($isAjax) {
        $sendJson(['ok' => false, 'message' => 'Bad request'], 400);
    }

    http_response_code(400);
    exit('Bad request');
}

$redirect = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/');

if ($isAjax) {
    $count = (int) db()->query('SELECT likes FROM lessons WHERE id = ' . (int) $id)->fetchColumn();
    $sendJson(['ok' => true, 'liked' => $action !== 'unlike', 'likes' => $count]);
}

header('Location: ' . $redirect);
exit;