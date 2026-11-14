<?php
require_once __DIR__ . '/../Config/demo.php';
demoSession();
$rows = demoFilter(demoData()['personas'], $_POST['busqueda'] ?? $_GET['busqueda'] ?? '');
$rows = demoRange($rows, $_POST['fechaInicio'] ?? $_GET['fechaInicio'] ?? '', $_POST['fechaFin'] ?? $_GET['fechaFin'] ?? '', 'ultima_entrega');
demoJson(['success' => true, 'message' => 'Datos ficticios de demostracion.', 'data' => $rows]);
