<?php
// First-time setup. DELETE THIS FILE after creating the admin; the app stays disabled until you do.
define('SETUP_PAGE', true);
require __DIR__ . '/locate.php';
if (!file_exists(CONTACT_SRC . '/settings.local.php')) {
    http_response_code(500);
    exit('Copy contact-src/settings.sample.php to contact-src/settings.local.php and fill in your database credentials first.');
}
require CONTACT_SRC . '/bootstrap.php';
start_admin_session();

$db = Database::getInstance()->getConnection();
$db->multi_query(file_get_contents(CONTACT_SRC . '/schema.sql'));
do { if ($r = $db->store_result()) $r->free(); } while ($db->more_results() && $db->next_result());

$exists = (int) $db->query("SELECT COUNT(*) c FROM admins")->fetch_assoc()['c'] > 0;
$done = false; $error = '';

if (!$exists && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Enter a valid email.';
    elseif (strlen($pass) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($pass !== ($_POST['password2'] ?? '')) $error = 'Passwords do not match.';
    else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO admins (email, password_hash)
            SELECT ?, ? FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM admins)");
        $stmt->bind_param('ss', $email, $hash);
        $stmt->execute();
        $done = $stmt->affected_rows === 1;
        $exists = true;
    }
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>First-time setup</title>
<style>body{font:16px system-ui,sans-serif;background:#f5f5f7;display:flex;justify-content:center;padding:60px 16px}
.box{background:#fff;padding:24px;border-radius:8px;width:100%;max-width:400px;box-shadow:0 1px 3px #0002}
input{width:100%;padding:10px;margin:4px 0 12px;box-sizing:border-box;border:1px solid #bbb;border-radius:6px;font:inherit}
button{width:100%;padding:10px;border:0;background:#2563eb;color:#fff;border-radius:6px;font:inherit}.e{color:#b91c1c}</style></head>
<body><div class="box"><h2>First-time setup</h2>
<?php if ($done): ?>
<p>Admin created.</p><p><strong>Now delete <code>public/setup.php</code> from the server.</strong> The app will not work until you do.</p>
<?php elseif ($exists): ?>
<p>An admin already exists. Delete <code>public/setup.php</code> from the server to continue.</p>
<?php else: ?>
<?php if ($error): ?><p class="e"><?= e($error) ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Admin email<input type="email" name="email" required autofocus></label>
<label>Password (8+ chars)<input type="password" name="password" required minlength="8"></label>
<label>Confirm password<input type="password" name="password2" required></label>
<button>Create admin</button></form>
<?php endif; ?></div></body></html>
