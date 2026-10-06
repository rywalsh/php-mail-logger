<?php
// Copy to config.php and edit.
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
];
