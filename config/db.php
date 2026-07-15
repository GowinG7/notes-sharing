<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'ggmode');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_NAME', 'GGmode');

function base_url(): string
{
    static $base = null;

    if ($base !== null) {
        return $base;
    }

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = rtrim(dirname($script), '/');

    if ($base === '/' || $base === '.') {
        $base = '';
    }

    if (str_ends_with($base, '/admin')) {
        $base = substr($base, 0, -6);
    }

    return $base;
}

if (!defined('BASE_URL')) {
    define('BASE_URL', base_url());
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        die('Database Connection failed: ' . $e->getMessage());
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        slug VARCHAR(120) NOT NULL,
        title VARCHAR(200) NOT NULL,
        summary TEXT,
        body MEDIUMTEXT,
        video_url VARCHAR(255) DEFAULT NULL,
        pdf_file VARCHAR(255) DEFAULT NULL,
        published TINYINT(1) NOT NULL DEFAULT 1,
        views INT NOT NULL DEFAULT 0,
        likes INT NOT NULL DEFAULT 0,
        downloads INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_subject_slug (subject_id, slug),
        CONSTRAINT fk_notes_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lesson_reactions (
        lesson_id INT PRIMARY KEY,
        likes INT NOT NULL DEFAULT 0,
        downloads INT NOT NULL DEFAULT 0,
        CONSTRAINT fk_lesson_reactions_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("CREATE TABLE IF NOT EXISTS lesson_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        lesson_id INT NOT NULL,
        visitor_key VARCHAR(80) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_lesson_visitor (lesson_id, visitor_key),
        CONSTRAINT fk_lesson_likes_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $pdo->exec("ALTER TABLE lessons ADD COLUMN IF NOT EXISTS likes INT NOT NULL DEFAULT 0");
    $pdo->exec("ALTER TABLE lessons ADD COLUMN IF NOT EXISTS downloads INT NOT NULL DEFAULT 0");
    $pdo->exec("ALTER TABLE notes ADD COLUMN IF NOT EXISTS likes INT NOT NULL DEFAULT 0");
    $pdo->exec("ALTER TABLE notes ADD COLUMN IF NOT EXISTS downloads INT NOT NULL DEFAULT 0");

    $GLOBALS['pdo'] = $pdo;
    return $pdo;
}

$pdo = db();

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_check(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        die('Bad CSRF token');
    }
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $st = db()->prepare('SELECT id, name, email FROM users WHERE id = ?');
    $st->execute([(int) $_SESSION['user_id']]);
    $user = $st->fetch();

    return $user ?: null;
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect(BASE_URL . '/admin/login.php');
    }
}

function all_subjects(): array
{
    return db()->query('SELECT s.*, (SELECT COUNT(*) FROM lessons l WHERE l.subject_id = s.id) AS lesson_count FROM subjects s ORDER BY sort_order, title')->fetchAll();
}

function youtube_embed(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }

    if (str_contains($url, 'youtube.com/embed/')) {
        return $url;
    }

    if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    if (preg_match('~v=([A-Za-z0-9_-]{6,})~', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    return $url;
}

function traffic_totals(): array
{
    $lessonTotals = db()->query('SELECT COALESCE(SUM(views), 0) AS views, COALESCE(SUM(likes), 0) AS likes, COALESCE(SUM(downloads), 0) AS downloads FROM lessons')->fetch();
    $noteTotals = db()->query('SELECT COALESCE(SUM(views), 0) AS views, COALESCE(SUM(likes), 0) AS likes, COALESCE(SUM(downloads), 0) AS downloads FROM notes')->fetch();

    return [
        'views' => (int) $lessonTotals['views'] + (int) $noteTotals['views'],
        'likes' => (int) $lessonTotals['likes'] + (int) $noteTotals['likes'],
        'downloads' => (int) $lessonTotals['downloads'] + (int) $noteTotals['downloads'],
    ];
}

function has_liked_lesson(int $lessonId): bool
{
    $st = db()->prepare('SELECT 1 FROM lesson_likes WHERE lesson_id = ? AND visitor_key = ? LIMIT 1');
    $st->execute([$lessonId, visitor_key()]);
    return (bool) $st->fetchColumn();
}

function increment_lesson_reaction(int $lessonId, string $column): void
{
    if (!in_array($column, ['likes', 'downloads'], true)) {
        return;
    }

    db()->prepare('INSERT INTO lesson_reactions (lesson_id, likes, downloads) VALUES (?, 0, 0) ON DUPLICATE KEY UPDATE lesson_id = lesson_id')->execute([$lessonId]);
    db()->prepare("UPDATE lesson_reactions SET {$column} = {$column} + 1 WHERE lesson_id = ?")->execute([$lessonId]);
    db()->prepare("UPDATE lessons SET {$column} = {$column} + 1 WHERE id = ?")->execute([$lessonId]);
}

function record_lesson_like(int $lessonId): bool
{
    if (has_liked_lesson($lessonId)) {
        return false;
    }

    db()->prepare('INSERT INTO lesson_likes (lesson_id, visitor_key) VALUES (?, ?)')->execute([$lessonId, visitor_key()]);
    increment_lesson_reaction($lessonId, 'likes');
    return true;
}

function remove_lesson_like(int $lessonId): bool
{
    $st = db()->prepare('DELETE FROM lesson_likes WHERE lesson_id = ? AND visitor_key = ?');
    $st->execute([$lessonId, visitor_key()]);

    if ($st->rowCount() === 0) {
        return false;
    }

    db()->prepare('UPDATE lessons SET likes = CASE WHEN likes > 0 THEN likes - 1 ELSE 0 END WHERE id = ?')->execute([$lessonId]);
    db()->prepare('UPDATE lesson_reactions SET likes = CASE WHEN likes > 0 THEN likes - 1 ELSE 0 END WHERE lesson_id = ?')->execute([$lessonId]);
    return true;
}

function visitor_key(): string
{
    if (!empty($_SESSION['user_id'])) {
        return 'user:' . (int) $_SESSION['user_id'];
    }

    if (empty($_SESSION['visitor_key'])) {
        $_SESSION['visitor_key'] = bin2hex(random_bytes(16));
    }

    return 'anon:' . $_SESSION['visitor_key'];
}

function increment_note_reaction(int $noteId, string $column): void
{
    if (!in_array($column, ['likes', 'downloads'], true)) {
        return;
    }

    db()->prepare("UPDATE notes SET {$column} = {$column} + 1 WHERE id = ?")->execute([$noteId]);
}