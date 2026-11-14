<?php
require_once __DIR__ . '/../Config/demo.php';
demoSession();
$rows = demoData()['dotaciones'];
?><!doctype html><html lang="es"><meta charset="utf-8"><title>Informe de demostracion</title>
<style>body{font:16px Arial;max-width:1000px;margin:40px auto;padding:20px}td,th{padding:12px;text-align:left;border-bottom:1px solid #ddd}table{width:100%}.aviso{padding:16px;background:#fff1cf}@media print{button{display:none}}</style>
<h1>Informe de dotaciones: demostracion</h1><p class="aviso">Todos los registros son ficticios. Esta vista permite revisar un ejemplo de informe. No consulta servicios reales.</p>
<button onclick="window.print()">Imprimir / guardar como PDF</button><table><tr><th>Codigo</th><th>Persona</th><th>Area</th><th>Entrega</th></tr>
<?php foreach ($rows as $r): ?><tr><?php foreach (['codigo','nombre_empleado','nombre_centro_costo','fecha_entrega'] as $k): ?><td><?=htmlspecialchars($r[$k], ENT_QUOTES, 'UTF-8')?></td><?php endforeach; ?></tr><?php endforeach; ?></table></html>
