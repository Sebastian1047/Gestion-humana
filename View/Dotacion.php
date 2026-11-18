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
    <title>Gestor de Dotaciones – Jardines de San Nicolás</title>
    <link rel="stylesheet" href="../CSS/variables.css" />
    <link rel="stylesheet" href="../CSS/StyleDotacion.css" />
    <link rel="stylesheet" href="../CSS/NewDotStyle.css" />
    <link rel="stylesheet" href="../CSS/ResumenPeriodosDemo.css" />
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
                    <h2>Gestor de Dotaciones</h2>
                </div>

                <div class="containernuevoregistro acciones-dotacion">
                    <button class="nuevoregistro" id="btnNuevoRegistro">
                        + Agregar nuevo registro
                    </button>

                    <button class="btn-informe-dotaciones" id="btnInformeDotaciones">
                        📄 Informe Dotaciones
                    </button>

                    <a href="TallasExistencias.php" class="btn-informe-dotaciones" id="btnTallasExistencias">
                        📦 Tallas vs. Existencias
                    </a>
                </div>

                <!-- Resumen opcional de movimientos del formulario unificado de prueba. -->
                <details class="nd-resumen-inicio" id="ndResumenInicio" hidden>
                    <summary>🗂️ Dotaciones y adelantos registrados en esta DEMO <span id="ndCantidadDemo"></span></summary>
                    <div class="nd-resumen-tabla-wrap">
                        <table>
                            <thead><tr><th>Colaborador</th><th>Cuatrimestre</th><th>Estado</th><th>Movimientos</th><th>Acción</th></tr></thead>
                            <tbody id="ndResumenTabla"></tbody>
                        </table>
                    </div>
                    <p>Estos movimientos se guardaron en el navegador, no en la base de datos real. Para revisarlos o continuar un adelanto, entra a <strong>Agregar nuevo registro</strong>.</p>
                </details>
                <!-- Exportar masivo -->
                <div class="exportar-wrap">
                    <div class="form-group">
                        <label for="exportFechaInicio">Desde</label>
                        <input type="date" id="exportFechaInicio" class="input">
                    </div>
                    <div class="form-group">
                        <label for="exportFechaFin">Hasta</label>
                        <input type="date" id="exportFechaFin" class="input">
                    </div>
                    <button class="btn btn-accion btn-consultar" id="btnConsultarFechas" style="padding:9px 20px;font-size:0.85rem;">
                        🔍 Consultar
                    </button>

                    <button class="btn btn-accion btn-excel" id="btnExportarMasivo" style="padding:9px 20px;font-size:0.85rem;">
                        &#128196; Exportar Excel
                    </button>
                    <span id="exportMensaje" style="font-size:0.82rem;color:#718096;"></span>
                </div>

                <!-- ── BUSCADOR DE PERSONAS ── -->
                <div class="buscador-principal-wrap">
                    <input
                        type="text"
                        id="buscadorPersona"
                        placeholder="Buscar por nombre o código del colaborador..."
                        autocomplete="off" />
                    <span class="icono-buscar">&#128269;</span>
                </div>

                <!-- ── FILTROS + CONTADOR ── -->
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin:10px 0 4px;">

                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="display:flex;align-items:center;gap:8px;margin:0;font-size:0.9rem;color:#4a5568;cursor:pointer;">
                            <input type="checkbox" id="filtroSinNinguna" style="width:16px;height:16px;cursor:pointer;">
                            🚫 Sin ninguna firma
                        </label>

                        <label style="display:flex;align-items:center;gap:8px;margin:0;font-size:0.9rem;color:#4a5568;cursor:pointer;">
                            <input type="checkbox" id="filtroSinFirmaTrabajador" style="width:16px;height:16px;cursor:pointer;">
                            ⚠️ Sin firma de trabajador
                        </label>

                        <label style="display:flex;align-items:center;gap:8px;margin:0;font-size:0.9rem;color:#4a5568;cursor:pointer;">
                            <input type="checkbox" id="filtroSinFirmaEncargado" style="width:16px;height:16px;cursor:pointer;">
                            ⚠️ Sin firma de encargado
                        </label>

                    </div>

                    <div id="contadorPersonas" style="font-size:0.85rem;font-weight:600;color:#4a5568;text-align:right;line-height:1.5;">
                    </div>

                </div>

                <!-- ── VISTA 1: Grid de personas ── -->
                <div id="vistaPersonas">
                    <div id="personasGrid" class="personas-grid">
                        <div style="grid-column:1/-1;text-align:center;color:#718096;padding:32px 0;">
                            Cargando colaboradores...
                        </div>
                    </div>
                </div>

                <!-- ── VISTA 2: Registros de una persona ── -->
                <div id="vistaRegistros" style="display:none;">
                    <div class="panel-persona">
                        <div class="panel-persona-header">
                            <div>
                                <h3 id="ppNombre">—</h3>
                                <div class="ph-meta" id="ppMeta">—</div>
                            </div>
                            <button class="btn-volver-personas" id="btnVolverPersonas">
                                &#8592; Volver
                            </button>
                        </div>

                        <!-- Tabla de registros de la persona -->
                        <div class="tabla-container" style="border-radius:0;border:none;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Fecha Entrega</th>
                                        <th>Prendas</th>
                                        <th>Registrado por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaRegistros">
                                    <tr>
                                        <td colspan="4" style="text-align:center;color:#718096;padding:24px;">
                                            Cargando...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal confirmación eliminar -->
    <div class="modal-eliminar" id="modalEliminar">
        <div class="modal-eliminar-box">
            <div class="me-icon">🗑️</div>
            <h3>¿Eliminar registro?</h3>
            <p id="modalEliminarTexto">Este registro se moverá a la papelera.</p>
            <div class="me-btns">
                <button class="btn-confirmar-eliminar" id="btnConfirmarEliminar">Sí, eliminar</button>
                <button class="btn-cancelar-eliminar" id="btnCancelarEliminar">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Toast de notificaciones -->
    <div class="toast" id="toast"></div>

    <!-- ═══════════════════════════════════════
     MODAL INFORME DOTACIONES
