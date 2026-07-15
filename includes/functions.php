<?php
function e($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    return $text ?: 'item-' . time();
}

function get_subjects(PDO $pdo)
{
    return $pdo->query("SELECT * FROM subjects ORDER BY sort_order, title")->fetchAll();
}

function get_subject(PDO $pdo, $slug)
{
    $s = $pdo->prepare("SELECT * FROM subjects WHERE slug = ?");
    $s->execute([$slug]);
    return $s->fetch();
}

function get_notes_by_subject(PDO $pdo, $subject_id)
{
    $s = $pdo->prepare("SELECT * FROM notes WHERE subject_id = ? AND published = 1 ORDER BY created_at DESC");
    $s->execute([$subject_id]);
    return $s->fetchAll();
}

function get_note(PDO $pdo, $slug)
{
    $s = $pdo->prepare("SELECT n.*, s.title AS subject_title, s.slug AS subject_slug FROM notes n JOIN subjects s ON s.id = n.subject_id WHERE n.slug = ?");
    $s->execute([$slug]);
    return $s->fetch();
}

function bump_views(PDO $pdo, $id)
{
    $pdo->prepare("UPDATE notes SET views = views + 1 WHERE id = ?")->execute([$id]);
}
