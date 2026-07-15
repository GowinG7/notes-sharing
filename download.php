<?php
require_once __DIR__ . '/includes/helpers.php';

$type = $_GET['type'] ?? '';
$id = (int) ($_GET['id'] ?? 0);
$preview = isset($_GET['preview']) || ($_GET['download'] ?? '') !== '1';

if ($type === 'lesson' && $id > 0) {
    $st = db()->prepare('SELECT pdf_path FROM lessons WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch();
    if ($row && $row['pdf_path']) {
        $file = __DIR__ . '/' . ltrim($row['pdf_path'], '/');
        if (is_file($file)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: ' . ($preview ? 'inline' : 'attachment') . '; filename="' . basename($file) . '"');
            if (!$preview) {
                increment_lesson_reaction($id, 'downloads');
            }
            readfile($file);
            exit;
        }
    }
} elseif ($type === 'note' && $id > 0) {
    $st = db()->prepare('SELECT pdf_file FROM notes WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch();
    if ($row && $row['pdf_file']) {
        $file = __DIR__ . '/uploads/' . $row['pdf_file'];
        if (is_file($file)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: ' . ($preview ? 'inline' : 'attachment') . '; filename="' . basename($file) . '"');
            if (!$preview) {
                increment_note_reaction($id, 'downloads');
            }
            readfile($file);
            exit;
        }
    }
}

http_response_code(404);
exit('File not found');