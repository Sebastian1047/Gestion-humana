<?php
require_once __DIR__ . '/../Config/demo.php';
require_once __DIR__ . '/GestionHumanaClient.php';
demoSession();
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') demoBlocked();
if ($action === 'buscar_empleado') demoJson(demoFilter(demoData()['personas'], $_GET['q'] ?? ''));
switch ($action) {
    case 'listar_personas': $path = '/api/gestion-humana/dotaciones/personas?' . http_build_query(['busqueda' => $_GET['q'] ?? '']); break;
    case 'listar_por_fecha': $path = '/api/gestion-humana/dotaciones/personas-por-fecha?' . http_build_query($_GET); break;
    case 'listar_por_persona': $path = '/api/gestion-humana/dotaciones/por-persona/' . rawurlencode($_GET['codigo'] ?? ''); break;
    case 'obtener': $path = '/api/gestion-humana/dotaciones/' . intval($_GET['id'] ?? 0); break;
    default: demoBlocked();
}
$r = gestionHumanaRequest('GET', $path);
demoJson($r['body'], $r['status']);
