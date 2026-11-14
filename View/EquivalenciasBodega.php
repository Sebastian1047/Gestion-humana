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
    <title>Equivalencias de Bodega – Jardines de San Nicolás</title>
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
                    <h2>Equivalencias de Bodega</h2>
                </div>

                <p style="color:#718096;font-size:0.9rem;margin-bottom:18px;">
                    Aquí configuras, una sola vez, qué prenda + talla + género del gestor corresponde a cada
                    código del inventario de bodega. Esta tabla se usa en
                    <a href="TallasExistencias.php" style="color:var(--color-primario);font-weight:600;">Tallas vs. Existencias</a>
                    para saber qué existencia corresponde a cada prenda pedida.
                </p>

                <div id="mensaje-resultado" class="mensaje-oculto"></div>

                <!-- Formulario agregar/editar -->
                <div class="card-seccion">
                    <h3 class="seccion-titulo" id="tituloFormEquiv">Agregar equivalencia</h3>
                    <form id="form-equivalencia" novalidate>
                        <input type="hidden" id="equiv_id" value="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="equiv_codigo">Código de bodega <span class="requerido">*</span></label>
                                <input type="text" id="equiv_codigo" class="input" placeholder="Ej. 7DOBOT121" />
                            </div>
                            <div class="form-group">
                                <label for="equiv_prenda">Prenda <span class="requerido">*</span></label>
                                <select id="equiv_prenda" class="input">
                                    <option value="">Selecciona</option>
                                    <option value="camisa">Camibuso</option>
                                    <option value="camisa_oxford">Camisa Oxford</option>
                                    <option value="camisa_polo">Camisa Polo</option>
                                    <option value="pantalon">Pantalón</option>
                                    <option value="sudadera">Sudadera</option>
                                    <option value="jean">Jean</option>
                                    <option value="overol">Overol</option>
                                    <option value="zapatos_seguridad">Zapatos Seguridad</option>
                                    <option value="zapatos">Zapatos</option>
                                    <option value="botas">Botas</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="equiv_talla">Talla <span class="requerido">*</span></label>
                                <input type="text" id="equiv_talla" class="input" placeholder="Ej. 39 o L" />
                            </div>
                            <div class="form-group">
                                <label for="equiv_genero">Género <small style="color:#a0aec0;">(vacío = aplica a ambos)</small></label>
                                <select id="equiv_genero" class="input">
                                    <option value="">Ambos géneros</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label for="equiv_descripcion">Descripción <small style="color:#a0aec0;">(opcional, referencia)</small></label>
                                <input type="text" id="equiv_descripcion" class="input" placeholder="Ej. BOTA CROYDON MACHA ALTA - TALLA 39" />
                            </div>
                        </div>
                        <div class="botones-wrap" style="justify-content:flex-start;margin-top:16px;">
                            <button type="submit" class="btn btn-guardar" id="btnGuardarEquiv">&#10003; Agregar</button>
                            <button type="button" class="btn btn-cancelar" id="btnCancelarEquiv" style="display:none;">&#10005; Cancelar edición</button>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="card-seccion">
                    <h3 class="seccion-titulo">Equivalencias registradas</h3>
                    <div class="tabla-container" style="border-radius:0;border:none;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Código Bodega</th>
                                    <th>Prenda</th>
                                    <th>Talla</th>
                                    <th>Género</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaEquivalencias">
                                <tr>
                                    <td colspan="6" style="text-align:center;color:#718096;padding:24px;">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="../JS/ui-common.js"></script>
    <script src="../JS/Script.js"></script>
    <script src="../JS/EquivalenciasBodega.js"></script>
    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