═══════════════════════════════════════ -->
    <div class="modal-overlay" id="modalInformeDotaciones">

        <div class="modal-informe-box">

            <div class="modal-header-informe">
                <h2>📄 Informe de Dotaciones</h2>

                <button
                    class="cerrar-modal-informe"
                    id="cerrarModalInforme">

                    ✕
                </button>
            </div>

            <p class="subtitulo-informe">
                Filtra registros existentes y selecciona
                uno o varios colaboradores para generar
                el informe.
            </p>

            <div class="filtros-informe">

                <div class="form-group">
                    <label>
                        Buscar colaborador
                    </label>

                    <input
                        type="text"
                        id="buscarEmpleadoInforme"
                        class="input"
                        placeholder="Buscar por nombre o código..." />
                </div>

                <div class="form-group">
                    <label>
                        Fecha inicio
                    </label>

                    <input
                        type="date"
                        id="fechaInicioInforme"
                        class="input">
                </div>

                <div class="form-group">
                    <label>
                        Fecha fin
                    </label>

                    <input
                        type="date"
                        id="fechaFinInforme"
                        class="input">
                </div>

                <div class="form-group">
                    <button
                        class="btn-buscar-informe"
                        id="btnBuscarInforme">

                        Buscar
                    </button>
                </div>
            </div>

            <div class="acciones-tabla-informe">

                <div class="contador-seleccionados"
                    id="contadorSeleccionados">

                    0 colaboradores seleccionados
                </div>

                <div class="acciones-botones-tabla">

                    <button
                        class="btn-secundario"
                        id="btnSeleccionarTodos">

                        Seleccionar todos
                    </button>

                    <button
                        class="btn-secundario"
                        id="btnLimpiarSeleccion">

                        Limpiar
                    </button>

                </div>

            </div>

            <div class="tabla-informe-wrap">

                <table class="tabla-informe">

                    <thead>
                        <tr>
                            <th></th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Centro costos</th>
                            <th>Registros</th>
                        </tr>
                    </thead>

                    <tbody id="tablaInformeDotaciones">

                        <tr>
                            <td
                                colspan="5"
                                class="estado-vacio-tabla">

                                Selecciona filtros y pulsa buscar.
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <div class="footer-modal-informe">

                <button
                    class="btn-cancelar-modal"
                    id="cancelarInforme">

                    Cancelar
                </button>

                <button
                    class="btn-generar-pdf"
                    id="btnGenerarInforme"
                    disabled>

                    Generar PDF
                </button>

            </div>

        </div>
    </div>

    <script src="../JS/ui-common.js"></script>
    <script src="../JS/Script.js"></script>
    <script src="../JS/ResumenPeriodosDemo.js"></script>
    <script src="../JS/DotacionReportes.js"></script>
    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
