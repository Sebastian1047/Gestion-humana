<?php require_once __DIR__ . '/../Config/demo.php'; ?>
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../View/login.php");
    exit();
}
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="../IMG/LOGOWINDOW.png" />
    <title>Tallas vs. Existencias – Jardines de San Nicolás</title>
    <link rel="stylesheet" href="../CSS/variables.css" />
    <link rel="stylesheet" href="../CSS/StyleDotacion.css" />
    <link rel="stylesheet" href="../CSS/NewDotStyle.css" />
</head>

<body>
    <?php include __DIR__ . '/partials/hamburger.php'; ?>

    <div class="container">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>


        <main class="main">
            <header class="header">
                <div style="display:flex;align-items:center;">
                    <?php include __DIR__ . '/partials/icono_papelera_header.php'; ?>
                    <div class="user">
                        <span>👤 <?php echo $_SESSION['user']['nombre'] ?? 'Usuario'; ?></span>
                    </div>
                </div>
            </header>

            <section class="content">
                <div class="top-bar">
                    <h2>Tallas vs. Existencias</h2>
                </div>

                <p style="color:#718096;font-size:0.9rem;margin-bottom:18px;">
                    Calcula automáticamente cuánto falta comprar por prenda, talla y género, comparando lo que
                    pidieron los colaboradores contra lo que hay disponible en bodega.
                    <a href="EquivalenciasBodega.php" style="color:var(--color-primario);font-weight:600;">Administrar equivalencias de bodega</a>
                </p>

                <div id="mensaje-resultado" class="mensaje-oculto"></div>

                <!-- Paso 1: exportar tallas -->
                <div class="card-seccion">
                    <h3 class="seccion-titulo">Paso 1 — Descargar el Excel de tallas pedidas</h3>
                    <p style="color:#718096;font-size:0.85rem;margin-bottom:14px;">
                        Genera el Excel con las tallas pedidas por todos los colaboradores en el rango de fechas
                        que elijas (mismo origen de datos que el Informe de Dotaciones).
                    </p>
                    <div class="exportar-wrap" style="justify-content:flex-start;">
                        <div class="form-group">
                            <label for="tallasFechaInicio">Desde</label>
                            <input type="date" id="tallasFechaInicio" class="input">
                        </div>
                        <div class="form-group">
                            <label for="tallasFechaFin">Hasta</label>
                            <input type="date" id="tallasFechaFin" class="input">
                        </div>
                        <button class="btn btn-accion btn-excel" id="btnDescargarTallas" style="padding:9px 20px;font-size:0.85rem;">
                            &#128196; Descargar Excel de Tallas
                        </button>
                    </div>
                </div>

                <!-- Paso 2: subir y calcular -->
                <div class="card-seccion">
                    <h3 class="seccion-titulo">Paso 2 — Subir los 2 archivos y calcular</h3>
                    <form id="form-comparar" novalidate>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="archivoTallas">Excel de tallas pedidas <small style="color:#a0aec0;">(el descargado en el Paso 1)</small></label>
                                <input type="file" id="archivoTallas" class="input" accept=".xlsx,.xls">
                            </div>
                            <div class="form-group">
                                <label for="archivoExistencias">Excel de existencias en bodega <small style="color:#a0aec0;">(el que envía almacén)</small></label>
                                <input type="file" id="archivoExistencias" class="input" accept=".xlsx,.xls">
                            </div>
                        </div>
                        <div class="botones-wrap" style="justify-content:flex-start;margin-top:16px;">
                            <button type="submit" class="btn btn-guardar" id="btnCalcular">&#10003; Calcular</button>
                        </div>
                    </form>
                </div>

                <!-- Resultado -->
                <div id="seccionResultado" style="display:none;">

                    <div class="card-seccion" id="cardSinMapear" style="display:none;">
                        <h3 class="seccion-titulo">⚠️ Códigos de bodega sin equivalencia configurada</h3>
                        <p style="color:#718096;font-size:0.85rem;margin-bottom:10px;">
                            Estos códigos aparecen en el archivo de existencias pero no están en la tabla de
                            equivalencias, así que no se contaron en el cálculo de abajo.
                            <a href="EquivalenciasBodega.php" style="color:var(--color-primario);font-weight:600;">Ir a configurarlos</a>
                        </p>
                        <div class="tabla-container" style="border-radius:0;border:none;">
                            <table>
                                <thead><tr><th>Código</th><th>Existencia</th></tr></thead>
                                <tbody id="tablaSinMapear"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-seccion">
                        <div class="resultado-header">
                            <h3 class="seccion-titulo" style="margin:0;">Resultado — qué falta comprar</h3>
                            <button type="button" class="btn-informe-compra" id="btnInformeCompra" disabled>
                                🛒 Generar Informe de Compra
                            </button>
                        </div>
                        <div class="tabla-container tabla-resultado-wrap" style="border-radius:0;border:none;max-height:600px;overflow-y:auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Prenda</th>
                                        <th>Talla</th>
                                        <th>Género</th>
                                        <th>Total pedido</th>
                                        <th>Existencia</th>
                                        <th>Faltante a comprar</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaResultado"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="../JS/ui-common.js"></script>
    <script src="../JS/Script.js"></script>
    <script src="../JS/TallasExistencias.js"></script>
    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
