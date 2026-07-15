<?php
require_once __DIR__ . '/includes/auth.php';
$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $s = $pdo->prepare("SELECT pdf_file FROM notes WHERE id=?");
    $s->execute([$id]);
    $row = $s->fetch();
    if ($row && $row['pdf_file'])
        @unlink(__DIR__ . '/../uploads/' . $row['pdf_file']);
    $pdo->prepare("DELETE FROM notes WHERE id=?")->execute([$id]);
}
header('Location: ' . BASE_URL . '/admin/notes.php');
