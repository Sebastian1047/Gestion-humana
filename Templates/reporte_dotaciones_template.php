<?php

/** @var string $fechaInicio */
/** @var string $fechaFin */


require_once
    dirname(__DIR__) .
    '/Config/paths.php';

function obtenerPeriodo(
    string $fecha
): array {
    $mes = (int) date(
        'n',
        strtotime($fecha)
    );

    if (
        in_array(
            $mes,
            [4, 5, 6, 7]
        )
    ) {
        return [
            'nombre' => 'ABRIL',
            'rango'  => 'Abril - Julio'
        ];
    }

    if (
        in_array(
            $mes,
            [8, 9, 10, 11]
        )
    ) {
        return [
            'nombre' => 'AGOSTO',
            'rango'  => 'Agosto - Noviembre'
        ];
    }

    return [
        'nombre' => 'DICIEMBRE',
        'rango'  => 'Diciembre - Marzo'
    ];
}

function obtenerPrendas(
    array $registro
): array {
    $prendas = [];

    $campos = [
        'camisa' => 'Camibuso',
        'pantalon' => 'Pantalón',
        'zapatos_seguridad' => 'Zapatos Seguridad',
        'sudadera' => 'Sudadera',
        'jean' => 'Jean',
        'overol' => 'Overol',
        'zapatos' => 'Zapatos',
        'botas' => 'Botas',
        'camisa_oxford' => 'Camisa Oxford',
        'camisa_polo' => 'Camisa Polo'
    ];

    foreach (
        $campos as
        $campo => $nombre
    ) {

        if (
            !empty($registro[$campo])
        ) {

            $prendas[] = [
                'prenda' => $nombre,
                'talla' =>
                $registro[$campo .
                    '_talla'] ?? '-'
            ];
        }
    }

    return $prendas;
}

