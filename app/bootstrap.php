<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
header('Content-Type: text/html; charset=UTF-8');

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $path = __DIR__ . '/config.local.php';
    if (!is_file($path)) {
        http_response_code(503);
        exit('Setup needed: copy app/config.example.php to app/config.local.php and edit the database settings.');
    }
    $c = require $path;
    try {
        $pdo = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4",
            $c['username'], $c['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
    } catch (PDOException $ex) {
        error_log('Moviezzz database connection failed. SQLSTATE: ' . $ex->getCode());
        http_response_code(503);
        exit('Database connection failed. Check the running MySQL server, port, database name and app/config.local.php.');
    }
    return $pdo;
}
function query(string $sql, array $params = []): PDOStatement {
    $s = db()->prepare($sql);
    $s->execute($params);
    return $s;
}
function csrf(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function check_csrf(): void {
    if (!isset($_POST['csrf']) || !is_string($_POST['csrf']) || !hash_equals(csrf(), $_POST['csrf'])) {
        http_response_code(403); exit('Invalid form token. Reload the page and try again.');
    }
}
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $u = query('SELECT u.user_id, c.username, r.role_type FROM users u JOIN credentials c ON c.user_id=u.user_id JOIN roles r ON r.role_id=u.role_id WHERE u.user_id=? AND u.active=1', [$_SESSION['user_id']])->fetch();
    if (!$u) { unset($_SESSION['user_id']); return null; }
    return $u;
}
function require_user(): array {
    $u = current_user();
    if (!$u) { header('Location: login.php'); exit; }
    return $u;
}
function input(array $source, string $key): string {
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : '';
}
function page_start(string $title): void {
    $user = current_user();
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($title).' · Moviezzz</title><link rel="stylesheet" href="style.css"><body><header><a class="brand" href="index.php">MOVIEZZZ</a><nav><a href="index.php">Movies</a> ';
    if ($user) {
        echo '<a href="index.php?favorites=1">Favorites</a> <span>'.e($user['username']).'</span> <form class="inline" method="post" action="logout.php"><input type="hidden" name="csrf" value="'.e(csrf()).'"><button>Log out</button></form>';
    } else echo '<a href="login.php">Log in</a> <a href="register.php">Register</a>';
    echo '</nav></header><main><h1>'.e($title).'</h1>';
}
function page_end(): void {
    echo '</main><footer>Team N · Database starter · Sample catalog is fictional</footer></body></html>';
}
