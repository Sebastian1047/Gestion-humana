<?php
require_once __DIR__ . '/../Config/demo.php';
class AuthController {
    public function login($username, $password) {
        if ($username !== 'DEMO' || $password !== 'DEMO') return 'Use DEMO como codigo y DEMO como contrasena. No use credenciales reales.';
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_regenerate_id(true);
        $_SESSION['user'] = ['success' => true, 'nombre' => 'Visitante Demo', 'codigoempleado' => 'DEMO', 'area' => 'SISTEMAS'];
        $_SESSION['login_username'] = 'DEMO';
        header('Location: ../View/dashboard.php');
        exit;
    }
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: ../View/login.php');
        exit;
    }
}
