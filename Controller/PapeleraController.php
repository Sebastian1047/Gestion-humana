<?php
require_once __DIR__ . '/../Config/demo.php';
demoSession();
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !in_array($_GET['action'] ?? 'listar', ['listar', 'estadisticas'], true)) demoBlocked();
demoJson(['success' => true, 'entradas' => [], 'categorias' => [], 'estadisticas' => ['total_archivos' => 0, 'espacio_total' => 0, 'por_categoria' => []]]);
