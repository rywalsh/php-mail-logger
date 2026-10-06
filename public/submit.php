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

    $in = json_decode(file_get_contents('php://input'), true) ?: [];
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
    if ($errors) respond(422, ['ok' => false, 'errors' => $errors]);

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $messages = new Messages;
    if ($ip && $messages->countRecentByIp($ip) >= 5) {
        respond(429, ['ok' => false, 'error' => 'Too many submissions. Please try again later.']);
    }

    $pageUrl = mb_substr($clean('page_url'), 0, 500);
    $id = $messages->create($name, $email, $subject, $message, $ip, $pageUrl);

    $settings = new Settings;
    $to = $settings->get('recipient_email');
    if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $strip = fn($s) => preg_replace('/[\r\n]+/', ' ', $s);
        $headers = [
            'From: ' . Config::get('mail_from'),
            'Reply-To: ' . $email,
            'Content-Type: text/plain; charset=UTF-8',
        ];
        $body = "Name: $name\nEmail: $email\nPage: $pageUrl\n\n$message\n";
        $subj = '=?UTF-8?B?' . base64_encode('[Contact] ' . ($strip($subject) ?: "Message from $name")) . '?=';
        if (@mail($to, $subj, $body, implode("\r\n", $headers))) $messages->markSent($id);
    }

    respond(200, ['ok' => true, 'message' => $settings->get('confirmation_message')]);
} catch (Throwable $t) {
    error_log($t);
    respond(500, ['ok' => false, 'error' => 'Something went wrong. Please try again later.']);
}
