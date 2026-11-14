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
    <title>Papelera – Jardines de San Nicolás</title>
    <link rel="stylesheet" href="../CSS/variables.css" />
    <link rel="stylesheet" href="../CSS/StyleDotacion.css" />
    <link rel="stylesheet" href="../CSS/NewDotStyle.css" />
    <link rel="stylesheet" href="../CSS/Papelera.css?v=<?= filemtime(__DIR__ . '/../CSS/Papelera.css') ?>" />
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

                <div class="hero-papelera">
                    <div class="hero-papelera-texto">
                        <h1>🗑️ Papelera</h1>
                        <h2>Documentos eliminados</h2>
                        <p>
                            Cuando alguien elimina un documento desde cualquier módulo, queda guardado
                            aquí antes de desaparecer de verdad. Puedes restaurarlo a su lugar original
                            o purgarlo definitivamente.
                        </p>
                    </div>
                    <div class="hero-papelera-botones">
                        <button class="btn-vaciar-papelera" id="btnVaciarPapelera">
                            <span>🔥</span> Vaciar papelera
                        </button>
                    </div>
                </div>

                <div class="stats-papelera">
                    <div class="stat-card-papelera">
                        <div class="stat-icono-papelera stat-icono-total">🗃️</div>
                        <div>
                            <p class="stat-numero-papelera" id="statTotalArchivos">–</p>
                            <p class="stat-label-papelera">Archivos en papelera</p>
                        </div>
                    </div>
                    <div class="stat-card-papelera">
                        <div class="stat-icono-papelera stat-icono-espacio">💾</div>
                        <div>
                            <p class="stat-numero-papelera" id="statEspacioTotal">–</p>
                            <p class="stat-label-papelera">Espacio ocupado</p>
                        </div>
                    </div>
                    <div class="stat-card-papelera">
                        <div class="stat-icono-papelera stat-icono-categoria">📂</div>
                        <div>
                            <p class="stat-numero-papelera" id="statCategoriaTop">–</p>
                            <p class="stat-label-papelera">Módulo con más eliminados</p>
                        </div>
                    </div>
                </div>

                <div class="toolbar-papelera">
                    <input type="text" id="buscadorPapelera" placeholder="Buscar por archivo, módulo o quién lo eliminó..." autocomplete="off">

                    <select id="filtroCategoriaPapelera">
                        <option value="">Todos los módulos</option>
                    </select>

                    <input type="date" id="filtroDesdePapelera" title="Desde">
                    <input type="date" id="filtroHastaPapelera" title="Hasta">

                    <button class="btn-limpiar-filtros-papelera" id="btnLimpiarFiltrosPapelera">Limpiar filtros</button>
                </div>

                <div class="tabla-wrap-papelera">
                    <table class="tabla-papelera">
                        <thead>
                            <tr>
                                <th>Módulo</th>
                                <th>Archivo</th>
                                <th>Eliminado por</th>
                                <th>Fecha</th>
                                <th>Tamaño</th>
                                <th class="col-acciones-papelera">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPapelera">
                        </tbody>
                    </table>
                </div>

                <div id="estadoVacioPapelera" class="estado-vacio-papelera" style="display:none;">
                    <div class="estado-vacio-icono-papelera">✨</div>
                    <h3>La papelera está vacía</h3>
                    <p>Los documentos que se eliminen desde cualquier módulo aparecerán aquí.</p>
                </div>

                <div id="estadoCargandoPapelera" class="estado-cargando-papelera">
                    <div class="spinner-papelera"></div>
                    <p>Cargando...</p>
                </div>

            </section>
        </main>
    </div>

    <!-- ══ MODAL: confirmación (restaurar / purgar / vaciar) ══ -->
    <div class="modal-overlay-papelera" id="modalConfirmarPapelera">
        <div class="modal-box-papelera">
            <div class="modal-box-icono-papelera" id="confirmarPapeleraIcono">⚠️</div>
            <h2 class="modal-box-titulo-papelera" id="confirmarPapeleraTitulo">¿Confirmas esta acción?</h2>
            <p class="modal-box-subtitulo-papelera" id="confirmarPapeleraTexto">Esta acción se puede revertir después.</p>

            <div class="modal-box-botones-papelera">
                <button type="button" class="btn-secundario-papelera" id="btnCancelarConfirmarPapelera">Cancelar</button>
                <button type="button" class="btn-peligro-papelera" id="btnAceptarConfirmarPapelera">Sí, confirmar</button>
            </div>
        </div>
    </div>

    <div id="toast"></div>

    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <script src="../JS/ui-common.js"></script>
    <script src="../JS/Papelera.js?v=<?= filemtime(__DIR__ . '/../JS/Papelera.js') ?>"></script>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
