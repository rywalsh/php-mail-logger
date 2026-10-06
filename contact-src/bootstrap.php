<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/messages.php';
require_once __DIR__ . '/mailer.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

function start_admin_session() {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_start();
}

function csrf_token() {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Invalid CSRF token');
    }
}

// Once an admin exists, refuse to run until the setup page has been deleted.
function enforce_setup_removed() {
    if (defined('SETUP_PAGE') || !defined('CONTACT_PUBLIC') || !file_exists(CONTACT_PUBLIC . '/setup.php')) return;
    $db = Database::getInstance()->getConnection();
    try {
        $has = (int) $db->query("SELECT COUNT(*) c FROM admins")->fetch_assoc()['c'] > 0;
    } catch (mysqli_sql_exception $e) {
        return; // tables not created yet
    }
    if ($has) {
        http_response_code(503);
        exit('Setup is complete. Delete setup.php from the server to continue.');
    }
}
if (PHP_SAPI !== 'cli') enforce_setup_removed();
