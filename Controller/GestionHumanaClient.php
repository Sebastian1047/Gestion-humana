<?php
require_once __DIR__ . '/../Config/demo.php';
function gestionHumanaRequest(string $method, string $path, $body = null): array {
    if (strtoupper($method) !== 'GET') return ['status' => 403, 'body' => ['success' => false, 'message' => DEMO_MESSAGE], 'curl_error' => null];
    $data = demoData();
    $route = parse_url($path, PHP_URL_PATH);
    parse_str(parse_url($path, PHP_URL_QUERY) ?? '', $q);
    $result = null;
    if (str_ends_with($route, '/personas')) $result = demoFilter($data['personas'], $q['busqueda'] ?? '');
    elseif (str_ends_with($route, '/personas-por-fecha')) $result = demoRange($data['personas'], $q['fechaInicio'] ?? '', $q['fechaFin'] ?? '', 'ultima_entrega');
    elseif (preg_match('~/por-persona/([^/]+)$~', $route, $m)) $result = array_values(array_filter($data['dotaciones'], static fn($r) => $r['codigo'] === rawurldecode($m[1])));
    elseif (preg_match('~/dotaciones/(\d+)$~', $route, $m)) {
        foreach ($data['dotaciones'] as $r) if ($r['id'] === intval($m[1])) $result = $r;
    }
    return ['status' => $result === null ? 404 : 200, 'body' => $result ?? ['error' => 'Registro no encontrado'], 'curl_error' => null];
}
