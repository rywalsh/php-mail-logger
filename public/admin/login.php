<?php
require __DIR__ . '/../../../contact-src/bootstrap.php';
start_admin_session();
$auth = new Auth;
if ($auth->isLoggedIn()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if ($auth->login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: index.php'); exit;
    }
    $error = 'Invalid email or password.';
    usleep(500000);
}
?><!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin login</title>
<style>body{font:16px system-ui,sans-serif;background:#f5f5f7;display:flex;justify-content:center;padding:60px 16px}
form{background:#fff;padding:24px;border-radius:8px;width:100%;max-width:360px;box-shadow:0 1px 3px #0002}
input{width:100%;padding:10px;margin:4px 0 12px;box-sizing:border-box;border:1px solid #bbb;border-radius:6px;font:inherit}
button{width:100%;padding:10px;border:0;background:#2563eb;color:#fff;border-radius:6px;font:inherit}.e{color:#b91c1c}</style></head>
<body><form method="post"><h2>Admin login</h2>
<?php if ($error): ?><p class="e"><?= e($error) ?></p><?php endif; ?>
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label>Email<input type="email" name="email" required autofocus></label>
<label>Password<input type="password" name="password" required></label>
<button>Log in</button></form></body></html>
