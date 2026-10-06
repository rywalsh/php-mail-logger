<?php
require __DIR__ . '/../../src/bootstrap.php';
start_admin_session();
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_check(); (new Auth)->logout(); }
header('Location: /admin/login.php');
