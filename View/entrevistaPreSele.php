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
    <title>Entrevista Selección de Personal – Jardines de San Nicolás</title>
    <link rel="stylesheet" href="../CSS/variables.css">
    <link rel="stylesheet" href="../CSS/layout.css">
    <link rel="stylesheet" href="../CSS/componentes.css">
    <link rel="stylesheet" href="../CSS/Stylepreingreso.css">
</head>

<style>
    body {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
</style>

<body>

    <?php include __DIR__ . '/partials/hamburger.php'; ?>

    <div class="container">

        <?php
        $sidebar_subtitulo = 'Entrevista Selección';
        include __DIR__ . '/partials/sidebar.php';
        ?>

        <main class="main">
            <header class="header">

                <button class="btn-registros" id="btn-registros">
                    <b>Ver Registros</b>
                </button>

                <div class="modal-registros" id="modalRegistros">
                    <div class="modal-contenido">
                        <div class="modal-header">
                            <h2>📁 Registros guardados</h2>
                            <button class="cerrar-modal" id="cerrarModal">✕</button>
                        </div>
                        <div class="busqueda-registros">
                            <input type="text" id="buscarFecha" placeholder="Buscar">
                        </div>
                        <div class="lista-registros" id="listaRegistros"></div>
                    </div>
                </div>

                <!-- ══ MODAL ELIMINAR ARCHIVO ══ -->
                <div class="modal-overlay" id="modalEliminarOverlay">
                    <div class="modal-box">
                        <div class="modal-icon">🗑️</div>
                        <h2 class="modal-title">¿Eliminar archivo?</h2>
                        <p class="modal-text" id="modalEliminarTexto">
                            ¿Estás seguro que quieres eliminar este registro?
                        </p>
                        <div class="modal-buttons">
                            <button class="btn btn-confirm" id="btnConfirmEliminar">Sí, eliminar</button>
                            <button class="btn btn-cancel" id="btnCancelEliminar">Cancelar</button>
                        </div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;">
                    <?php include __DIR__ . '/partials/icono_papelera_header.php'; ?>
                    <div class="user">
                        <span>👤 <?php echo $_SESSION['user']['nombre'] ?? $_SESSION['user']['codigoempleado'] ?? 'Usuario'; ?></span>
                    </div>
                </div>
            </header>

            <section class="content">
                <div class="top-bar">
                    <h2>Entrevista Selección de Personal</h2>
                </div>

                <!-- Barra de progreso -->
                <div class="progreso-wrap">
                    <div class="progreso-pasos">
                        <div class="paso activo" data-paso="1"><div class="paso-circulo">1</div><span class="paso-label">Encabezado</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="2"><div class="paso-circulo">2</div><span class="paso-label">Info General</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="3"><div class="paso-circulo">3</div><span class="paso-label">Vivienda</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="4"><div class="paso-circulo">4</div><span class="paso-label">Familia</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="5"><div class="paso-circulo">5</div><span class="paso-label">Salud</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="6"><div class="paso-circulo">6</div><span class="paso-label">Experiencia</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="7"><div class="paso-circulo">7</div><span class="paso-label">Perfil</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="8"><div class="paso-circulo">8</div><span class="paso-label">Planes</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="9"><div class="paso-circulo">9</div><span class="paso-label">Prueba</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="10"><div class="paso-circulo">10</div><span class="paso-label">Certificación</span></div>
                        <div class="paso-linea"></div>
                        <div class="paso" data-paso="11"><div class="paso-circulo">11</div><span class="paso-label">Conclusiones</span></div>
                    </div>
                    <div class="progreso-barra-wrap">
                        <div class="progreso-barra" id="progresoBarra"></div>
                    </div>
                </div>

                <form id="form-preingreso" novalidate>

                    <!-- ═══ SECCIÓN 1: ENCABEZADO ═══ -->
                    <div class="seccion-paso" id="paso-1">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">📋 Sección 1 — Encabezado</h3>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label for="fecha">Fecha <span class="req">*</span></label>
                                    <input type="date" id="fecha" name="fecha" class="input" required value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="cargo">Cargo <span class="req">*</span></label>
                                    <input type="text" id="cargo" name="cargo" class="input" required placeholder="Ej: Auxiliar de producción">
                                </div>
                                <div class="form-group">
                                    <label for="responsable_entrevista">Responsable de la entrevista <span class="req">*</span></label>
                                    <input type="text" id="responsable_entrevista" name="responsable_entrevista" class="input" required placeholder="Nombre completo">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 2: INFORMACIÓN GENERAL ═══ -->
                    <div class="seccion-paso oculto" id="paso-2">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">👤 Sección 2 — Información General</h3>

                            <h4 class="subseccion-titulo">Datos Personales</h4>
                            <div class="form-grid-3">
                                <div class="form-group span-2">
                                    <label for="nombre_completo">Nombre completo <span class="req">*</span></label>
                                    <input type="text" id="nombre_completo" name="nombre_completo" class="input" required placeholder="Apellidos y nombres">
                                </div>
                                <div class="form-group">
                                    <label for="cedula">No. C.C. <span class="req">*</span></label>
                                    <input type="text" id="cedula" name="cedula" class="input" required placeholder="Sin puntos ni espacios" inputmode="numeric" pattern="[0-9]*" oninput="soloNumeros(this)">
                                </div>
                                <div class="form-group span-2">
                                    <label for="lugar_nacimiento">Lugar de nacimiento <span class="req">*</span></label>
                                    <input type="text" id="lugar_nacimiento" name="lugar_nacimiento" class="input" required placeholder="Ciudad, Departamento">
                                </div>
                                <div class="form-group">
                                    <label for="fecha_nacimiento">Fecha de nacimiento <span class="req">*</span></label>
                                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="input" required>
                                </div>
                                <div class="form-group">
                                    <label for="edad">Edad actual</label>
                                    <input type="number" id="edad" name="edad" class="input input-readonly" readonly placeholder="Automático">
                                </div>
                                <div class="form-group">
                                    <label for="telefono_fijo"># Teléfono fijo</label>
                                    <input type="text" id="telefono_fijo" name="telefono_fijo" class="input" placeholder="Número fijo">
                                </div>
                                <div class="form-group">
                                    <label for="celular"># Celular <span class="req">*</span></label>
                                    <input type="text" id="celular" name="celular" class="input" required placeholder="10 dígitos" maxlength="10" inputmode="numeric" pattern="[0-9]*" oninput="soloNumeros(this)">
                                </div>
                                <div class="form-group span-2">
                                    <label for="correo">Correo electrónico</label>
                                    <input type="email" id="correo" name="correo" class="input" placeholder="ejemplo@correo.com">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Información Militar</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>¿Ha prestado servicio militar?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="servicio_militar" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="servicio_militar" value="no"> No</label>
                                    </div>
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Información Financiera</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>¿Tiene cuenta de ahorros?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="cuenta_ahorros" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="cuenta_ahorros" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_banco" style="display:none;">
                                    <label for="banco">¿Dónde?</label>
                                    <input type="text" id="banco" name="banco" class="input" placeholder="Nombre del banco">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Seguridad Social</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>¿Tiene EPS?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_eps" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_eps" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_eps" style="display:none;">
                                    <label for="cual_eps">¿Cuál EPS?</label>
                                    <input type="text" id="cual_eps" name="cual_eps" class="input" placeholder="Nombre de la EPS">
                                </div>
                                <div class="form-group">
                                    <label>¿Tiene pensión?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_pension" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_pension" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group" id="wrap_fondo" style="display:none;">
                                    <label for="fondo_pension">¿Cuál fondo?</label>
                                    <input type="text" id="fondo_pension" name="fondo_pension" class="input" placeholder="Nombre del fondo">
                                </div>
                                <div class="form-group">
                                    <label for="tiempo_cotizacion">Tiempo de cotización</label>
                                    <input type="text" id="tiempo_cotizacion" name="tiempo_cotizacion" class="input" placeholder="Ej: 5 años">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Educación</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label for="escolaridad">Grado de escolaridad <span class="req">*</span></label>
                                    <select id="escolaridad" name="escolaridad" class="input" required>
                                        <option value="">Selecciona...</option>
                                        <option value="primaria">Primaria</option>
                                        <option value="bachiller">Bachiller</option>
                                        <option value="tecnico">Técnico</option>
                                        <option value="tecnologo">Tecnólogo</option>
                                        <option value="profesional">Profesional</option>
                                        <option value="especializacion">Especialización</option>
                                        <option value="maestria">Maestría</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>¿Continúa estudiando?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="continua_estudiando" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="continua_estudiando" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-3" id="wrap_estudio_detalle" style="display:none;">
                                    <label for="estudio_detalle">Explique</label>
                                    <textarea id="estudio_detalle" name="estudio_detalle" class="input textarea" rows="2" placeholder="¿Qué estudia? ¿Horarios?"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 3: VIVIENDA ═══ -->
                    <div class="seccion-paso oculto" id="paso-3">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">🏠 Sección 3 — Vivienda</h3>

                            <h4 class="subseccion-titulo">Ubicación</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label for="municipio">Municipio <span class="req">*</span></label>
                                    <input type="text" id="municipio" name="municipio" class="input" required placeholder="Municipio donde vive">
                                </div>
                                <div class="form-group span-2">
                                    <label for="direccion">Dirección <span class="req">*</span></label>
                                    <input type="text" id="direccion" name="direccion" class="input" required placeholder="Dirección completa">
                                </div>
                                <div class="form-group">
                                    <label for="barrio">Barrio</label>
                                    <input type="text" id="barrio" name="barrio" class="input" placeholder="Barrio o vereda">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Tipo de Vivienda</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Modalidad de vivienda <span class="req">*</span></label>
                                    <div class="radio-group vertical">
                                        <label class="radio-label"><input type="radio" name="modalidad_vivienda" value="propia"> Propia</label>
                                        <label class="radio-label"><input type="radio" name="modalidad_vivienda" value="arrendada"> Arrendada</label>
                                        <label class="radio-label"><input type="radio" name="modalidad_vivienda" value="prestada"> Prestada</label>
                                        <label class="radio-label"><input type="radio" name="modalidad_vivienda" value="familiar"> Familiar</label>
                                        <label class="radio-label"><input type="radio" name="modalidad_vivienda" value="otra"> Otra</label>
                                    </div>
                                </div>
                                <div class="form-group" id="wrap_otra_vivienda" style="display:none;">
                                    <label for="otra_vivienda">¿Cuál otra?</label>
                                    <input type="text" id="otra_vivienda" name="otra_vivienda" class="input">
                                </div>
                                <div class="form-group" id="wrap_valor_arrendo" style="display:none;">
                                    <label for="valor_arrendo">Valor arrendo</label>
                                    <input type="text" id="valor_arrendo" name="valor_arrendo" class="input" placeholder="Ej: $450.000">
                                </div>
                                <div class="form-group">
                                    <label for="tiempo_vivienda">¿Hace cuánto vive allí?</label>
                                    <input type="text" id="tiempo_vivienda" name="tiempo_vivienda" class="input" placeholder="Ej: 3 años">
                                </div>
                                <div class="form-group">
                                    <label for="cambios_casa">Cambios de casa en últimos 3 años</label>
                                    <input type="number" id="cambios_casa" name="cambios_casa" class="input" min="0" placeholder="0">
                                </div>
                                <div class="form-group span-2">
                                    <label for="explica_cambios">Explique los cambios</label>
                                    <textarea id="explica_cambios" name="explica_cambios" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="desplazamiento">Si pasa el proceso de selección, ¿en qué se desplazaría a trabajar?</label>
                                    <textarea id="desplazamiento" name="desplazamiento" class="input textarea" rows="2" placeholder="Ej: en moto, en bus..."></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="num_habitantes">Número de habitantes en la vivienda</label>
                                    <input type="number" id="num_habitantes" name="num_habitantes" class="input" min="1" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label>¿Tiene animales?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_animales" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_animales" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group" id="wrap_animales" style="display:none;">
                                    <label for="cuales_animales">¿Cuáles?</label>
                                    <input type="text" id="cuales_animales" name="cuales_animales" class="input">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Materiales de construcción</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label for="material_techo">Techo</label>
                                    <input type="text" id="material_techo" name="material_techo" class="input" placeholder="Ej: teja, placa...">
                                </div>
                                <div class="form-group">
                                    <label for="material_paredes">Paredes</label>
                                    <input type="text" id="material_paredes" name="material_paredes" class="input" placeholder="Ej: ladrillo, bahareque...">
                                </div>
                                <div class="form-group">
                                    <label for="material_piso">Piso</label>
                                    <input type="text" id="material_piso" name="material_piso" class="input" placeholder="Ej: baldosa, cemento, tierra...">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Espacios de la vivienda</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>¿Tiene cocina?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_cocina" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_cocina" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>¿Tiene baños?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_banos" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_banos" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group" id="wrap_num_banos" style="display:none;">
                                    <label for="num_banos"># Baños</label>
                                    <input type="number" id="num_banos" name="num_banos" class="input" min="0" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label>¿Tiene alcobas?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_alcobas" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_alcobas" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group" id="wrap_num_alcobas" style="display:none;">
                                    <label for="num_alcobas"># Alcobas</label>
                                    <input type="number" id="num_alcobas" name="num_alcobas" class="input" min="0" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label>¿Tiene patio?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_patio" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_patio" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group" id="wrap_num_patios" style="display:none;">
                                    <label for="num_patios"># Patios</label>
                                    <input type="number" id="num_patios" name="num_patios" class="input" min="0" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 4: FAMILIA ═══ -->
                    <div class="seccion-paso oculto" id="paso-4">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">👨‍👩‍👧 Sección 4 — Familia</h3>

                            <h4 class="subseccion-titulo">Estado civil y convivencia</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>Estado civil</label>
                                    <div class="radio-group vertical">
                                        <label class="radio-label"><input type="radio" name="estado_civil" value="casado"> Casado</label>
                                        <label class="radio-label"><input type="radio" name="estado_civil" value="soltero"> Soltero</label>
                                        <label class="radio-label"><input type="radio" name="estado_civil" value="union_libre"> Unión libre</label>
                                        <label class="radio-label"><input type="radio" name="estado_civil" value="separado"> Separado</label>
                                        <label class="radio-label"><input type="radio" name="estado_civil" value="otro"> Otro</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_estado_civil_otro" style="display:none;">
                                    <label for="estado_civil_otro">¿Cuál otro?</label>
                                    <input type="text" id="estado_civil_otro" name="estado_civil_otro" class="input">
                                </div>
                                <div class="form-group span-3">
                                    <label for="con_quien_vive">¿Con quién vive?</label>
                                    <textarea id="con_quien_vive" name="con_quien_vive" class="input textarea" rows="2" placeholder="Ej: esposa, 2 hijos, madre"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="num_personas_cargo">Número de personas a cargo</label>
                                    <input type="number" id="num_personas_cargo" name="num_personas_cargo" class="input" min="0" placeholder="0">
                                </div>
                                <div class="form-group span-2">
                                    <label for="quienes_cargo">¿Quiénes?</label>
                                    <textarea id="quienes_cargo" name="quienes_cargo" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="quien_aporta">¿Quién aporta en el hogar económicamente?</label>
                                    <textarea id="quien_aporta" name="quien_aporta" class="input textarea" rows="2"></textarea>
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Pareja</h4>
                            <div class="form-grid-3">
                                <div class="form-group span-2">
                                    <label for="nombre_pareja">Nombre del esposo/a o compañero/a</label>
                                    <input type="text" id="nombre_pareja" name="nombre_pareja" class="input" placeholder="Nombre completo (o 'No aplica')">
                                </div>
                                <div class="form-group">
                                    <label for="edad_pareja">Edad</label>
                                    <input type="number" id="edad_pareja" name="edad_pareja" class="input" min="0" placeholder="0">
                                </div>
                                <div class="form-group span-3">
                                    <label for="info_pareja">¿Hace cuánto está con usted y a qué se dedica?</label>
                                    <textarea id="info_pareja" name="info_pareja" class="input textarea" rows="2"></textarea>
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Hijos o Hijastros</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label for="num_hijos">Número de hijos</label>
                                    <input type="number" id="num_hijos" name="num_hijos" class="input" min="0" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label for="num_hijos_hijastros">Número de hijos o hijastros</label>
                                    <input type="number" id="num_hijos_hijastros" name="num_hijos_hijastros" class="input" min="0" placeholder="0">
                                </div>
                                <div class="form-group">
                                    <label>¿Son de un solo padre/madre?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="hijos_mismo_padre" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="hijos_mismo_padre" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>¿Ha recibido/recibe cuota monetaria por sus hijos o sus padres?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="cuota_hijos" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="cuota_hijos" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_medio_pago" style="display:none;">
                                    <label for="medio_pago_cuota">Medio de pago</label>
                                    <input type="text" id="medio_pago_cuota" name="medio_pago_cuota" class="input" placeholder="Ej: transferencia bancaria">
                                </div>
                                <div class="form-group">
                                    <label>¿Sus hijos presentan alguna adicción?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="hijos_adiccion" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="hijos_adiccion" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_hijos_adiccion" style="display:none;">
                                    <label for="cual_hijos_adiccion">¿Cuál?</label>
                                    <input type="text" id="cual_hijos_adiccion" name="cual_hijos_adiccion" class="input">
                                </div>
                            </div>

                            <div class="tabla-dinamica-wrap">
                                <div class="tabla-dinamica-header">
                                    <span class="tabla-dinamica-titulo">Información de hijos</span>
                                    <button type="button" class="btn-agregar-fila" id="btnAgregarHijo">+ Agregar hijo</button>
                                </div>
                                <div class="tabla-responsive">
                                    <table class="tabla-dinamica" id="tablaHijos">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Edad</th>
                                                <th>¿Qué hace?</th>
                                                <th>¿Sufre alguna enfermedad?</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="bodyHijos">
                                            <tr class="fila-vacia">
                                                <td colspan="5">Sin hijos registrados. Haz clic en "+ Agregar hijo".</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Padres</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>¿Vive el padre?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="padre_vive" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="padre_vive" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_causa_padre" style="display:none;">
                                    <label for="causa_muerte_padre">Si murió, ¿cuál fue la causa de la muerte?</label>
                                    <input type="text" id="causa_muerte_padre" name="causa_muerte_padre" class="input">
                                </div>
                                <div class="form-group">
                                    <label>¿Vive la madre?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="madre_vive" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="madre_vive" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_causa_madre" style="display:none;">
                                    <label for="causa_muerte_madre">Si murió, ¿cuál fue la causa de la muerte?</label>
                                    <input type="text" id="causa_muerte_madre" name="causa_muerte_madre" class="input">
                                </div>
                            </div>

                            <div class="tabla-dinamica-wrap">
                                <div class="tabla-dinamica-header">
                                    <span class="tabla-dinamica-titulo">Información de los padres</span>
                                </div>
                                <div class="tabla-responsive">
                                    <table class="tabla-dinamica">
                                        <thead>
                                            <tr>
                                                <th>Parentesco</th>
                                                <th>Nombre</th>
                                                <th>Edad</th>
                                                <th>¿Qué hace?</th>
                                                <th>¿Dónde vive?</th>
                                                <th>¿Sufre alguna enfermedad?</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Padre</strong></td>
                                                <td><input type="text" name="padre_nombre" class="input input-tabla" placeholder="Nombre"></td>
                                                <td><input type="number" name="padre_edad" class="input input-tabla" placeholder="Edad" min="0" style="width:70px;"></td>
                                                <td><input type="text" name="padre_que_hace" class="input input-tabla" placeholder="Ocupación"></td>
                                                <td><input type="text" name="padre_donde_vive" class="input input-tabla" placeholder="Ciudad"></td>
                                                <td><input type="text" name="padre_enfermedad" class="input input-tabla" placeholder="Ninguna/especificar"></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Madre</strong></td>
                                                <td><input type="text" name="madre_nombre" class="input input-tabla" placeholder="Nombre"></td>
                                                <td><input type="number" name="madre_edad" class="input input-tabla" placeholder="Edad" min="0" style="width:70px;"></td>
                                                <td><input type="text" name="madre_que_hace" class="input input-tabla" placeholder="Ocupación"></td>
                                                <td><input type="text" name="madre_donde_vive" class="input input-tabla" placeholder="Ciudad"></td>
                                                <td><input type="text" name="madre_enfermedad" class="input input-tabla" placeholder="Ninguna/especificar"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Hermanos</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label for="num_hermanos">Número de hermanos</label>
                                    <input type="number" id="num_hermanos" name="num_hermanos" class="input" min="0" placeholder="0">
                                </div>
                                <div class="form-group span-2">
                                    <label for="hermanos_allegados">Mencione los más allegados</label>
                                    <input type="text" id="hermanos_allegados" name="hermanos_allegados" class="input">
                                </div>
                            </div>

                            <div class="tabla-dinamica-wrap">
                                <div class="tabla-dinamica-header">
                                    <span class="tabla-dinamica-titulo">Información de hermanos</span>
                                    <button type="button" class="btn-agregar-fila" id="btnAgregarHermano">+ Agregar hermano</button>
                                </div>
                                <div class="tabla-responsive">
                                    <table class="tabla-dinamica" id="tablaHermanos">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>¿Qué hace?</th>
                                                <th>¿Dónde vive?</th>
                                                <th>¿Sufre alguna enfermedad?</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="bodyHermanos">
                                            <tr class="fila-vacia">
                                                <td colspan="5">Sin hermanos registrados. Haz clic en "+ Agregar hermano".</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Contacto de emergencia</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label for="emergencia_nombre">Nombre</label>
                                    <input type="text" id="emergencia_nombre" name="emergencia_nombre" class="input" placeholder="Nombre completo">
                                </div>
                                <div class="form-group">
                                    <label for="emergencia_parentesco">Parentesco</label>
                                    <input type="text" id="emergencia_parentesco" name="emergencia_parentesco" class="input" placeholder="Ej: hermana, esposo...">
                                </div>
                                <div class="form-group">
                                    <label for="emergencia_telefono">Teléfono de contacto</label>
                                    <input type="text" id="emergencia_telefono" name="emergencia_telefono" class="input" placeholder="Número">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Dinámica familiar</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label for="quien_cuida_hijos">¿Quién le colabora con el cuidado de sus hijos?</label>
                                    <input type="text" id="quien_cuida_hijos" name="quien_cuida_hijos" class="input">
                                </div>
                                <div class="form-group span-2">
                                    <label for="costo_cuidado_hijos">¿Tiene algún costo?</label>
                                    <input type="text" id="costo_cuidado_hijos" name="costo_cuidado_hijos" class="input" placeholder="Ej: sí, $X mensuales / no">
                                </div>
                                <div class="form-group span-3">
                                    <label for="describa_familia">Describa su familia y cómo son sus relaciones</label>
                                    <textarea id="describa_familia" name="describa_familia" class="input textarea" rows="3"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="tiempo_familia">¿Qué tiempo comparte con su familia? ¿Qué actividades realizan juntos? Hobbies (cómo emplea su tiempo libre y si participa en actividades diferentes a las laborales y personales, ¿cuáles?)</label>
                                    <textarea id="tiempo_familia" name="tiempo_familia" class="input textarea" rows="3"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="autoridad_familia">¿Quién ejerce la autoridad en la familia?</label>
                                    <textarea id="autoridad_familia" name="autoridad_familia" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="valores_familia">¿Cuáles son los valores que se promulgan en su familia? (los 3 más importantes)</label>
                                    <textarea id="valores_familia" name="valores_familia" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="acontecimiento_familiar">Relate un acontecimiento familiar importante que haya sucedido en los últimos 6 meses</label>
                                    <textarea id="acontecimiento_familiar" name="acontecimiento_familiar" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="comportamiento_dificiles">¿Cómo es su comportamiento frente a situaciones difíciles?</label>
                                    <textarea id="comportamiento_dificiles" name="comportamiento_dificiles" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="situacion_honradez">Mencione alguna situación donde haya actuado con honradez y solidaridad</label>
                                    <textarea id="situacion_honradez" name="situacion_honradez" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="situaciones_eticas">¿Cómo actúa usted ante situaciones poco éticas? ¿Le da temor enfrentarse a las injusticias?</label>
                                    <textarea id="situaciones_eticas" name="situaciones_eticas" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="planes_familiares">Planes e intereses familiares</label>
                                    <textarea id="planes_familiares" name="planes_familiares" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="circulo_apoyo">¿Cuenta con un círculo de apoyo en situaciones difíciles?</label>
                                    <textarea id="circulo_apoyo" name="circulo_apoyo" class="input textarea" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 5: SALUD ═══ -->
                    <div class="seccion-paso oculto" id="paso-5">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">🏥 Sección 5 — Salud</h3>

                            <h4 class="subseccion-titulo">Hábitos</h4>
                            <div class="form-grid-3">
                                <div class="form-group">
                                    <label>¿Fuma?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="fuma" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="fuma" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_frecuencia_fuma" style="display:none;">
                                    <label for="frecuencia_fuma">Frecuencia</label>
                                    <input type="text" id="frecuencia_fuma" name="frecuencia_fuma" class="input" placeholder="Ej: 5 cigarrillos al día">
                                </div>
                                <div class="form-group">
                                    <label>¿Consume licor?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="consume_licor" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="consume_licor" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_frecuencia_licor" style="display:none;">
                                    <label for="frecuencia_licor">Frecuencia</label>
                                    <input type="text" id="frecuencia_licor" name="frecuencia_licor" class="input" placeholder="Ej: fines de semana">
                                </div>
                                <div class="form-group">
                                    <label>¿Consumo de drogas?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="consume_drogas" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="consume_drogas" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_frecuencia_drogas" style="display:none;">
                                    <label for="frecuencia_drogas">Frecuencia</label>
                                    <input type="text" id="frecuencia_drogas" name="frecuencia_drogas" class="input">
                                </div>
                            </div>

                            <div class="aviso-rrhh">🔒 Uso exclusivo del entrevistador / RRHH</div>
                            <div class="form-grid-3">
                                <div class="form-group span-3">
                                    <label for="codigos_salud">Códigos (SE / AS / TM / CPR / ACC. LTC / Ci.C) y observaciones (TC)</label>
                                    <textarea id="codigos_salud" name="codigos_salud" class="input textarea" rows="3" placeholder="Ej: SE___ AS___ TM___ CPR___ ACC.LTC___ Ci.C___ — Observaciones: ..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 6: EXPERIENCIA LABORAL ═══ -->
                    <div class="seccion-paso oculto" id="paso-6">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">💼 Sección 6 — Experiencia Laboral</h3>

                            <div class="form-grid-3">
                                <div class="form-group span-3">
                                    <label for="primera_experiencia">¿A qué edad empezó a laborar y en qué?</label>
                                    <textarea id="primera_experiencia" name="primera_experiencia" class="input textarea" rows="2"></textarea>
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Última Empresa</h4>
                            <div class="form-grid-3">
                                <div class="form-group span-2">
                                    <label for="ultima_empresa">Nombre de la última empresa que laboró</label>
                                    <input type="text" id="ultima_empresa" name="ultima_empresa" class="input">
                                </div>
                                <div class="form-group">
                                    <label for="fecha_ultima_empresa">Fecha</label>
                                    <input type="date" id="fecha_ultima_empresa" name="fecha_ultima_empresa" class="input">
                                </div>
                                <div class="form-group span-3">
                                    <label for="que_hacia_ultima">¿Qué hacía?</label>
                                    <textarea id="que_hacia_ultima" name="que_hacia_ultima" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="tiempo_ultima">¿Cuánto tiempo trabajó?</label>
                                    <input type="text" id="tiempo_ultima" name="tiempo_ultima" class="input" placeholder="Ej: 2 años 3 meses">
                                </div>
                                <div class="form-group span-2">
                                    <label for="por_que_salio_ultima">¿Por qué salió?</label>
                                    <textarea id="por_que_salio_ultima" name="por_que_salio_ultima" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="impresion_ultima">¿Qué impresión tiene acerca de esa empresa?</label>
                                    <textarea id="impresion_ultima" name="impresion_ultima" class="input textarea" rows="2"></textarea>
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Otra Empresa</h4>
                            <div class="form-grid-3">
                                <div class="form-group span-2">
                                    <label for="otra_empresa">Otra empresa que laboró</label>
                                    <input type="text" id="otra_empresa" name="otra_empresa" class="input">
                                </div>
                                <div class="form-group">
                                    <label for="fecha_otra_empresa">Fecha</label>
                                    <input type="date" id="fecha_otra_empresa" name="fecha_otra_empresa" class="input">
                                </div>
                                <div class="form-group span-3">
                                    <label for="que_hacia_otra">¿Qué hacía?</label>
                                    <textarea id="que_hacia_otra" name="que_hacia_otra" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="tiempo_otra">¿Cuánto tiempo trabajó?</label>
                                    <input type="text" id="tiempo_otra" name="tiempo_otra" class="input">
                                </div>
                                <div class="form-group span-2">
                                    <label for="por_que_salio_otra">¿Por qué salió?</label>
                                    <textarea id="por_que_salio_otra" name="por_que_salio_otra" class="input textarea" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="tabla-dinamica-wrap">
                                <div class="tabla-dinamica-header">
                                    <span class="tabla-dinamica-titulo">Otras empresas (opcional)</span>
                                    <button type="button" class="btn-agregar-fila" id="btnAgregarEmpresa">+ Agregar empresa</button>
                                </div>
                                <div class="tabla-responsive">
                                    <table class="tabla-dinamica" id="tablaEmpresas">
                                        <thead>
                                            <tr>
                                                <th>Nombre empresa</th>
                                                <th>¿Qué hacía?</th>
                                                <th>Tiempo</th>
                                                <th>Fecha salida</th>
                                                <th>¿Por qué salió?</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="bodyEmpresas">
                                            <tr class="fila-vacia">
                                                <td colspan="6">Sin empresas adicionales. Haz clic en "+ Agregar empresa".</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="form-grid-3" style="margin-top:16px;">
                                <div class="form-group span-3">
                                    <label for="mayor_satisfaccion">¿A nivel laboral le ha generado mayor satisfacción? ¿Cómo lo logró?</label>
                                    <textarea id="mayor_satisfaccion" name="mayor_satisfaccion" class="input textarea" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 7: PERFIL ═══ -->
                    <div class="seccion-paso oculto" id="paso-7">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">🧠 Sección 7 — Información Personal y Perfil</h3>

                            <div class="form-grid-3">
                                <div class="form-group span-3">
                                    <label for="por_que_jardines">¿Por qué está interesado en trabajar en Jardines de San Nicolás S.A.S?</label>
                                    <textarea id="por_que_jardines" name="por_que_jardines" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="como_supo_vacante">¿Cómo se enteró de la vacante?</label>
                                    <textarea id="como_supo_vacante" name="como_supo_vacante" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>¿Tiene familiares o conocidos en Jardines de San Nicolás S.A.S?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_conocidos" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_conocidos" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="quien_recomendo">¿Quién lo recomendó?</label>
                                    <input type="text" id="quien_recomendo" name="quien_recomendo" class="input">
                                </div>
                                <div class="form-group">
                                    <label for="por_que_lo_conoce">¿Por qué lo conoce?</label>
                                    <input type="text" id="por_que_lo_conoce" name="por_que_lo_conoce" class="input">
                                </div>
                                <div class="form-group span-3">
                                    <label for="que_conoce_jardines">¿Qué conoce o ha escuchado mencionar de Jardines de San Nicolás S.A.S?</label>
                                    <textarea id="que_conoce_jardines" name="que_conoce_jardines" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Después de su jornada laboral, ¿realiza otras actividades?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="otras_actividades" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="otras_actividades" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_otras_actividades" style="display:none;">
                                    <label for="cuales_otras_actividades">¿Cuáles?</label>
                                    <input type="text" id="cuales_otras_actividades" name="cuales_otras_actividades" class="input">
                                </div>
                                <div class="form-group span-3">
                                    <label for="caracteristicas_equipo">¿Qué características le gustaría encontrar en su equipo de trabajo?</label>
                                    <textarea id="caracteristicas_equipo" name="caracteristicas_equipo" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="que_motiva">¿Qué le motiva a trabajar?</label>
                                    <textarea id="que_motiva" name="que_motiva" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>¿Actualmente se encuentra en otros procesos de selección?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="otros_procesos" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="otros_procesos" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_otros_procesos" style="display:none;">
                                    <label for="cuales_procesos">Especifique</label>
                                    <textarea id="cuales_procesos" name="cuales_procesos" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>¿Tiene alguna aspiración salarial?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="aspiracion_salarial" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="aspiracion_salarial" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_salario" style="display:none;">
                                    <label for="cuanto_salario">¿Cuál?</label>
                                    <input type="text" id="cuanto_salario" name="cuanto_salario" class="input" placeholder="Ej: $1.500.000">
                                </div>
                                <div class="form-group span-3">
                                    <label>¿En algún momento de su vida se ha visto involucrado en un evento o situación con alguna de las siguientes instituciones: Juzgados, comisarías, Inspecciones de Policía, entre otros?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="involucrado_instituciones" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="involucrado_instituciones" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-3" id="wrap_instituciones" style="display:none;">
                                    <label for="cuales_instituciones">¿Cuáles?</label>
                                    <textarea id="cuales_instituciones" name="cuales_instituciones" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label>Dentro de su grupo familiar, ¿cuenta con un PEP (Persona Expuesta Políticamente)?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_pep" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_pep" value="no"> No</label>
                                    </div>
                                </div>
                                <div id="wrap_pep" style="display:none;">
                                    <div class="form-grid-3">
                                        <div class="form-group">
                                            <label for="pep_nombre">Nombre</label>
                                            <input type="text" id="pep_nombre" name="pep_nombre" class="input">
                                        </div>
                                        <div class="form-group">
                                            <label for="pep_consanguinidad">Grado de consanguinidad</label>
                                            <input type="text" id="pep_consanguinidad" name="pep_consanguinidad" class="input">
                                        </div>
                                        <div class="form-group">
                                            <label for="pep_cargo">Cargo</label>
                                            <input type="text" id="pep_cargo" name="pep_cargo" class="input">
                                        </div>
                                        <div class="form-group span-3">
                                            <label for="pep_motivo">¿Motivo?</label>
                                            <textarea id="pep_motivo" name="pep_motivo" class="input textarea" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>¿Ha tenido sanciones disciplinarias?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="sanciones" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="sanciones" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_sanciones" style="display:none;">
                                    <label for="cual_sancion">¿Cuál?</label>
                                    <textarea id="cual_sancion" name="cual_sancion" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>¿Practica algún deporte?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="practica_deporte" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="practica_deporte" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group" id="wrap_deporte" style="display:none;">
                                    <label for="cual_deporte">¿Cuál?</label>
                                    <input type="text" id="cual_deporte" name="cual_deporte" class="input">
                                </div>
                                <div class="form-group" id="wrap_frecuencia_deporte" style="display:none;">
                                    <label for="frecuencia_deporte">Frecuencia</label>
                                    <input type="text" id="frecuencia_deporte" name="frecuencia_deporte" class="input">
                                </div>
                                <div class="form-group span-3">
                                    <label for="tiempo_libre">¿Qué le gusta hacer en su tiempo libre?</label>
                                    <textarea id="tiempo_libre" name="tiempo_libre" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>¿Cuenta con alguna habilidad para un arte o labor?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="tiene_arte" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="tiene_arte" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_arte" style="display:none;">
                                    <label for="cual_arte">¿Cuál?</label>
                                    <input type="text" id="cual_arte" name="cual_arte" class="input">
                                </div>
                                <div class="form-group">
                                    <label>¿Pertenece a algún grupo?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="pertenece_grupo" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="pertenece_grupo" value="no"> No</label>
                                    </div>
                                </div>
                                <div class="form-group span-2" id="wrap_grupo" style="display:none;">
                                    <label for="cual_grupo">Especifique</label>
                                    <input type="text" id="cual_grupo" name="cual_grupo" class="input">
                                </div>
                                <div class="form-group span-3">
                                    <label for="religion">¿Qué religión practica y con qué frecuencia?</label>
                                    <input type="text" id="religion" name="religion" class="input">
                                </div>
                                <div class="form-group span-3">
                                    <label for="fortalezas">¿Cuáles son sus fortalezas?</label>
                                    <textarea id="fortalezas" name="fortalezas" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="aspectos_mejorar">¿Cuáles son sus aspectos por mejorar?</label>
                                    <textarea id="aspectos_mejorar" name="aspectos_mejorar" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="suenos">¿Cuáles son sus principales sueños?</label>
                                    <textarea id="suenos" name="suenos" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="que_le_hace_enojar">¿Qué le hace enojar?</label>
                                    <textarea id="que_le_hace_enojar" name="que_le_hace_enojar" class="input textarea" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 8: PLANES E INTERESES ═══ -->
                    <div class="seccion-paso oculto" id="paso-8">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">🎯 Sección 8 — Planes e Intereses</h3>

                            <div class="form-grid-3">
                                <div class="form-group span-3">
                                    <label for="descripcion_companeros">¿Cómo cree que lo describirían sus compañeros o sus jefes?</label>
                                    <textarea id="descripcion_companeros" name="descripcion_companeros" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="sentido_pertenencia">¿Qué significa para usted tener sentido de pertenencia?</label>
                                    <textarea id="sentido_pertenencia" name="sentido_pertenencia" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="tiempo_proyectado">Planes e intereses: ¿Cuánto tiempo tiene proyectado quedarse con nosotros? ¿Qué planes laborales tiene a futuro?</label>
                                    <textarea id="tiempo_proyectado" name="tiempo_proyectado" class="input textarea" rows="3"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="compromiso">Si la empresa decide contratarlo, ¿a qué se comprometería?</label>
                                    <textarea id="compromiso" name="compromiso" class="input textarea" rows="2"></textarea>
                                </div>
                                <div class="form-group span-3">
                                    <label for="deuda_credito">¿Actualmente cuenta con una deuda o crédito?</label>
                                    <textarea id="deuda_credito" name="deuda_credito" class="input textarea" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 9: CONCEPTO DE PRUEBA DE CAMPO ═══ -->
                    <div class="seccion-paso oculto" id="paso-9">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">🔧 Sección 9 — Concepto de Prueba de Campo</h3>

                            <div class="form-grid-3">
                                <div class="form-group span-2">
                                    <label for="responsable_prueba">Responsable de la prueba</label>
                                    <input type="text" id="responsable_prueba" name="responsable_prueba" class="input" placeholder="Nombre completo">
                                </div>
                                <div class="form-group">
                                    <label>¿Aplica?</label>
                                    <div class="radio-group">
                                        <label class="radio-label"><input type="radio" name="aplica_prueba" value="si"> Sí</label>
                                        <label class="radio-label"><input type="radio" name="aplica_prueba" value="no"> No</label>
                                    </div>
                                </div>
                            </div>

                            <div id="wrap_detalle_prueba">
                                <div class="form-grid-3">
                                    <div class="form-group span-3">
                                        <label for="labor_prueba">Labor</label>
                                        <textarea id="labor_prueba" name="labor_prueba" class="input textarea" rows="2"></textarea>
                                    </div>
                                    <div class="form-group span-3">
                                        <label for="rendimiento_prueba">Rendimiento</label>
                                        <textarea id="rendimiento_prueba" name="rendimiento_prueba" class="input textarea" rows="2"></textarea>
                                    </div>
                                    <div class="form-group span-3">
                                        <label for="calidad_prueba">Calidad</label>
                                        <textarea id="calidad_prueba" name="calidad_prueba" class="input textarea" rows="2"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>En el momento de la prueba, ¿cumplió las indicaciones dadas?</label>
                                        <div class="radio-group">
                                            <label class="radio-label"><input type="radio" name="cumplio_indicaciones" value="si"> Sí</label>
                                            <label class="radio-label"><input type="radio" name="cumplio_indicaciones" value="no"> No</label>
                                        </div>
                                    </div>
                                    <div class="form-group span-2">
                                        <label for="porque_cumplio">¿Por qué?</label>
                                        <input type="text" id="porque_cumplio" name="porque_cumplio" class="input">
                                    </div>
                                    <div class="form-group span-2">
                                        <label for="cargo_responsable_prueba">Cargo del responsable de la prueba</label>
                                        <input type="text" id="cargo_responsable_prueba" name="cargo_responsable_prueba" class="input">
                                    </div>
                                    <div class="form-group span-3">
                                        <label for="observaciones_prueba">Observaciones frente a la prueba y el aspirante</label>
                                        <textarea id="observaciones_prueba" name="observaciones_prueba" class="input textarea" rows="3"></textarea>
                                    </div>
                                </div>

                                <h4 class="subseccion-titulo">Firma del responsable de la prueba</h4>
                                <div class="firma-bloque-preingreso">
                                    <div class="canvas-wrap-pi" id="wrap_firma_prueba">
                                        <canvas id="canvas_firma_prueba"></canvas>
                                        <span class="canvas-hint-pi">Firme aquí</span>
                                    </div>
                                    <div class="firma-acciones-pi">
                                        <button type="button" class="btn-limpiar-pi" id="limpiar_firma_prueba">✕ Editar</button>
                                        <span class="firma-estado-pi" id="estado_firma_prueba">Sin firma</span>
                                    </div>
                                    <input type="hidden" name="firma_prueba" id="firma_prueba_data">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 10: CERTIFICACIÓN ═══ -->
                    <div class="seccion-paso oculto" id="paso-10">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">✅ Sección 10 — Certificación</h3>

                            <div class="declaracion-box">
                                <p>📄 <strong>Declaración:</strong> "Declaro que la información suministrada es verídica y que autorizo a Jardines de San Nicolás S.A.S. a verificar los datos aquí consignados."</p>
                            </div>

                            <div class="form-grid-3" style="margin-top:20px;">
                                <div class="form-group span-2">
                                    <label for="nombre_certifica">Nombre completo <span class="req">*</span></label>
                                    <input type="text" id="nombre_certifica" name="nombre_certifica" class="input" required placeholder="Nombre de quien suministra la información">
                                </div>
                                <div class="form-group">
                                    <label for="cedula_certifica">Identificado con C.C. <span class="req">*</span></label>
                                    <input type="text" id="cedula_certifica" name="cedula_certifica" class="input" required placeholder="Número de cédula" inputmode="numeric" pattern="[0-9]*" oninput="soloNumeros(this)">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Firma de quien suministra la información</h4>
                            <p class="constancia-firma" style="margin:0 0 14px;padding:12px 16px;background:#faf5f5;border-left:3px solid #780606;border-radius:4px;font-size:.9rem;line-height:1.7;color:#2d3748;text-align:justify;">
                                En constancia de lo anterior, el candidato manifiesta que suscribe el presente documento
                                mediante firma a través de la plataforma dispuesta por la Compañía para tal efecto, la cual
                                tiene plena validez y efectos jurídicos.
                            </p>
                            <div class="firma-bloque-preingreso">
                                <div class="canvas-wrap-pi" id="wrap_firma_aspirante">
                                    <canvas id="canvas_firma_aspirante"></canvas>
                                    <span class="canvas-hint-pi">Firme aquí</span>
                                </div>
                                <div class="firma-acciones-pi">
                                    <button type="button" class="btn-limpiar-pi" id="limpiar_firma_aspirante">✕ Editar</button>
                                    <span class="firma-estado-pi" id="estado_firma_aspirante">Sin firma</span>
                                </div>
                                <input type="hidden" name="firma_aspirante" id="firma_aspirante_data">
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECCIÓN 11: CONCLUSIONES ═══ -->
                    <div class="seccion-paso oculto" id="paso-11">
                        <div class="card-seccion">
                            <h3 class="seccion-titulo">📊 Sección 11 — Conclusiones de la Entrevista</h3>

                            <div class="aviso-rrhh">🔒 Esta sección es de uso exclusivo del entrevistador / RRHH</div>

                            <div class="form-grid-3" style="margin-top:18px;">
                                <div class="form-group span-3">
                                    <label for="observaciones_entrevistador">Observaciones del entrevistador</label>
                                    <textarea id="observaciones_entrevistador" name="observaciones_entrevistador" class="input textarea" rows="4"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="resultado">Apto <span class="req">*</span></label>
                                    <select id="resultado" name="resultado" class="input" required>
                                        <option value="">Selecciona...</option>
                                        <option value="apto">✅ Apto</option>
                                        <option value="no_apto">❌ No apto</option>
                                        <option value="pendiente">⏳ Pendiente</option>
                                    </select>
                                </div>
                                <div class="form-group span-2">
                                    <label for="cargo_entrevistador">Cargo del entrevistador</label>
                                    <input type="text" id="cargo_entrevistador" name="cargo_entrevistador" class="input">
                                </div>
                            </div>

                            <h4 class="subseccion-titulo">Firma del entrevistador</h4>
                            <div class="firma-bloque-preingreso">
                                <div class="canvas-wrap-pi" id="wrap_firma_entrevistador">
                                    <canvas id="canvas_firma_entrevistador"></canvas>
                                    <span class="canvas-hint-pi">Firme aquí</span>
                                </div>
                                <div class="firma-acciones-pi">
                                    <button type="button" class="btn-limpiar-pi" id="limpiar_firma_entrevistador">✕ Editar</button>
                                    <span class="firma-estado-pi" id="estado_firma_entrevistador">Sin firma</span>
                                </div>
                                <input type="hidden" name="firma_entrevistador" id="firma_entrevistador_data">
                            </div>
                        </div>
                    </div>

                    <!-- ── Navegación ── -->
                    <div class="nav-pasos">
                        <button type="button" class="btn btn-cancelar" id="btnAnterior" style="display:none;">&#8592; Anterior</button>
                        <div class="nav-pasos-derecha">
                            <span class="paso-contador" id="pasoContador">Paso 1 de 11</span>
                            <button type="button" class="btn btn-guardar" id="btnSiguiente">Siguiente &#8594;</button>
                            <button type="button" class="btn btn-guardar" id="btnGuardar" style="display:none;">&#10003; Guardar</button>
                        </div>
                    </div>

                </form>
            </section>
        </main>
    </div>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

    <script src="../JS/ui-common.js"></script>
    <script src="../JS/firma-canvas.js"></script>
    <script src="../JS/PreingresoJN.js"></script>
    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>
<script src="../JS/demo.js"></script>
</body>

</html>
