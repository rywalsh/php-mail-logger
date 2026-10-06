<?php
require __DIR__ . '/../../../contact-src/bootstrap.php';
start_admin_session();
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); (new Auth)->logout(); }
header('Location: login.php');
