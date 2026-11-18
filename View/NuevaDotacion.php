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
    <title>Nueva Dotación – Jardines de San Nicolás</title>
    <link rel="stylesheet" href="../CSS/variables.css" />
    <link rel="stylesheet" href="../CSS/StyleDotacion.css" />
    <link rel="stylesheet" href="../CSS/NewDotStyle.css" />
    <link rel="stylesheet" href="../CSS/ControlCuatrimestral.css" />
    <link rel="stylesheet" href="../CSS/NuevaDotacionPeriodos.css" />

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
                    <h2>Nueva Dotación</h2>
                </div>

                <div class="nd-demo-aviso"><strong>DEMO · Formulario integrado.</strong>
                    Prueba entregas cuatrimestrales y adelantos con datos ficticios. Los movimientos de esta pantalla se guardan solamente en este navegador, sin modificar registros ni servicios reales.
                </div>
                <!-- Mensaje resultado -->
                <div id="mensaje-resultado" class="mensaje-oculto"></div>

                <form id="form-dotacion" novalidate>

                    <!-- ========== DATOS DEL TRABAJADOR ========== -->
                    <div class="card-seccion">
                        <h3 class="seccion-titulo">Datos del Trabajador</h3>

                        <!-- Buscador -->
                        <div class="form-group buscador-wrap">
                            <label for="busqueda">Buscar por código o nombre <span class="requerido">*</span></label>
                            <input
                                type="text"
                                id="busqueda"
                                class="input"
                                placeholder="Escribe el código o nombre del colaborador..."
                                autocomplete="off" />
                            <div id="sugerencias" class="sugerencias-lista"></div>
                        </div>

                        <!-- Campos autocompletados (solo lectura, informativos) -->
                        <div class="form-grid">

                            <!--
                                IMPORTANTE: el código sí se envía al controller.
                                Nombre y cargo son solo informativos (readonly), ya no
                                se guardan en tbl_dotaciones — se traen del JOIN.
                            -->
                            <div class="form-group">
                                <label for="codigo">Código <span class="requerido">*</span></label>
                                <input type="text" id="codigo" name="codigo" class="input input-readonly" readonly placeholder="Automático" />
                            </div>

                            <div class="form-group">
                                <label for="nombre">Nombre <small style="color:#a0aec0;">(informativo)</small></label>
                                <input type="text" id="nombre" name="nombre" class="input input-readonly" readonly placeholder="Automático" />
                            </div>

                            <div class="form-group">
                                <label for="cargo">Centro de Costos <small style="color:#a0aec0;">(informativo)</small></label>
                                <input type="text" id="cargo" name="cargo" class="input input-readonly" readonly placeholder="Automático" />
                            </div>

                            <div class="form-group">
                                <label for="fecha_entrega">Fecha de Entrega <span class="requerido">*</span></label>
                                <input type="date" id="fecha_entrega" name="fecha_entrega" class="input"
                                    value="<?php echo date('Y-m-d'); ?>" />
                            </div>

                            <div class="form-group">
                                <label for="genero">Género <span class="requerido">*</span></label>
                                <select name="genero" id="genero" class="input">
                                    <option value="">Selecciona</option>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                </select>
                            </div>

                        </div>

                        <!-- Pregunta destacada: cambio de carnet -->
                        <fieldset class="carnet-deterioro">
                            <legend>¿Cambio de carnet por deterioro?</legend>
                            <div class="carnet-opciones">
                                <label class="carnet-opcion">
                                    <input type="radio" name="cambio_carnet_deterioro" value="si"> Sí
                                </label>
                                <label class="carnet-opcion">
                                    <input type="radio" name="cambio_carnet_deterioro" value="no"> No
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <!-- ========== CONTROL POR PERIODO Y ADELANTOS ========== -->
                    <section class="card-seccion nd-periodo-panel" aria-labelledby="nd-titulo-control">
                        <h3 class="seccion-titulo" id="nd-titulo-control">Control de dotación por cuatrimestre</h3>
                        <div class="nd-dos-col">
                            <div>
                                <label class="nd-label">Tipo de movimiento</label>
                                <div class="nd-tipos">
                                    <label><input type="radio" name="nd_tipo" value="normal" checked> Entrega normal</label>
                                    <label><input type="radio" name="nd_tipo" value="adelanto"> Adelanto de dotación</label>
                                </div>
                            </div>
                            <div class="nd-destino" id="ndDestino" aria-live="polite">Selecciona un trabajador y una fecha para consultar el periodo.</div>
                        </div>
                        <div id="ndAviso" role="status" aria-live="polite"></div>
                        <div class="nd-label" style="margin:15px 0 9px;">Estado de las tres dotaciones del año</div>
                        <div class="cc-resumen" id="ndResumen" aria-live="polite"></div>
                        <div class="nd-concepto">
                            <label for="ndConcepto">Concepto de la entrega o adelanto <span class="requerido">*</span></label>
                            <textarea id="ndConcepto" rows="2" maxlength="400" placeholder="Ej. Entrega ordinaria de agosto, cambio de labor..."></textarea>
                        </div>
                        <p class="nd-hint">Abril: periodo 1 · Agosto: periodo 2 · Diciembre: periodo 3. Solo se puede adelantar el periodo siguiente. El adelanto puede ser parcial o completo según las prendas seleccionadas.</p>
                    </section>

                    <section class="card-seccion nd-historial-panel" aria-labelledby="nd-titulo-historial">
                        <h3 class="seccion-titulo" id="nd-titulo-historial">Registro principal e historial de movimientos</h3>
                        <div id="ndHistorial" aria-live="polite"><p class="nd-vacio">Selecciona un colaborador para ver las prendas adelantadas y los movimientos del periodo.</p></div>
                    </section>

                    <!-- ========== PRENDAS ENTREGADAS ========== -->
                    <div class="card-seccion">
                        <h3 class="seccion-titulo">Prendas entregadas <small style="font-size:.78rem;font-weight:500;color:#64748b;">(se bloquean los artículos ya recibidos)</small></h3>
                        <p class="nd-prendas-instruccion" id="ndPlanDescripcion">Selecciona un colaborador para ver las prendas que corresponden a su dotación de prueba.</p>

                        <!-- Grupo: Ropa de trabajo -->
                        <p class="prendas-grupo-label">🧥 Ropa de trabajo</p>
                        <div class="prendas-grid">

                            <!-- CAMISA -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="camisa" id="check_camisa" class="check-prenda" />
                                        <span class="prenda-nombre">Camibuso</span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="camisa_talla">Talla</label>
                                    <select name="camisa_talla" id="camisa_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="XS">XS</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="XXL">XXL</option>
                                        <option value="XXXL">XXXL</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_camisa" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_camisa">Sexo opuesto</label>
                                </div>
                            </div>

                            <!-- CAMISA OXFORD -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="camisa_oxford" id="check_camisa_oxford" class="check-prenda" />
                                        <span class="prenda-nombre">Camisa Oxford <small class="prenda-badge-admin">Adm.</small></span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="camisa_oxford_talla">Talla</label>
                                    <select name="camisa_oxford_talla" id="camisa_oxford_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="XS">XS</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="XXL">XXL</option>
                                        <option value="XXXL">XXXL</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_camisa_oxford" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_camisa_oxford">Sexo opuesto</label>
                                </div>
                            </div>

                            <!-- CAMISA POLO -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="camisa_polo" id="check_camisa_polo" class="check-prenda" />
                                        <span class="prenda-nombre">Camisa Polo <small class="prenda-badge-admin">Adm.</small></span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="camisa_polo_talla">Talla</label>
                                    <select name="camisa_polo_talla" id="camisa_polo_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="XS">XS</option>
                                        <option value="S">S</option>
                                        <option value="M">M</option>
                                        <option value="L">L</option>
                                        <option value="XL">XL</option>
                                        <option value="XXL">XXL</option>
                                        <option value="XXXL">XXXL</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_camisa_polo" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_camisa_polo">Sexo opuesto</label>
                                </div>
                            </div>

                            <!-- PANTALÓN -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="pantalon" id="check_pantalon" class="check-prenda" />
                                        <span class="prenda-nombre">Pantalón</span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="pantalon_talla">Talla</label>
                                    <select name="pantalon_talla" id="pantalon_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="4">4</option>
                                        <option value="6">6</option>
                                        <option value="8">8</option>
                                        <option value="10">10</option>
                                        <option value="12">12</option>
                                        <option value="14">14</option>
                                        <option value="16">16</option>
                                        <option value="18">18</option>
                                        <option value="20">20</option>
                                        <option value="22">22</option>
                                        <option value="24">24</option>
                                        <option value="26">26</option>
                                        <option value="28">28</option>
                                        <option value="30">30</option>
                                        <option value="32">32</option>
                                        <option value="34">34</option>
                                        <option value="36">36</option>
                                        <option value="38">38</option>
                                        <option value="40">40</option>
                                        <option value="42">42</option>
                                        <option value="44">44</option>
                                        <option value="46">46</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_pantalon" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_pantalon">Sexo opuesto</label>
                                </div>
                            </div>

                            <!-- SUDADERA -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="sudadera" id="check_sudadera" class="check-prenda" />
                                        <span class="prenda-nombre">Sudadera</span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="sudadera_talla">Talla</label>
                                    <select name="sudadera_talla" id="sudadera_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="4">4</option>
                                        <option value="6">6</option>
                                        <option value="8">8</option>
                                        <option value="10">10</option>
                                        <option value="12">12</option>
                                        <option value="14">14</option>
                                        <option value="16">16</option>
                                        <option value="18">18</option>
                                        <option value="20">20</option>
                                        <option value="22">22</option>
                                        <option value="24">24</option>
                                        <option value="26">26</option>
                                        <option value="28">28</option>
                                        <option value="30">30</option>
                                        <option value="32">32</option>
                                        <option value="34">34</option>
                                        <option value="36">36</option>
                                        <option value="38">38</option>
                                        <option value="40">40</option>
                                        <option value="42">42</option>
                                        <option value="44">44</option>
                                        <option value="46">46</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_sudadera" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_sudadera">Sexo opuesto</label>
                                </div>
                            </div>

                            <!-- JEAN -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="jean" id="check_jean" class="check-prenda" />
                                        <span class="prenda-nombre">Jean</span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="jean_talla">Talla</label>
                                    <select name="jean_talla" id="jean_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="4">4</option>
                                        <option value="6">6</option>
                                        <option value="8">8</option>
                                        <option value="10">10</option>
                                        <option value="12">12</option>
                                        <option value="14">14</option>
                                        <option value="16">16</option>
                                        <option value="18">18</option>
                                        <option value="20">20</option>
                                        <option value="22">22</option>
                                        <option value="24">24</option>
                                        <option value="26">26</option>
                                        <option value="28">28</option>
                                        <option value="30">30</option>
                                        <option value="32">32</option>
                                        <option value="34">34</option>
                                        <option value="36">36</option>
                                        <option value="38">38</option>
                                        <option value="40">40</option>
                                        <option value="42">42</option>
                                        <option value="44">44</option>
                                        <option value="46">46</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_jean" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_jean">Sexo opuesto</label>
                                </div>
                            </div>

                            <!-- OVEROL -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="overol" id="check_overol" class="check-prenda" />
                                        <span class="prenda-nombre">Overol</span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="overol_talla">Talla</label>
                                    <select name="overol_talla" id="overol_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="4">4</option>
                                        <option value="6">6</option>
                                        <option value="8">8</option>
                                        <option value="10">10</option>
                                        <option value="12">12</option>
                                        <option value="14">14</option>
                                        <option value="16">16</option>
                                        <option value="18">18</option>
                                        <option value="20">20</option>
                                        <option value="22">22</option>
                                        <option value="24">24</option>
                                        <option value="26">26</option>
                                        <option value="28">28</option>
                                        <option value="30">30</option>
                                        <option value="32">32</option>
                                        <option value="34">34</option>
                                        <option value="36">36</option>
                                        <option value="38">38</option>
                                        <option value="40">40</option>
                                        <option value="42">42</option>
                                        <option value="44">44</option>
                                        <option value="46">46</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_overol" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_overol">Sexo opuesto</label>
                                </div>
                            </div>

                        </div><!-- /prendas-grid ropa -->

                        <!-- Grupo: Zapatos -->
                        <p class="prendas-grupo-label" style="margin-top:20px;">👟 Zapatos</p>
                        <div class="prendas-grid">

                            <!-- ZAPATOS SEGURIDAD -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="zapatos_seguridad" id="check_zapatos_seguridad" class="check-prenda" />
                                        <span class="prenda-nombre">Zapatos Seguridad</span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="zapatos_seguridad_talla">Talla</label>
                                    <select name="zapatos_seguridad_talla" id="zapatos_seguridad_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="34">34</option>
                                        <option value="35">35</option>
                                        <option value="36">36</option>
                                        <option value="37">37</option>
                                        <option value="38">38</option>
                                        <option value="39">39</option>
                                        <option value="40">40</option>
                                        <option value="41">41</option>
                                        <option value="42">42</option>
                                        <option value="43">43</option>
                                        <option value="44">44</option>
                                        <option value="45">45</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_zapatos_seguridad" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_zapatos_seguridad">Sexo opuesto</label>
                                </div>
                            </div>

                            <!-- ZAPATOS -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="zapatos" id="check_zapatos" class="check-prenda" />
                                        <span class="prenda-nombre">Zapatos</span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="zapatos_talla">Talla</label>
                                    <select name="zapatos_talla" id="zapatos_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="34">34</option>
                                        <option value="35">35</option>
                                        <option value="36">36</option>
                                        <option value="37">37</option>
                                        <option value="38">38</option>
                                        <option value="39">39</option>
                                        <option value="40">40</option>
                                        <option value="41">41</option>
                                        <option value="42">42</option>
                                        <option value="43">43</option>
                                        <option value="44">44</option>
                                        <option value="45">45</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_zapatos" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_zapatos">Sexo opuesto</label>
                                </div>
                            </div>

                            <!-- BOTAS -->
                            <div class="prenda-bloque">
                                <div class="prenda-header">
                                    <label class="prenda-label">
                                        <input type="checkbox" name="botas" id="check_botas" class="check-prenda" />
                                        <span class="prenda-nombre">Botas</span>
                                    </label>
                                </div>
                                <div class="prenda-talla">
                                    <label for="botas_talla">Talla</label>
                                    <select name="botas_talla" id="botas_talla" class="input select-talla" disabled>
                                        <option value="">Selecciona</option>
                                        <option value="34">34</option>
                                        <option value="35">35</option>
                                        <option value="36">36</option>
                                        <option value="37">37</option>
                                        <option value="38">38</option>
                                        <option value="39">39</option>
                                        <option value="40">40</option>
                                        <option value="41">41</option>
                                        <option value="42">42</option>
                                        <option value="43">43</option>
                                        <option value="44">44</option>
                                        <option value="45">45</option>
                                    </select>
                                </div>
                                <div class="prenda-sexo-opuesto">
                                    <input type="checkbox" id="sexo_opuesto_botas" class="check-sexo-opuesto" disabled />
                                    <label for="sexo_opuesto_botas">Sexo opuesto</label>
                                </div>
                            </div>

                        </div><!-- /prendas-grid zapatos -->
                    </div>

                    <!-- ========== FIRMAS ========== -->
                    <div class="card-seccion">
                        <h3 class="seccion-titulo">Firmas</h3>
                        <p class="nd-hint" id="ndTextoFirmas">Entrega normal: firmas del colaborador y encargado. Adelanto: firma de quien recibe.</p>

                        <div class="firmas-grid">

                            <!-- Firma Trabajador -->
                            <div class="firma-bloque">
                                <label class="firma-label">Firma del Colaborador</label>
                                <div class="canvas-wrap" id="wrap_trabajador">
                                    <canvas id="canvas_trabajador"></canvas>
                                    <span class="canvas-hint">Firme aquí</span>
                                </div>
                                <div class="firma-acciones">
                                    <button type="button" class="btn btn-limpiar" id="limpiar_trabajador">
                                        &#10005; Limpiar
                                    </button>
                                    <span class="firma-estado" id="estado_trabajador">Sin firma</span>
                                </div>
                                <input type="hidden" name="firma_trabajador" id="firma_trabajador_data" />
                            </div>

                            <!-- Firma Encargado -->
                            <div class="firma-bloque" id="ndEncargadoBloque">
                                <label class="firma-label">Firma del Encargado</label>
                                <div class="canvas-wrap" id="wrap_encargado">
                                    <canvas id="canvas_encargado"></canvas>
                                    <span class="canvas-hint">Firme aquí</span>
                                </div>
                                <div class="firma-acciones">
                                    <button type="button" class="btn btn-limpiar" id="limpiar_encargado">
                                        &#10005; Limpiar
                                    </button>
                                    <span class="firma-estado" id="estado_encargado">Sin firma</span>
                                </div>
                                <input type="hidden" name="firma_encargado" id="firma_encargado_data" />
                            </div>

                        </div>
                    </div>


                    <!-- ========== OBSERVACIONES ========== -->
                    <div class="card-seccion">
                        <h3 class="seccion-titulo">Observaciones</h3>
                        <div class="form-group">
                            <label for="observaciones">Observaciones <small style="color:#a0aec0;">(opcional)</small></label>
                            <textarea
                                id="observaciones"
                                name="observaciones"
                                class="input"
                                rows="4"
                                maxlength="500"
                                placeholder="Escribe cualquier observación relevante sobre esta dotación..."
                                style="resize:vertical; width:100%; font-family:inherit; font-size:0.95rem; padding:10px 12px; border-radius:8px; border:1px solid #e2e8f0; color:#2d3748; line-height:1.5;"></textarea>
                            <small style="color:#a0aec0; display:block; margin-top:4px;">Máximo 500 caracteres</small>
                        </div>
                    </div>

                    <!-- ========== BOTONES ========== -->
                    <div class="botones-wrap">
                        <button type="submit" class="btn btn-guardar" id="btn-guardar">
                            &#10003; Registrar movimiento DEMO
                        </button>
                        <a href="Dotacion.php" class="btn btn-cancelar">
                            &#10005; Cancelar
                        </a>
                    </div>

                </form>

            </section>
        </main>
    </div>

    <script src="../JS/ui-common.js"></script>
    <script src="../JS/firma-canvas.js"></script>
    <script src="../JS/NuevaDotacionPeriodos.js?v=20261118a"></script>
    <script src="../JS/Script.js"></script>
    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
