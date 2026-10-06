<?php
require __DIR__ . '/../../../contact-src/bootstrap.php';
require __DIR__ . '/_layout.php';
start_admin_session();
(new Auth)->requireLogin();
$settings = new Settings;
$flash = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $recipient = trim($_POST['recipient_email'] ?? '');
    $confirm = trim($_POST['confirmation_message'] ?? '');
    $colors = [];
    foreach (Settings::COLOR_DEFAULTS as $k => $_) {
        $colors[$k] = trim($_POST[$k] ?? '');
    }
    $badColor = array_filter($colors, fn($v) => !preg_match('/^#[0-9a-f]{6}$/i', $v));
    if ($badColor) {
        $error = 'Colors must be hex values like #2563eb.';
    } elseif ($recipient !== '' && !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $error = 'Recipient must be a valid email address.';
    } elseif ($confirm === '' || mb_strlen($confirm) > 1000) {
        $error = 'Confirmation message is required (max 1000 characters).';
    } else {
        $settings->set('recipient_email', $recipient);
        $settings->set('confirmation_message', $confirm);
        foreach ($colors as $k => $v) $settings->set($k, strtolower($v));
        $flash = 'Settings saved.';
    }
}
$colors = $_SERVER['REQUEST_METHOD'] === 'POST' && $error ? array_merge($settings->colors(), array_filter($_POST, fn($v, $k) => isset(Settings::COLOR_DEFAULTS[$k]) && preg_match('/^#[0-9a-f]{6}$/i', $v), ARRAY_FILTER_USE_BOTH)) : $settings->colors();
$labels = ['color_primary' => 'Button / focus color', 'color_button_text' => 'Button text', 'color_text' => 'Text', 'color_background' => 'Background', 'color_border' => 'Field border'];
$recipient = $_POST['recipient_email'] ?? $settings->get('recipient_email');
$confirm = $_POST['confirmation_message'] ?? $settings->get('confirmation_message');
admin_header('Settings'); ?>
<h1>Settings</h1>
<?php if ($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="card">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<label for="recipient_email">Recipient email (leave blank to only save messages)</label>
<input id="recipient_email" name="recipient_email" type="email" value="<?= e($recipient) ?>">
<label for="confirmation_message">Confirmation message</label>
<textarea id="confirmation_message" name="confirmation_message" rows="4" required><?= e($confirm) ?></textarea>
<h3>Form colors</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px">
<?php foreach ($labels as $k => $label): ?>
<label><?= e($label) ?><input type="color" name="<?= e($k) ?>" value="<?= e($colors[$k]) ?>" style="height:44px;padding:2px"></label>
<?php endforeach; ?></div>
<button>Save</button></form>
<div class="card"><strong>Embed snippet</strong>
<pre>&lt;script src="<?= e((!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/')) ?>/form.js"&gt;&lt;/script&gt;</pre></div>
<?php admin_footer();
