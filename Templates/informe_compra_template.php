<?php

/** @var array $faltantes  Filas ya filtradas (faltante > 0) y ordenadas, del módulo Tallas vs. Existencias */

require_once
    dirname(__DIR__) .
    '/config/paths.php';

$logoPath =
    'file:///' .
    str_replace(
        '\\',
        '/',
        IMG_PATH .
            '/LOGO.png'
    );

$totalFaltante = array_sum(array_column($faltantes, 'faltante'));

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            color: #1a202c;
            font-size: 9pt;
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #8b2816;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .logo-wrap {
            display: inline-block;
            background: #111827;
            padding: 10px 16px;
            border-radius: 12px;
            margin-bottom: 14px;
        }

        .logo {
            width: 130px;
            display: block;
        }

        .titulo {
            font-size: 17pt;
            font-weight: bold;
            color: #8b2816;
            margin-bottom: 6px;
        }

        .meta {
            color: #64748b;
            font-size: 9pt;
        }

        .resumen {
            display: table;
            width: 100%;
            margin-bottom: 18px;
        }

        .resumen-item {
            display: table-cell;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 16px;
            text-align: center;
        }

        .resumen-item .num {
            font-size: 15pt;
            font-weight: 800;
            color: #991b1b;
            display: block;
        }

        .resumen-item .label {
            font-size: 8pt;
            color: #7f1d1d;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        table.tabla-compra {
            width: 100%;
            border-collapse: collapse;
        }

        table.tabla-compra th {
            background: #8b2816;
            color: #fff;
            padding: 9px 10px;
            text-align: left;
            font-size: 8.5pt;
            border: 1px solid #8b2816;
        }

        table.tabla-compra td {
            padding: 8px 10px;
            border: 1px solid #dbe2ea;
            font-size: 9pt;
        }

        table.tabla-compra tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .col-faltante {
            text-align: center;
            font-weight: 800;
            color: #991b1b;
            background: #fee2e2 !important;
        }

        .col-num {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">

        <div class="logo-wrap">
            <img
                src="<?= $logoPath ?>"
                class="logo">
        </div>

        <div class="titulo">
            INFORME DE COMPRA — TALLAS FALTANTES
        </div>

        <div class="meta">
            <strong>Generado:</strong>
            <?= date('d/m/Y H:i') ?>
        </div>

    </div>

    <div class="resumen">
        <div class="resumen-item">
            <span class="num"><?= count($faltantes) ?></span>
            <span class="label">Combinaciones prenda + talla</span>
        </div>
        <div class="resumen-item">
            <span class="num"><?= $totalFaltante ?></span>
            <span class="label">Unidades totales a comprar</span>
        </div>
    </div>

    <table class="tabla-compra">

        <thead>
            <tr>
                <th>Prenda</th>
                <th>Talla</th>
                <th>Género</th>
                <th class="col-num">Total pedido</th>
                <th class="col-num">Existencia</th>
                <th class="col-num">Faltante a comprar</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($faltantes as $fila): ?>
                <tr>
                    <td><?= htmlspecialchars($fila['prenda'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['talla'] ?? '') ?></td>
                    <td><?= htmlspecialchars($fila['genero'] ?? '') ?></td>
                    <td class="col-num"><?= intval($fila['pedido'] ?? 0) ?></td>
                    <td class="col-num"><?= intval($fila['existencia'] ?? 0) ?></td>
                    <td class="col-faltante"><?= intval($fila['faltante'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>

</body>

</html>
