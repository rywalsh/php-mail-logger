<?php
// Copy to settings.local.php and edit.
return [
    'db' => [
        'host' => 'localhost',
        'user' => 'mail_logger',
        'pass' => 'change-me',
        'name' => 'mail_logger',
    ],
    // Origins allowed to post the form cross-site (empty = same-origin only).
    'allowed_origins' => [],
    // Used as the From address for outgoing mail (should be on your domain).
    'mail_from' => 'no-reply@example.com',
    // Optional file uploads (defaults shown). upload_dir must be writable by PHP;
    // upload_url is the public URL of that folder (auto-detected if omitted).
    // 'upload_dir' => __DIR__ . '/../public/uploads',
    // 'upload_url' => 'https://example.com/uploads',
    // 'upload_max_files' => 5,
    // 'upload_max_bytes' => 10485760,
    // 'upload_allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'zip'],
];
