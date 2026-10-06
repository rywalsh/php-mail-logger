<?php
// Finds the contact-src folder: inside this folder, next to it, or one level above the web root's subfolder.
// To force a location, define CONTACT_SRC before including this file or edit $candidates.
define('CONTACT_PUBLIC', __DIR__);
if (!defined('CONTACT_SRC')) {
    $candidates = [__DIR__ . '/contact-src', __DIR__ . '/../contact-src', __DIR__ . '/../../contact-src'];
    foreach ($candidates as $dir) {
        if (is_file($dir . '/bootstrap.php')) { define('CONTACT_SRC', realpath($dir)); break; }
    }
    if (!defined('CONTACT_SRC')) {
        http_response_code(500);
        error_log('contact-form: contact-src folder not found');
        exit('contact-src folder not found. See README (Deploying).');
    }
}
