<?php
// Usage: php bin/create-admin.php admin@example.com 'password'
require __DIR__ . '/../contact-src/bootstrap.php';
[, $email, $pass] = $argv + [null, null, null];
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen((string) $pass) < 8) {
    fwrite(STDERR, "Usage: php bin/create-admin.php <email> <password (8+ chars)>\n");
    exit(1);
}
$db = Database::getInstance()->getConnection();
$hash = password_hash($pass, PASSWORD_BCRYPT);
$stmt = $db->prepare("INSERT INTO admins (email, password_hash) VALUES (?, ?)
    ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)");
$stmt->bind_param('ss', $email, $hash);
$stmt->execute();
echo "Admin $email saved.\n";
