<?php
function admin_header($title) { ?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?></title>
<style>
body{font:16px/1.5 system-ui,sans-serif;margin:0;background:#f5f5f7;color:#222}
nav{background:#1f2937;padding:12px 20px;display:flex;gap:16px;flex-wrap:wrap}
nav a{color:#fff;text-decoration:none}
main{max-width:900px;margin:20px auto;padding:0 16px}
.card{background:#fff;padding:20px;border-radius:8px;margin-bottom:16px;box-shadow:0 1px 3px #0002}
input,textarea{width:100%;padding:10px;border:1px solid #bbb;border-radius:6px;font:inherit;box-sizing:border-box}
label{display:block;margin:12px 0 4px;font-weight:600}
button{padding:10px 20px;border:0;border-radius:6px;background:#2563eb;color:#fff;font:inherit;cursor:pointer;margin-top:12px}
button.danger{background:#b91c1c;margin:0}
.flash{background:#ecfdf5;border:1px solid #10b981;padding:10px;border-radius:6px;margin-bottom:16px}
.error{background:#fef2f2;border:1px solid #ef4444;padding:10px;border-radius:6px;margin-bottom:16px}
.meta{color:#666;font-size:14px}
pre{white-space:pre-wrap;word-break:break-word;font:inherit;margin:8px 0}
</style></head><body>
<nav><a href="/admin/index.php">Messages</a><a href="/admin/settings.php">Settings</a>
<form method="post" action="/admin/logout.php" style="margin-left:auto"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><button style="margin:0;padding:4px 12px">Log out</button></form></nav>
<main><?php }
function admin_footer() { echo '</main></body></html>'; }
