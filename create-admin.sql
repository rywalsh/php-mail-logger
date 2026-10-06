-- Create (or reset) an admin without PHP CLI access.
-- 1. Generate a bcrypt hash on any machine with PHP (not the prod server):
--      php -r 'echo password_hash("YOUR-PASSWORD", PASSWORD_BCRYPT), "\n";'
--    (Any bcrypt generator producing a $2y$ hash also works.)
-- 2. Replace the two values below and run this in phpMyAdmin / the mysql client.
INSERT INTO admins (email, password_hash)
VALUES ('you@example.com', 'PASTE_BCRYPT_HASH_HERE')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);
