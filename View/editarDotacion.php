<?php require_once __DIR__ . '/../Config/demo.php'; ?>
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../View/login.php");
    exit();
}
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: Dotacion.php");
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
    <title>Editar Dotación – Jardines de San Nicolás</title>
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
                    <h2>Editar Dotación</h2>
                </div>

                <div id="mensaje-resultado" class="mensaje-oculto"></div>
                <div id="contenido-editar">
                    <div style="text-align:center;color:#718096;padding:60px 0;">Cargando datos...</div>
                </div>
            </section>
        </main>
    </div>

    <script>
        var REGISTRO_ID = <?php echo $id; ?>;
        // Calculada dinámicamente — ver el comentario equivalente en Verdotacion.php
        // sobre por qué window.location.origin + '/' a secas rompe las firmas
        // cuando la app vive en una subcarpeta (ej. /GestorDocumentosJN/).
        var BASE_URL = window.location.origin + window.location.pathname.split('/View/')[0] + '/';

        // Ver el comentario equivalente en Verdotacion.php — parseInt(true) da NaN.
        function esVerdadero(v) {
            return v === true || v === 1 || v === '1';
        }

        fetch('../Controller/DotacionController.php?action=obtener&id=' + REGISTRO_ID)
            .then(function(r) {
                return r.json();
            })
            .then(function(d) {
                if (d.error) {
                    mostrarError(d.error);
                    return;
                }
                construirFormulario(d);
            })
            .catch(function() {
                mostrarError('Error de conexión.');
            });

        function construirFormulario(r) {
            var fecha = r.fecha_entrega ? r.fecha_entrega.split('T')[0] : '';
            var registroBloqueado =
                r.firma_trabajador &&
                r.firma_encargado;
            var nombreMostrar = r.nombre_empleado || r.codigo || '—';
            var centroMostrar = r.nombre_centro_costo || '—';

            var tallasRopa = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
            var tallasPantalon = ['4', '6', '8', '10', '12', '14', '16', '18', '20', '22', '24', '26', '28', '30', '32', '34', '36', '38', '40', '42', '44', '46'];
            var tallasCalzado = ['34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];

            // ── Helper: genera un bloque de prenda completo ───────────────
            function bloquePrend(campo, labelHTML, tallas, tiene, tallaActual, generoPrenda) {
                var activa = esVerdadero(tiene);
                var opts = tallas.map(function(v) {
                    return '<option value="' + v + '"' + (v == tallaActual ? ' selected' : '') + '>' + v + '</option>';
                }).join('');
                // "Sexo opuesto" está marcado si el género ya resuelto de esta prenda
                // es distinto del género de la persona (r.genero).
                var sexoOpuestoMarcado = activa && generoPrenda && r.genero && generoPrenda !== r.genero;
                return '<div class="prenda-bloque' + (activa ? ' activa' : '') + '">' +
                    '<div class="prenda-header"><label class="prenda-label">' +
                    '<input type="checkbox" name="' + campo + '" id="check_' + campo + '" class="check-prenda"' + (activa ? ' checked' : '') + '>' +
                    '<span class="prenda-nombre">' + labelHTML + '</span>' +
                    '</label></div>' +
                    '<div class="prenda-talla"><label for="' + campo + '_talla">Talla</label>' +
                    '<select name="' + campo + '_talla" id="' + campo + '_talla" class="input select-talla"' + (!activa ? ' disabled' : '') + '>' +
                    '<option value="">Selecciona</option>' + opts +
                    '</select></div>' +
                    '<div class="prenda-sexo-opuesto">' +
                    '<input type="checkbox" id="sexo_opuesto_' + campo + '" class="check-sexo-opuesto"' + (!activa ? ' disabled' : '') + (sexoOpuestoMarcado ? ' checked' : '') + '>' +
                    '<label for="sexo_opuesto_' + campo + '">Sexo opuesto</label>' +
                    '</div></div>';
            }

            var adminBadge = '<small class="prenda-badge-admin">Adm.</small>';

            // ── Armar sección de prendas ──────────────────────────────────
            var prendasHTML =
                '<p class="prendas-grupo-label">🧥 Ropa de trabajo</p>' +
                '<div class="prendas-grid">' +
                bloquePrend('camisa', 'Camibuso', tallasRopa, r.camisa, r.camisa_talla, r.camisa_genero) +
                bloquePrend('camisa_oxford', 'Camisa Oxford ' + adminBadge, tallasRopa, r.camisa_oxford, r.camisa_oxford_talla, r.camisa_oxford_genero) +
                bloquePrend('camisa_polo', 'Camisa Polo ' + adminBadge, tallasRopa, r.camisa_polo, r.camisa_polo_talla, r.camisa_polo_genero) +
                bloquePrend('pantalon', 'Pantalón', tallasPantalon, r.pantalon, r.pantalon_talla, r.pantalon_genero) +
                bloquePrend('sudadera', 'Sudadera', tallasPantalon, r.sudadera, r.sudadera_talla, r.sudadera_genero) +
                bloquePrend('jean', 'Jean', tallasPantalon, r.jean, r.jean_talla, r.jean_genero) +
                bloquePrend('overol', 'Overol', tallasPantalon, r.overol, r.overol_talla, r.overol_genero) +
                '</div>' +
                '<p class="prendas-grupo-label" style="margin-top:20px;">👟 Zapatos</p>' +
                '<div class="prendas-grid">' +
                bloquePrend('zapatos_seguridad', 'Zapatos Seguridad', tallasCalzado, r.zapatos_seguridad, r.zapatos_seguridad_talla, r.zapatos_seguridad_genero) +
                bloquePrend('zapatos', 'Zapatos', tallasCalzado, r.zapatos, r.zapatos_talla, r.zapatos_genero) +
                bloquePrend('botas', 'Botas', tallasCalzado, r.botas, r.botas_talla, r.botas_genero) +
                '</div>';

            // ── Armar firma ───────────────────────────────────────────────
            function firmaActual(ruta, label, campo) {
                return '<div class="firma-bloque">' +
                    '<label class="firma-label">' + label + '</label>' +
                    (ruta ?
                        '<div style="margin-bottom:8px;"><img src="' + BASE_URL + ruta + '" style="max-height:80px;border:1px solid #e2e8f0;border-radius:6px;padding:4px;" alt="Firma actual"></div>' :
                        '<p style="font-size:0.82rem;color:#a0aec0;margin-bottom:8px;">Sin firma actual</p>'
                    ) +
                    '<div class="canvas-wrap" id="wrap_' + campo + '">' +
                    '<canvas id="canvas_' + campo + '"></canvas>' +
                    '<span class="canvas-hint">Dibuja aquí para reemplazar la firma</span>' +
                    '</div>' +
                    '<div class="firma-acciones">' +
                    '<button type="button" class="btn btn-limpiar" id="limpiar_' + campo + '">&#10005; Limpiar</button>' +
                    '<span class="firma-estado" id="estado_' + campo + '">Sin cambios</span>' +
                    '</div>' +
                    '<input type="hidden" name="firma_' + campo + '" id="firma_' + campo + '_data">' +
                    '</div>';
            }

            // ── Inyectar formulario completo ──────────────────────────────
            document.getElementById('contenido-editar').innerHTML =
                '<form id="form-editar" novalidate>' +

                (registroBloqueado ?

                    '<div style="background:#FEF3C7;border:1px solid #F59E0B;color:#92400E;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:600;">🔒 Este registro ya fue firmado por ambas partes y no puede modificarse.</div>'

                    :
                    '') +
                '<input type="hidden" name="id" value="' + r.id + '">' +

                // Datos del trabajador
                '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Datos del Trabajador <small style="color:#a0aec0;font-weight:400;">(no editables)</small></h3>' +
                '<div class="form-grid">' +
                '<div class="form-group"><label>Código</label>' +
                '<input type="text" class="input input-readonly" value="' + r.codigo + '" readonly></div>' +
                '<div class="form-group"><label>Nombre</label>' +
                '<input type="text" class="input input-readonly" value="' + nombreMostrar + '" readonly></div>' +
                '<div class="form-group"><label>Centro de Costos</label>' +
                '<input type="text" class="input input-readonly" value="' + centroMostrar + '" readonly></div>' +
                '<div class="form-group"><label for="fecha_entrega">Fecha de Entrega <span class="requerido">*</span></label>' +
                '<input type="date" id="fecha_entrega" name="fecha_entrega" class="input" value="' + fecha + '" required></div>' +
                '<div class="form-group"><label for="genero">Género <span class="requerido">*</span></label>' +
                '<select name="genero" id="genero" class="input">' +
                '<option value="">Selecciona</option>' +
                '<option value="M"' + (r.genero === 'M' ? ' selected' : '') + '>Masculino</option>' +
                '<option value="F"' + (r.genero === 'F' ? ' selected' : '') + '>Femenino</option>' +
                '</select></div>' +
                '<div class="form-group"><label for="cambio_carnet_deterioro">Cambio de carnet por deterioro</label>' +
                '<select name="cambio_carnet_deterioro" id="cambio_carnet_deterioro" class="input">' +
                '<option value="">Selecciona</option>' +
                '<option value="si"' + (['si', 'sí', '1', 'true'].includes(String(r.cambio_carnet_deterioro).toLowerCase()) ? ' selected' : '') + '>Sí</option>' +
                '<option value="no"' + (['no', '0', 'false'].includes(String(r.cambio_carnet_deterioro).toLowerCase()) ? ' selected' : '') + '>No</option>' +
                '</select></div>' +
                '</div></div>' +

                // Prendas
                '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Prendas Entregadas</h3>' +
                prendasHTML +
                '</div>' +

                // Firmas
                '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Firmas</h3>' +
                '<p style="font-size:0.85rem;color:#718096;margin-bottom:16px;">Si ya hay una firma guardada se muestra abajo. Dibuja en el canvas para reemplazarla.</p>' +
                '<div class="firmas-grid">' +
                firmaActual(r.firma_trabajador, 'Firma del Trabajador', 'trabajador') +
                firmaActual(r.firma_encargado, 'Firma del Encargado', 'encargado') +
                '</div></div>' +


                // Observaciones
                '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Observaciones</h3>' +
                '<div class="form-group">' +
                '<label for="observaciones">Observaciones <small style="color:#a0aec0;">(opcional)</small></label>' +
                '<textarea id="observaciones" name="observaciones" class="input" rows="4" maxlength="500"' +
                ' placeholder="Escribe cualquier observación relevante sobre esta dotación..."' +
                ' style="resize:vertical;width:100%;font-family:inherit;font-size:0.95rem;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;color:#2d3748;line-height:1.5;">' +
                (r.observaciones ? escapeHtml(r.observaciones) : '') +
                '</textarea>' +
                '<small style="color:#a0aec0;display:block;margin-top:4px;">Máximo 500 caracteres</small>' +
                '</div></div>' +

                // Botones
                '<div class="botones-wrap">' +
                '<button type="submit" class="btn btn-guardar" id="btn-guardar">&#10003; Guardar Cambios</button>' +
                '<a href="Dotacion.php" class="btn btn-cancelar">&#10005; Cancelar</a>' +
                '</div>' +
                '</form>';

            // ── Activar todos los checkboxes ──────────────────────────────
            var NOMBRES_PRENDAS = ['camisa', 'camisa_oxford', 'camisa_polo', 'pantalon', 'sudadera',
                'jean', 'overol', 'zapatos_seguridad', 'zapatos', 'botas'
            ];
            NOMBRES_PRENDAS.forEach(function(nombre) {
                var chk = document.getElementById('check_' + nombre);
                var sel = document.getElementById(nombre + '_talla');
                var chkSexoOpuesto = document.getElementById('sexo_opuesto_' + nombre);
                if (!chk || !sel) return;
                var bloque = chk.closest('.prenda-bloque');
                chk.addEventListener('change', function() {
                    sel.disabled = !this.checked;
                    if (!this.checked) sel.value = '';
                    bloque.classList.toggle('activa', this.checked);
                    if (chkSexoOpuesto) {
                        chkSexoOpuesto.disabled = !this.checked;
                        if (!this.checked) chkSexoOpuesto.checked = false;
                    }
                });
            });

            var opuesto = {
                M: 'F',
                F: 'M'
            };

            function generoResueltoPrenda(nombrePrenda) {
                var genero = document.getElementById('genero').value;
                var chkSexoOpuesto = document.getElementById('sexo_opuesto_' + nombrePrenda);
                if (!genero) return '';
                return chkSexoOpuesto && chkSexoOpuesto.checked ? opuesto[genero] : genero;
            }

            // ── Activar canvas de firmas ──────────────────────────────────
            iniciarCanvas('canvas_trabajador', 'firma_trabajador_data', 'limpiar_trabajador', 'estado_trabajador', 'wrap_trabajador');
            iniciarCanvas('canvas_encargado', 'firma_encargado_data', 'limpiar_encargado', 'estado_encargado', 'wrap_encargado');
            if (registroBloqueado) {
                bloquearFormulario();
            }
            // ── Envío del formulario ──────────────────────────────────────
            document.getElementById('form-editar').addEventListener('submit', function(e) {
                e.preventDefault();

                var fecha = document.getElementById('fecha_entrega').value;
                if (!fecha) {
                    mostrarMensaje('Por favor selecciona la fecha de entrega.', 'error');
                    return;
                }

                var campoGenero = document.getElementById('genero');
                if (!campoGenero || !campoGenero.value) {
                    mostrarMensaje('Por favor selecciona el género de la persona.', 'error');
                    return;
                }

                // Validar que al menos una prenda esté marcada
                var checksPrendas = ['check_camisa', 'check_camisa_oxford', 'check_camisa_polo',
                    'check_pantalon', 'check_sudadera', 'check_jean', 'check_overol',
                    'check_zapatos_seguridad', 'check_zapatos', 'check_botas'
                ];
                var algunaMarcada = checksPrendas.some(function(id) {
                    var el = document.getElementById(id);
                    return el && el.checked;
                });
                if (!algunaMarcada) {
                    mostrarMensaje('Debes marcar al menos una prenda para guardar el registro.', 'error');
                    return;
                }

                // Validar talla de cada prenda marcada
                var validaciones = [{
                        check: 'check_camisa',
                        talla: 'camisa_talla',
                        nombre: 'camisa'
                    },
                    {
                        check: 'check_camisa_oxford',
                        talla: 'camisa_oxford_talla',
                        nombre: 'camisa oxford'
                    },
                    {
                        check: 'check_camisa_polo',
                        talla: 'camisa_polo_talla',
                        nombre: 'camisa polo'
                    },
                    {
                        check: 'check_pantalon',
                        talla: 'pantalon_talla',
                        nombre: 'pantalón'
                    },
                    {
                        check: 'check_sudadera',
                        talla: 'sudadera_talla',
                        nombre: 'sudadera'
                    },
                    {
                        check: 'check_jean',
                        talla: 'jean_talla',
                        nombre: 'jean'
                    },
                    {
                        check: 'check_overol',
                        talla: 'overol_talla',
                        nombre: 'overol'
                    },
                    {
                        check: 'check_zapatos_seguridad',
                        talla: 'zapatos_seguridad_talla',
                        nombre: 'zapatos seguridad'
                    },
                    {
                        check: 'check_zapatos',
                        talla: 'zapatos_talla',
                        nombre: 'zapatos'
                    },
                    {
                        check: 'check_botas',
                        talla: 'botas_talla',
                        nombre: 'botas'
                    },
                ];
                for (var i = 0; i < validaciones.length; i++) {
                    var v = validaciones[i];
                    var chk = document.getElementById(v.check);
                    if (chk && chk.checked && !document.getElementById(v.talla).value) {
                        mostrarMensaje('Selecciona la talla de ' + v.nombre + '.', 'error');
                        return;
                    }
                }

                var btn = document.getElementById('btn-guardar');
                btn.disabled = true;
                btn.textContent = 'Guardando...';

                var formData = new FormData(this);

                NOMBRES_PRENDAS.forEach(function(nombre) {
                    formData.append(nombre + '_genero', generoResueltoPrenda(nombre));
                });

                fetch('../Controller/DotacionController.php?action=actualizar', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            mostrarMensaje('✅ ' + data.message, 'exito');
                            setTimeout(function() {
                                window.location.href = 'Dotacion.php';
                            }, 1800);
                        } else if (data.error === 'duplicado') {
                            mostrarMensaje('⚠️ ' + data.message, 'aviso');
                            btn.disabled = false;
                            btn.innerHTML = '&#10003; Guardar Cambios';
                        } else {
                            mostrarMensaje('❌ ' + (data.error || 'Error al guardar'), 'error');
                            btn.disabled = false;
                            btn.innerHTML = '&#10003; Guardar Cambios';
                        }
                    })
                    .catch(function() {
                        mostrarMensaje('❌ Error de conexión.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '&#10003; Guardar Cambios';
                    });
            });
        }

        // ── Canvas de firmas (JS/firma-canvas.js) ───────────────────────────────
        function iniciarCanvas(canvasId, hiddenId, limpiarId, estadoId, wrapId) {
            return initFirmaCanvas({
                canvas: canvasId,
                hidden: hiddenId,
                btnLimpiar: limpiarId,
                estado: estadoId,
                wrap: wrapId,
                claseActivo: 'activo',
                textoFirmado: '✔ Nueva firma',
                textoVacio: 'Sin cambios',
                formato: 'image/png',
                capturaEnVivo: false,
                capturaEnSuelta: true,
                dpiAware: false,
            });
        }

        function bloquearFormulario() {

            document.querySelectorAll(
                '#form-editar input, #form-editar select, #form-editar textarea'
            ).forEach(function(el) {

                if (el.type === 'hidden') return;

                if (el.tagName === 'TEXTAREA') {
                    el.readOnly = true;
                } else {
                    el.disabled = true;
                }

            });

            document.querySelectorAll(
                '#form-editar canvas'
            ).forEach(function(canvas) {

                canvas.style.pointerEvents = 'none';
                canvas.style.opacity = '.6';

            });

            document.querySelectorAll(
                '.btn-limpiar'
            ).forEach(function(btn) {

                btn.disabled = true;

            });

            var guardar = document.getElementById('btn-guardar');

            if (guardar) {
                guardar.style.display = 'none';
            }

        }

        // ── Helpers ───────────────────────────────────────────────────────────
        function mostrarMensaje(texto, tipo) {
            var div = document.getElementById('mensaje-resultado');
            var claseCSS = tipo === 'exito' ? 'mensaje-exito' : tipo === 'aviso' ? 'mensaje-aviso' : 'mensaje-error';
            div.textContent = texto;
            div.className = claseCSS;
            div.style.display = 'block';
            div.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function mostrarError(msg) {
            document.getElementById('contenido-editar').innerHTML =
                '<div style="text-align:center;color:#e53e3e;padding:40px;">❌ ' + msg + '</div>';
        }
    </script>
    <script src="../JS/ui-common.js"></script>
    <script src="../JS/Script.js"></script>
    <script src="../JS/firma-canvas.js"></script>
    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
