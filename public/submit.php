<?php
require __DIR__ . '/locate.php';
require CONTACT_SRC . '/bootstrap.php';

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && in_array($origin, Config::get('allowed_origins', []), true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Vary: Origin');
}

function respond($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') respond(204, []);

try {
    if ($method === 'GET') {
        header('Cache-Control: no-cache');
        respond(200, ['ok' => true, 'colors' => (new Settings)->colors()]);
    }
    if ($method !== 'POST') respond(405, ['ok' => false, 'error' => 'Method not allowed']);

    $isMultipart = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') === 0;
    $in = $isMultipart ? $_POST : (json_decode(file_get_contents('php://input'), true) ?: []);
    $clean = fn($k) => trim((string) ($in[$k] ?? ''));

    // Honeypot: pretend success to bots.
    if ($clean('website') !== '') respond(200, ['ok' => true, 'message' => (new Settings)->get('confirmation_message')]);

    $name = $clean('name'); $email = $clean('email');
    $subject = $clean('subject'); $message = $clean('message');
    $errors = [];
    if ($name === '' || mb_strlen($name) > 255) $errors['name'] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) $errors['email'] = 'Please enter a valid email.';
    if (mb_strlen($subject) > 255) $errors['subject'] = 'Subject is too long.';
    if ($message === '' || mb_strlen($message) > 5000) $errors['message'] = 'Please enter a message (max 5000 characters).';
    $uploads = [];
    if ($isMultipart && !empty($_FILES['files'])) {
        $f = $_FILES['files'];
        $maxFiles = (int) Config::get('upload_max_files', 5);
        $maxBytes = (int) Config::get('upload_max_bytes', 10 * 1024 * 1024);
        $allowed = Config::get('upload_allowed_extensions', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'zip']);
        $names = (array) $f['name'];
        for ($i = 0; $i < count($names); $i++) {
            $err = $f['error'][$i];
            if ($err === UPLOAD_ERR_NO_FILE) continue;
            if (count($uploads) >= $maxFiles) { $errors['files'] = "You can upload at most $maxFiles files."; break; }
            if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE || $f['size'][$i] > $maxBytes) {
                $errors['files'] = 'Each file must be under ' . round($maxBytes / 1048576, 1) . ' MB.'; break;
            }
            if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'][$i])) { $errors['files'] = 'File upload failed.'; break; }
            $orig = basename(str_replace('\\', '/', (string) $names[$i]));
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) { $errors['files'] = 'File type not allowed: .' . $ext; break; }
            $base = preg_replace('/[^A-Za-z0-9._-]+/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $base = trim(substr($base, 0, 80), '._') ?: 'file';
            $uploads[] = ['tmp' => $f['tmp_name'][$i], 'name' => "$base.$ext", 'size' => (int) $f['size'][$i]];
        }
    }
    if ($errors) respond(422, ['ok' => false, 'errors' => $errors]);

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $messages = new Messages;
    if ($ip && $messages->countRecentByIp($ip) >= 5) {
        respond(429, ['ok' => false, 'error' => 'Too many submissions. Please try again later.']);
    }

    $pageUrl = mb_substr($clean('page_url'), 0, 500);

    $files = [];
    if ($uploads) {
        $root = Config::get('upload_dir', CONTACT_PUBLIC . '/uploads');
        $baseUrl = rtrim(Config::get('upload_url', ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')
            . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/uploads'), '/');
        $dir = bin2hex(random_bytes(16));
        $target = "$root/$dir";
        if (!is_dir($target) && !mkdir($target, 0755, true)) throw new RuntimeException("Cannot create $target");
        // Never execute anything uploaded, even if an extension slips through.
        $ht = "$root/.htaccess";
        if (!is_file($ht)) file_put_contents($ht, "Options -Indexes -ExecCGI\nRemoveHandler .php .phtml .php3 .php5 .phar\nphp_flag engine off\n<FilesMatch \"\\.(php\\d?|phtml|phar)$\">\n  Require all denied\n</FilesMatch>\n");
        if (!is_file("$root/index.html")) file_put_contents("$root/index.html", '');
        foreach ($uploads as $u) {
            if (!move_uploaded_file($u['tmp'], "$target/{$u['name']}")) throw new RuntimeException('Cannot store upload');
            $files[] = ['name' => $u['name'], 'size' => $u['size'], 'url' => "$baseUrl/$dir/" . rawurlencode($u['name'])];
        }
    }

    if ($files) {
        $message .= "\n\nAttachments:\n" . implode("\n", array_map(fn($f) => $f['url'], $files));
    }
    $id = $messages->create($name, $email, $subject, $message, $ip, $pageUrl);
    if ($files) $message = substr($message, 0, strrpos($message, "\n\nAttachments:\n"));

    $settings = new Settings;
    $to = $settings->get('recipient_email');
    if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
        if (Mailer::sendContact($to, $name, $email, $subject, $message, $pageUrl, $ip, $files)) $messages->markSent($id);
    }

    respond(200, ['ok' => true, 'message' => $settings->get('confirmation_message')]);
} catch (Throwable $t) {
    error_log($t);
    respond(500, ['ok' => false, 'error' => 'Something went wrong. Please try again later.']);
}
