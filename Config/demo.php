<?php
const DEMO_MESSAGE = 'Copia de demostracion: guardar, eliminar, subir archivos y cambiar contrasenas esta deshabilitado. No se modifico informacion.';
function demoHeaders(): void {
    if (!headers_sent()) {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self' data:; connect-src 'self'; form-action 'self'; object-src 'none'; frame-src 'none'; base-uri 'self'");
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
    }
}
demoHeaders();
function demoData(): array {
    return json_decode(file_get_contents(__DIR__ . '/../data/demo.json'), true);
}
function demoJson($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function demoBlocked(): void {
    demoJson(['success' => false, 'demo' => true, 'error' => DEMO_MESSAGE, 'message' => DEMO_MESSAGE], 403);
}
function demoSession(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user'])) demoJson(['success' => false, 'error' => 'Inicie sesion con DEMO / DEMO'], 401);
}
function demoFilter(array $rows, string $term): array {
    $term = strtolower(trim($term));
    return array_values(array_filter($rows, static fn($row) => $term === '' || strpos(strtolower($row['codigo'] . ' ' . $row['nombre_empleado']), $term) !== false));
}
function demoRange(array $rows, string $start, string $end, string $field): array {
    return array_values(array_filter($rows, static fn($row) =>
        ($start === '' || $row[$field] >= $start) && ($end === '' || $row[$field] <= $end)));
}
