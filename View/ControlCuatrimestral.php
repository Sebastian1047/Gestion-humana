<?php require_once __DIR__ . '/../Config/demo.php'; ?>
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$pagina_actual = basename($_SERVER['PHP_SELF']);
$personas_demo = array_map(static function ($p) {
    return [
        'codigo' => (string)($p['codigo'] ?? ''),
        'nombre' => (string)($p['nombre_empleado'] ?? $p['nombre'] ?? ''),
        'area' => (string)($p['nombre_centro_costo'] ?? ''),
    ];
}, demoData()['personas'] ?? []);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../IMG/LOGOWINDOW.png">
    <title>Control cuatrimestral de dotaciones – DEMO</title>
    <link rel="stylesheet" href="../CSS/variables.css">
    <link rel="stylesheet" href="../CSS/StyleDotacion.css">
    <link rel="stylesheet" href="../CSS/NewDotStyle.css">
    <link rel="stylesheet" href="../CSS/ControlCuatrimestral.css">
</head>
<body>
<?php include __DIR__ . '/partials/hamburger.php'; ?>
<div class="container">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="main">
        <header class="header">
            <div style="display:flex;align-items:center;">
                <?php include __DIR__ . '/partials/icono_papelera_header.php'; ?>
                <div class="user"><span>👤 <?= htmlspecialchars($_SESSION['user']['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></span></div>
            </div>
        </header>
        <section class="content control-cuatri">
            <div class="top-bar">
                <h2>Control cuatrimestral de dotaciones</h2>
            </div>
            <p class="cc-intro">Una dotación principal por trabajador y periodo, con historial de entregas físicas, adelantos y prendas pendientes.</p>
            <div class="cc-demo-aviso" role="note"><strong>SIMULADOR DEMO · Datos ficticios.</strong>
                Los movimientos de esta pantalla se almacenan solo en este navegador, no en la base de datos ni en el módulo original. No ingreses información personal real.</div>

            <div class="cc-filtros card-seccion">
                <div class="cc-filtro">
                    <label for="ccPersona">Colaborador de prueba</label>
                    <select class="input" id="ccPersona"></select>
                </div>
                <div class="cc-filtro">
                    <label for="ccFecha">Fecha simulada del movimiento</label>
                    <input class="input" type="date" id="ccFecha">
                </div>
                <div class="cc-botones-sim">
                    <button id="ccCasoEjemplo" type="button" class="cc-btn cc-btn-outline">Cargar ejemplo abril → agosto 2027</button>
                    <button id="ccRestablecer" type="button" class="cc-btn cc-btn-texto">Reiniciar datos DEMO</button>
                </div>
            </div>

            <section class="card-seccion">
                <h3 class="seccion-titulo">Estado de las tres dotaciones del año <span id="ccAnio"></span></h3>
                <div id="ccResumen" class="cc-resumen" aria-live="polite"></div>
                <p class="cc-leyenda">Las entregas normales corresponden a abril, agosto y diciembre. Solo se puede adelantar el periodo siguiente. Un adelanto completo cubre totalmente ese periodo; uno parcial permite completar los artículos faltantes.</p>
            </section>

            <div class="cc-dos-columnas">
                <section class="card-seccion">
                    <h3 class="seccion-titulo">Registrar entrega o adelanto</h3>
                    <div class="cc-tipo" role="group" aria-label="Tipo de movimiento">
                        <label><input type="radio" name="ccTipo" value="normal" checked> Entrega normal</label>
                        <label><input type="radio" name="ccTipo" value="adelanto"> Adelanto de dotación</label>
                    </div>
                    <div id="ccDestino" class="cc-destino"></div>
                    <div id="ccAviso" role="status" aria-live="polite"></div>
                    <div class="cc-subtitulo">Prendas o artículos para este periodo</div>
                    <div id="ccPrendas" class="cc-prendas"></div>
                    <div class="cc-campo">
                        <label for="ccConcepto">Concepto / motivo de la entrega</label>
                        <textarea id="ccConcepto" rows="2" maxlength="400" placeholder="Ej. Cambio de labor; entrega del cuatrimestre..."></textarea>
                    </div>
                    <div class="cc-firmas">
                        <div class="cc-firma">
                            <label for="ccFirmaTrabajador">Firma de quien recibe <span class="cc-obligatorio">*</span></label>
                            <div class="canvas-wrap cc-canvas-wrap" id="ccFirmaTrabajadorWrap">
                                <canvas id="ccFirmaTrabajador" aria-label="Firma manuscrita del colaborador"></canvas>
                                <span class="canvas-hint">Firme aquí</span>
                            </div>
                            <div class="cc-firma-acciones">
                                <span id="ccEstadoTrabajador" class="firma-estado">Sin firma</span>
                                <button type="button" id="ccLimpiarTrabajador" class="cc-btn cc-btn-texto">Limpiar firma</button>
                            </div>
                        </div>
                        <div class="cc-firma" id="ccBloqueEncargado">
                            <label for="ccFirmaEncargado">Firma del encargado <span class="cc-obligatorio">*</span></label>
                            <div class="canvas-wrap cc-canvas-wrap" id="ccFirmaEncargadoWrap">
                                <canvas id="ccFirmaEncargado" aria-label="Firma manuscrita del encargado"></canvas>
                                <span class="canvas-hint">Firme aquí</span>
                            </div>
                            <div class="cc-firma-acciones">
                                <span id="ccEstadoEncargado" class="firma-estado">Sin firma</span>
                                <button type="button" id="ccLimpiarEncargado" class="cc-btn cc-btn-texto">Limpiar firma</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="ccGuardar" class="cc-btn cc-btn-primario">Registrar movimiento DEMO</button>
                    <div id="ccMensaje" role="status" aria-live="polite"></div>
                </section>

                <section class="card-seccion cc-historial-panel">
                    <h3 class="seccion-titulo">Registro principal e historial</h3>
                    <div id="ccHistorial" aria-live="polite"></div>
                </section>
            </div>
            <p class="cc-volver"><a href="Dotacion.php">← Volver al gestor de dotaciones</a></p>
        </section>
    </main>
</div>
<script>
    window.PERSONAS_DOTACION_DEMO = <?= json_encode($personas_demo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="../JS/ui-common.js"></script>
<script src="../JS/firma-canvas.js"></script>
<script src="../JS/ControlCuatrimestral.js"></script>
<?php include __DIR__ . '/partials/hamburger_script.php'; ?>
<?php include __DIR__ . '/partials/modal_logout.php'; ?>
<script src="../JS/demo.js"></script>
</body>
</html>
