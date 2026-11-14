<?php
require_once __DIR__ . '/authcontroller.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
$auth = new AuthController();
$error = $auth->login($_POST['username'] ?? '', $_POST['password'] ?? '');
header('Location: ../View/login.php?error=' . urlencode($error));
exit;