$logoPath =
    'file:///' .
    str_replace(
        '\\',
        '/',
        IMG_PATH .
            '/LOGO.png'
    );

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #780606;
            padding-bottom: 16px;
            margin-bottom: 25px;
        }

        .logo-wrap {
            display: inline-block;
            background: #111827;
            padding: 10px 16px;
            border-radius: 12px;
            margin-bottom: 14px;
        }

        .logo {
            width: 140px;
            display: block;
        }

        .titulo {
            font-size: 24px;
            font-weight: bold;
            color: #8b2816;
            margin-bottom: 6px;
        }

        .meta {
            color: #475569;
            font-size: 12px;
        }

        .persona {
            border: 1px solid #dbe2ea;
            border-radius: 12px;
            margin-bottom: 28px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .persona-header {
            background: #faf5f5;
            padding: 18px 22px;
            border-bottom: 1px solid #f0e6e6;
        }

        .persona-nombre {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #1e293b;
        }

        .persona-info {
            font-size: 12px;
            color: #475569;
        }

        .persona-body {
            padding: 20px;
        }

        .periodo {
            margin-bottom: 24px;
        }

        .periodo-title {
            background: #8b2816;
            color: white;
            padding: 10px 14px;
            border-radius: 8px;
            font-weight: bold;
            margin-bottom: 14px;
        }

        .tabla-dotaciones {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .tabla-dotaciones th {
            background: #f8fafc;
            border: 1px solid #dbe2ea;
            padding: 10px;
            text-align: left;
        }

        .tabla-dotaciones td {
            border: 1px solid #dbe2ea;
            padding: 9px;
        }

        .observaciones {
            margin-top: 10px;
            margin-bottom: 18px;
        }

        .obs-title {
            font-weight: bold;
        }

        .firma-wrap {
            text-align: center;
            margin-top: 30px;
            flex-direction: row;
        }

        .firmas-contenedor {
            width: 100%;
            display: table;
            table-layout: fixed;
            margin-top: 30px;
        }

        .firmas-contenedor .firma-wrap {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }

        .firma-img {
            width: 180px;
            max-height: 90px;
            object-fit: contain;
        }

        .linea-firma {
            width: 220px;
            border-top: 1px solid #000;
            margin: 12px auto 6px;
        }

        .firma-label {
            font-size: 11px;
            color: #475569;
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
            INFORME DE DOTACIONES
        </div>

        <div class="meta">
            <strong>Periodo:</strong>
            <?= date(
                'd/m/Y',
                strtotime($fechaInicio)
            ) ?>

            <?= date(
                'd/m/Y',
                strtotime($fechaFin)
            ) ?>
        </div>

        <div class="meta">
            <strong>Generado:</strong>
            <?= date('d/m/Y H:i') ?>
        </div>

    </div>

    <?php foreach (
        $personas as $persona
    ): ?>

        <div class="persona">

            <div class="persona-header">

                <div class="persona-nombre">
                    <?= htmlspecialchars(
                        $persona['nombre']
                    ) ?>
                </div>

                <div class="persona-info">

                    <strong>Código:</strong>
                    <?= $persona['codigo'] ?>

                    &nbsp;&nbsp;|

                    <strong>Documento:</strong>
                    <?= $persona['docId'] ?>

                    &nbsp;&nbsp;|

                    <strong>Centro costos:</strong>
                    <?= $persona['centro_costos'] ?? '-' ?>

                </div>

            </div>

            <div class="persona-body">

                <?php foreach (
                    $persona['periodos']
                    as $periodo
                ): ?>

                    <div class="periodo">

                        <div class="periodo-title">
                            PERIODO
                            <?= $periodo['nombre'] ?>
                            (<?= $periodo['rango'] ?>)
                        </div>

                        <table
                            class="tabla-dotaciones">

                            <thead>
                                <tr>
                                    <th width="25%">
                                        Fecha entrega
                                    </th>

                                    <th width="50%">
                                        Prenda
                                    </th>

                                    <th width="25%">
                                        Talla
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach (
                                    $periodo['registros']
                                    as $registro
                                ):

                                    $prendas =
                                        obtenerPrendas(
                                            $registro
                                        );

                                    foreach (
                                        $prendas
                                        as $prenda
                                    ):
                                ?>

                                        <tr>

                                            <td>
                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime(
                                                        $registro['fecha_entrega']
                                                    )
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= $prenda['prenda'] ?>
                                            </td>

                                            <td>
                                                <?= $prenda['talla'] ?>
                                            </td>

                                        </tr>

                                <?php
                                    endforeach;
                                endforeach;
                                ?>

                            </tbody>
                        </table>

                    </div>

                <?php endforeach; ?>

                <div class="observaciones">

                    <div class="obs-title">
                        Observaciones:
                    </div>

                    <?= !empty(trim(
                        $persona['observaciones'] ?? ''
                    ))
                        ? htmlspecialchars(
                            $persona['observaciones']
                        )
                        : 'Sin observaciones' ?>

                </div>

                <div class="firmas-contenedor">

                    <?php
                    if (!empty($persona['firma_trabajador'])) {

                        $firmaTrabajadorPath =
                            ROOT_PATH . '/' .
                            $persona['firma_trabajador'];

                        if (file_exists($firmaTrabajadorPath)) {

                            $firmaTrabajador =
                                'file:///' .
                                str_replace(
                                    '\\',
                                    '/',
                                    $firmaTrabajadorPath
                                );
                    ?>

                            <div class="firma-wrap">

                                <img
                                    src="<?= $firmaTrabajador ?>"
                                    class="firma-img">

                                <div class="linea-firma"></div>

                                <div class="firma-label">
                                    Firma trabajador
                                </div>

                            </div>

                    <?php
                        }
                    }
                    ?>

                    <?php
                    if (!empty($persona['firma_encargado'])) {

                        $firmaEncargadoPath =
                            ROOT_PATH . '/' .
                            $persona['firma_encargado'];

                        if (file_exists($firmaEncargadoPath)) {

                            $firmaEncargado =
                                'file:///' .
                                str_replace(
                                    '\\',
                                    '/',
                                    $firmaEncargadoPath
                                );
                    ?>

                            <div class="firma-wrap">

                                <img
                                    src="<?= $firmaEncargado ?>"
                                    class="firma-img">

                                <div class="linea-firma"></div>

                                <div class="firma-label">
                                    Firma encargado
                                </div>

                            </div>

                    <?php
                        }
                    }
                    ?>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

</body>

</html>