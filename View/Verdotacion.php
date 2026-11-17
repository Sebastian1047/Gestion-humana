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
    <title>Ver Dotación – Jardines de San Nicolás</title>
    <link rel="stylesheet" href="../CSS/variables.css" />
    <link rel="stylesheet" href="../CSS/StyleDotacion.css" />
    <link rel="stylesheet" href="../CSS/NewDotStyle.css" />
    <style>
        /* Comprobante individual de dotación: compacto para impresión A4. */
        #print-doc .condiciones-dotacion {
            margin: 0 0 12px;
            color: #1e293b;
            font-size: 9pt;
            line-height: 1.28;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        #print-doc .condiciones-dotacion h2,
        #print-doc .condiciones-dotacion h3 {
            font-size: 9pt;
            font-weight: 700;
            margin: 0 0 5px;
            text-transform: uppercase;
        }

        #print-doc .condiciones-dotacion h3 {
            margin-top: 7px;
        }

        #print-doc .condiciones-dotacion p {
            margin: 0 0 5px;
        }

        @page {
            size: A4;
            margin: 7mm;
        }

        @media print {
            #print-doc {
                max-width: none !important;
                padding: 6px 12px !important;
            }

            #print-doc table {
                min-width: 0;
            }

            #print-doc th,
            #print-doc td {
                white-space: normal;
            }
        }
    </style>

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
                    <h2>Detalle de Dotación</h2>
                </div>
                <div id="contenido">
                    <div style="text-align:center;color:#718096;padding:60px 0;">Cargando registro...</div>
                </div>
                <div id="print-doc"></div>
            </section>
        </main>
    </div>

    <script>
        var REGISTRO_ID = <?php echo $id; ?>;
        // Calculada dinámicamente (no window.location.origin + '/' a secas):
        // esta vista vive en View/, y la app puede estar publicada en la raíz
        // de su propio sitio o en una subcarpeta de otro (ej. /GestorDocumentosJN/
        // dentro de "Default Web Site", junto a FlowerLogixProduccion) — con la
        // raíz fija, las firmas se pedían sin el prefijo de la subcarpeta y daban 404.
        var BASE_URL = window.location.origin + window.location.pathname.split('/View/')[0] + '/';

        // Lee un campo booleano de la API sin asumir cómo llega representado.
        // La API en .NET manda JSON real (true/false) para camisa, entrega_confirmada,
        // etc. — parseInt(true) da NaN (falso), así que "esVerdadero(r.campo)" rompía todo
        // esto en silencio. true/1/"1" cuentan como verdadero, cualquier otra cosa no.
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
                renderDetalle(d);
                renderDocImpresion(d);
                activarConfirmarEntrega(d);
            })
            .catch(function() {
                mostrarError('Error de conexión.');
            });

        // ── Render pantalla ───────────────────────────────────────────────────
        function renderDetalle(r) {
            var nombre = r.nombre_empleado || r.nombre || '—';
            var centroCosto = r.nombre_centro_costo || r.centro_costos || '—';
            var fechaIngresoDato = r.fecha_ingreso || r.fechaIngreso;
            var fechaIngreso = fechaIngresoDato ? String(fechaIngresoDato).split('T')[0] : '—';
            var docId = r.docId || '—';

            function tagSi(nombre, talla) {
                return '<span class="prenda-tag si">✔ ' + nombre + (talla ? ' — Talla ' + talla : '') + '</span>';
            }

            function tagNo() {
                return '<span class="prenda-tag no">No se entrega</span>';
            }

            function firmaHTML(ruta) {
                return ruta ?
                    '<img src="' + BASE_URL + ruta + '" alt="Firma">' :
                    '<span class="firma-vacia">Sin firma registrada</span>';
            }

            // Ropa — solo las que tienen, si ninguna → "No se entrega"
            var ropaItems = '';
            if (esVerdadero(r.camisa)) ropaItems += tagSi('Camibuso', r.camisa_talla);
            if (esVerdadero(r.camisa_oxford)) ropaItems += tagSi('Camisa Oxford', r.camisa_oxford_talla);
            if (esVerdadero(r.camisa_polo)) ropaItems += tagSi('Camisa Polo', r.camisa_polo_talla);
            if (esVerdadero(r.pantalon)) ropaItems += tagSi('Pantalón', r.pantalon_talla);
            if (esVerdadero(r.sudadera)) ropaItems += tagSi('Sudadera', r.sudadera_talla);
            if (esVerdadero(r.jean)) ropaItems += tagSi('Jean', r.jean_talla);
            if (esVerdadero(r.overol)) ropaItems += tagSi('Overol', r.overol_talla);
            if (!ropaItems) ropaItems = tagNo();

            // Zapatos — solo el que tiene, si ninguno → "No se entrega"
            var calzadoItems = '';
            if (esVerdadero(r.zapatos_seguridad)) calzadoItems += tagSi('Zapatos Seguridad', r.zapatos_seguridad_talla);
            if (esVerdadero(r.zapatos)) calzadoItems += tagSi('Zapatos', r.zapatos_talla);
            if (esVerdadero(r.botas)) calzadoItems += tagSi('Botas', r.botas_talla);
            if (!calzadoItems) calzadoItems = tagNo();

            var prendasHTML =
                '<div class="prendas-grupo-titulo">🧥 Ropa de trabajo</div>' + ropaItems +
                '<div class="prendas-grupo-titulo" style="margin-top:14px;">👟 Zapatos</div>' + calzadoItems;

            document.getElementById('contenido').innerHTML =
                '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Datos del Trabajador</h3>' +
                '<div class="detalle-grid">' +
                '<div class="detalle-campo"><label>Código</label><span>' + r.codigo + '</span></div>' +
                '<div class="detalle-campo"><label>Nombre</label><span>' + nombre + '</span></div>' +
                '<div class="detalle-campo"><label>Centro de Costos</label><span>' + centroCosto + '</span></div>' +
                '<div class="detalle-campo"><label>Fecha de ingreso</label><span>' + fechaIngreso + '</span></div>' +
                '<div class="detalle-campo"><label>Documento</label><span>' + docId + '</span></div>' +
                '<div class="detalle-campo"><label>Cambio de carnet por deterioro</label><span>' +
                (['si', 'sí', '1', 'true'].includes(String(r.cambio_carnet_deterioro).toLowerCase()) ? 'Sí' :
                    ['no', '0', 'false'].includes(String(r.cambio_carnet_deterioro).toLowerCase()) ? 'No' : 'No registrado') +
                '</span></div>' +
                (esVerdadero(r.entrega_confirmada) ? '<div class="detalle-campo"><label>Entregado en</label><span>' + (r.fecha_confirmacion_entrega ? r.fecha_confirmacion_entrega.split('T')[0] : '—') + '</span></div>' : '') +
                '</div></div>' +

                '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Prendas Entregadas</h3>' +
                '<div class="prendas-detalle">' + prendasHTML + '</div>' +
                '</div>' +

                '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Firmas</h3>' +
                '<div class="firmas-detalle">' +
                '<div class="firma-detalle-bloque"><label>Firma del Trabajador</label>' +
                '<div class="firma-img-wrap">' + firmaHTML(r.firma_trabajador) + '</div></div>' +
                '<div class="firma-detalle-bloque"><label>Firma del Encargado</label>' +
                '<div class="firma-img-wrap">' + firmaHTML(r.firma_encargado) + '</div></div>' +
                '</div></div>' +

                bloqueConfirmarEntrega(r) +

                '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Observaciones</h3>' +
                '<div class="detalle-campo">' +
                '<span style="white-space:pre-wrap;">' +
                (r.observaciones ? escapeHtml(r.observaciones) : '<em style="color:#a0aec0;">Sin observaciones registradas</em>') +
                '</span>' +
                '</div></div>' +

                '<div class="botones-wrap">' +
                '<a href="editarDotacion.php?id=' + r.id + '" class="btn btn-guardar">&#9998; Editar</a>' +
                '<button onclick="window.print()" class="btn btn-accion btn-imprimir" style="padding:10px 24px;font-size:.9rem;">&#128438; Imprimir</button>' +
                '<a href="javascript:history.back()" class="btn btn-cancelar">&#8592; Volver</a>' +
                '</div>';
        }

        // ── Render documento de impresión ─────────────────────────────────────
        function renderDocImpresion(r) {
            var nombre = r.nombre_empleado || r.nombre || '—';
            var centroCosto = r.nombre_centro_costo || r.centro_costos || '—';
            var fechaIngresoDato = r.fecha_ingreso || r.fechaIngreso;
            var fechaIngreso = fechaIngresoDato ? String(fechaIngresoDato).split('T')[0] : '—';
            var fechaEntrega = r.fecha_entrega ? String(r.fecha_entrega).split('T')[0] : '—';
            var docId = r.docId || '—';

            // Solo se llama cuando la prenda SÍ se entrega
            function filaPrenda(nombre, talla) {
                return '<tr>' +
                    '<td style="padding:7px 10px;border:1px solid #ccc;">' + nombre + '</td>' +
                    '<td style="padding:7px 10px;border:1px solid #ccc;text-align:center;font-weight:700;color:#1a7a3c;">SÍ</td>' +
                    '<td style="padding:7px 10px;border:1px solid #ccc;text-align:center;">' + (talla || '—') + '</td>' +
                    '</tr>';
            }

            function filaNoEntrega(texto) {
                return '<tr>' +
                    '<td colspan="3" style="padding:7px 10px;border:1px solid #ccc;color:#999;font-style:italic;">' + texto + '</td>' +
                    '</tr>';
            }

            function grupoImpresion(titulo) {
                return '<tr style="background:#f5eaea;">' +
                    '<td colspan="3" style="padding:5px 10px;border:1px solid #ccc;font-weight:700;font-size:9pt;color:#5c0404;">' + titulo + '</td>' +
                    '</tr>';
            }

            function firmaImpresion(ruta, label) {
                var img = ruta ?
                    '<img src="' + BASE_URL + ruta + '" alt="' + label + '" style="max-width:180px;max-height:80px;object-fit:contain;display:block;margin:0 auto;">' :
                    '<span style="color:#aaa;font-style:italic;font-size:9pt;">Sin firma registrada</span>';
                return '<div style="flex:1;text-align:center;">' +
                    '<div style="border:1px solid #ccc;border-radius:6px;padding:12px;min-height:90px;display:flex;align-items:center;justify-content:center;background:#fafafa;margin-bottom:8px;">' + img + '</div>' +
                    '<div style="font-size:9pt;font-weight:700;color:#444;border-top:1px solid #888;padding-top:4px;">' + label + '</div>' +
                    '</div>';
            }

            // Ropa — solo filas de las que se entregan
            var filasRopa = '';
            if (esVerdadero(r.camisa)) filasRopa += filaPrenda('Camibuso', r.camisa_talla);
            if (esVerdadero(r.camisa_oxford)) filasRopa += filaPrenda('Camisa Oxford', r.camisa_oxford_talla);
            if (esVerdadero(r.camisa_polo)) filasRopa += filaPrenda('Camisa Polo', r.camisa_polo_talla);
            if (esVerdadero(r.pantalon)) filasRopa += filaPrenda('Pantalón', r.pantalon_talla);
            if (esVerdadero(r.sudadera)) filasRopa += filaPrenda('Sudadera', r.sudadera_talla);
            if (esVerdadero(r.jean)) filasRopa += filaPrenda('Jean', r.jean_talla);
            if (esVerdadero(r.overol)) filasRopa += filaPrenda('Overol', r.overol_talla);
            if (!filasRopa) filasRopa = filaNoEntrega('No se entrega ropa');

            // Zapatos — solo filas de los que se entregan
            var filasCalzado = '';
            if (esVerdadero(r.zapatos_seguridad)) filasCalzado += filaPrenda('Zapatos Seguridad', r.zapatos_seguridad_talla);
            if (esVerdadero(r.zapatos)) filasCalzado += filaPrenda('Zapatos', r.zapatos_talla);
            if (esVerdadero(r.botas)) filasCalzado += filaPrenda('Botas', r.botas_talla);
            if (!filasCalzado) filasCalzado = filaNoEntrega('No se entrega calzado');

            document.getElementById('print-doc').innerHTML =
                // Encabezado
                '<div style="text-align:center;margin-bottom:18px;padding-bottom:14px;border-bottom:2px solid #1e293b;">' +
                '<img src="../IMG/LOGO.png" alt="Logo" style="height:52px;margin-bottom:6px;display:block;margin-left:auto;margin-right:auto;">' +
                '<h1 style="font-size:13pt;font-weight:700;color:#1e293b;margin:0;text-transform:uppercase;letter-spacing:.04em;">Entrega de Dotación</h1>' +
                '<p style="font-size:9pt;color:#555;margin:3px 0 0;">Gestor de Dotaciones</p>' +
                '</div>' +

                // Datos del trabajador
                '<div style="margin-bottom:18px;">' +
                '<h2 style="font-size:10pt;font-weight:700;color:#1e293b;margin:0 0 10px;padding-bottom:5px;border-bottom:1px solid #e2e8f0;text-transform:uppercase;">Datos del Trabajador</h2>' +
                '<table style="width:100%;border-collapse:collapse;font-size:10pt;">' +
                '<tr><td style="padding:5px 0;color:#555;width:160px;"><strong>Código:</strong></td><td>' + r.codigo + '</td>' +
                '<td style="padding:5px 0;color:#555;width:160px;"><strong>Nombre:</strong></td><td>' + nombre + '</td></tr>' +
                '<tr><td style="padding:5px 0;color:#555;"><strong>Centro de Costos:</strong></td><td>' + centroCosto + '</td>' +
                '<td style="padding:5px 0;color:#555;"><strong>Fecha de ingreso:</strong></td><td>' + fechaIngreso + '</td></tr>' +
                '<tr><td style="padding:5px 0;color:#555;"><strong>Documento:</strong></td><td>' + docId + '</td>' +
                (esVerdadero(r.entrega_confirmada) ? '<td style="padding:5px 0;color:#555;"><strong>Entregado en:</strong></td><td>' + (r.fecha_confirmacion_entrega ? r.fecha_confirmacion_entrega.split('T')[0] : '—') + '</td>' : '<td></td><td></td>') + '</tr>' +
                '</table></div>' +

                // Prendas
                '<div style="margin-bottom:22px;">' +
                '<h2 style="font-size:10pt;font-weight:700;color:#1e293b;margin:0 0 10px;padding-bottom:5px;border-bottom:1px solid #e2e8f0;text-transform:uppercase;">Prendas Entregadas</h2>' +
                '<table style="width:100%;border-collapse:collapse;font-size:9.5pt;">' +
                '<thead><tr style="background:#f1f5f9;">' +
                '<th style="padding:7px 10px;border:1px solid #ccc;text-align:left;">Prenda</th>' +
                '<th style="padding:7px 10px;border:1px solid #ccc;text-align:center;width:90px;">Entregado</th>' +
                '<th style="padding:7px 10px;border:1px solid #ccc;text-align:center;width:90px;">Talla</th>' +
                '</tr></thead><tbody>' +
                grupoImpresion('🧥 Ropa de trabajo') + filasRopa +
                grupoImpresion('👟 Zapatos') + filasCalzado +
                '</tbody></table></div>' +

                // Observaciones (impresión)
                '<div style="margin-bottom:18px;">' +
                '<h2 style="font-size:10pt;font-weight:700;color:#1e293b;margin:0 0 10px;padding-bottom:5px;border-bottom:1px solid #e2e8f0;text-transform:uppercase;">Observaciones</h2>' +
                '<div style="padding:10px 14px;border:1px solid #e2e8f0;border-radius:6px;background:#fafafa;font-size:10pt;white-space:pre-wrap;color:#2d3748;min-height:40px;">' +
                (r.observaciones ? escapeHtml(r.observaciones) : '<em style="color:#a0aec0;">Sin observaciones</em>') +
                '</div></div>' +

                // Condiciones de uso y constancia para el PDF individual
                '<section class="condiciones-dotacion">' +
                '<h2>JARDINES DE SAN NICOLÁS S.A.S.</h2>' +
                '<p>JARDINES DE SAN NICOLÁS S.A.S. hace entrega de dotación de conformidad con la normativa laboral vigente.</p>' +
                '<p>La dotación entregada es de uso exclusivo del trabajador que la recibe y deberá utilizarse para el desempeño de sus labores dentro de la empresa.</p>' +
                '<p>Queda prohibido regalar, vender, prestar o destinar la dotación a fines diferentes de aquellos para los cuales fue suministrada.</p>' +
                '<p>El trabajador declara haber recibido la dotación y se compromete a cumplir las condiciones de uso aquí establecidas.</p>' +
                '<h3>CONSTANCIA DE RECIBO Y COMPROMISO</h3>' +
                '<p>Acuso recibo de la dotación entregada y manifiesto mi compromiso de cumplir las condiciones aquí establecidas.</p>' +
                '</section>' +

                // Fecha de entrega, inmediatamente antes de las firmas del comprobante
                '<div style="font-size:9pt;color:#1e293b;margin:0 0 7px;"><strong>Fecha de entrega:</strong> ' + fechaEntrega + '</div>' +

                // Firmas
                '<div style="margin-bottom:24px;">' +
                '<h2 style="font-size:10pt;font-weight:700;color:#1e293b;margin:0 0 14px;padding-bottom:5px;border-bottom:1px solid #e2e8f0;text-transform:uppercase;">Firmas</h2>' +
                '<div style="display:flex;gap:32px;">' +
                firmaImpresion(r.firma_trabajador, 'Firma del Trabajador') +
                firmaImpresion(r.firma_encargado, 'Firma del Encargado') +
                '</div></div>' +

                // Pie
                '<div style="border-top:1px solid #ccc;padding-top:8px;text-align:center;">' +
                '<p style="font-size:8pt;color:#888;margin:0;">Generado por el Sistema de Dotaciones — ' + new Date().toLocaleDateString('es-CO') + '</p>' +
                '</div>';
        }

        // ── Confirmación de entrega ──────────────────────────────────────────
        var TEXTO_COMPROMISO_ENTREGA = 'Al firmar, confirmo que recibí completa y correctamente las prendas y tallas relacionadas en este registro.';

        function bloqueConfirmarEntrega(r) {
            var tieneFirmasSolicitud = r.firma_trabajador && r.firma_encargado;

            if (esVerdadero(r.entrega_confirmada)) {
                var fechaConf = r.fecha_confirmacion_entrega ? r.fecha_confirmacion_entrega.split('T')[0] : '—';
                return '<div class="card-seccion">' +
                    '<h3 class="seccion-titulo">Confirmación de Entrega</h3>' +
                    '<div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-weight:600;">' +
                    '✅ Entregado en ' + fechaConf + (r.confirmado_por ? ' por ' + r.confirmado_por : '') +
                    '</div>' +
                    '<div class="firmas-detalle">' +
                    '<div class="firma-detalle-bloque"><label>Firma de Entrega — Trabajador</label>' +
                    '<div class="firma-img-wrap">' + (r.firma_entrega_trabajador ? '<img src="' + BASE_URL + r.firma_entrega_trabajador + '" alt="Firma">' : '<span class="firma-vacia">Sin firma</span>') + '</div></div>' +
                    '<div class="firma-detalle-bloque"><label>Firma de Entrega — Encargado</label>' +
                    '<div class="firma-img-wrap">' + (r.firma_entrega_encargado ? '<img src="' + BASE_URL + r.firma_entrega_encargado + '" alt="Firma">' : '<span class="firma-vacia">Sin firma</span>') + '</div></div>' +
                    '</div></div>';
            }

            if (!tieneFirmasSolicitud) {
                return '<div class="card-seccion">' +
                    '<h3 class="seccion-titulo">Confirmación de Entrega</h3>' +
                    '<p style="color:#a0aec0;font-size:0.9rem;">Este registro debe tener las 2 firmas de la solicitud (trabajador y encargado) antes de poder confirmar la entrega física de las prendas.</p>' +
                    '</div>';
            }

            return '<div class="card-seccion">' +
                '<h3 class="seccion-titulo">Confirmación de Entrega</h3>' +
                '<p style="color:#718096;font-size:0.9rem;margin-bottom:14px;">Cuando se entreguen físicamente las prendas a la persona, confirma aquí con una firma nueva del trabajador y del encargado.</p>' +
                '<button type="button" class="btn btn-guardar" id="btn-mostrar-confirmar-entrega">&#10003; Confirmar Entrega</button>' +
                '<div id="panel-confirmar-entrega" style="display:none;margin-top:18px;">' +
                '<div style="background:var(--color-primario-claro);border:1px solid var(--color-primario);color:var(--color-primario-oscuro);padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:0.88rem;">' + TEXTO_COMPROMISO_ENTREGA + '</div>' +
                '<div class="firmas-grid">' +
                '<div class="firma-bloque">' +
                '<label class="firma-label">Firma del Trabajador</label>' +
                '<div class="canvas-wrap" id="wrap_entrega_trabajador"><canvas id="canvas_entrega_trabajador"></canvas><span class="canvas-hint">Firme aquí</span></div>' +
                '<div class="firma-acciones"><button type="button" class="btn btn-limpiar" id="limpiar_entrega_trabajador">&#10005; Limpiar</button>' +
                '<span class="firma-estado" id="estado_entrega_trabajador">Sin firma</span></div>' +
                '</div>' +
                '<div class="firma-bloque">' +
                '<label class="firma-label">Firma del Encargado</label>' +
                '<div class="canvas-wrap" id="wrap_entrega_encargado"><canvas id="canvas_entrega_encargado"></canvas><span class="canvas-hint">Firme aquí</span></div>' +
                '<div class="firma-acciones"><button type="button" class="btn btn-limpiar" id="limpiar_entrega_encargado">&#10005; Limpiar</button>' +
                '<span class="firma-estado" id="estado_entrega_encargado">Sin firma</span></div>' +
                '</div>' +
                '</div>' +
                '<div class="botones-wrap">' +
                '<button type="button" class="btn btn-guardar" id="btn-confirmar-entrega-final">&#10003; Confirmar</button>' +
                '<button type="button" class="btn btn-cancelar" id="btn-cancelar-confirmar-entrega">&#10005; Cancelar</button>' +
                '</div>' +
                '<div id="mensaje-confirmar-entrega" class="mensaje-oculto" style="margin-top:14px;"></div>' +
                '</div></div>';
        }

        function activarConfirmarEntrega(r) {
            var btnMostrar = document.getElementById('btn-mostrar-confirmar-entrega');
            if (!btnMostrar) return; // ya confirmada, o no tiene las firmas de solicitud

            var panel = document.getElementById('panel-confirmar-entrega');
            var firmaEntregaTrabajador = null;
            var firmaEntregaEncargado = null;
            var canvasInicializados = false;

            btnMostrar.addEventListener('click', function() {
                panel.style.display = 'block';
                btnMostrar.style.display = 'none';
                if (!canvasInicializados) {
                    initFirmaCanvas({
                        canvas: 'canvas_entrega_trabajador',
                        hidden: null,
                        btnLimpiar: 'limpiar_entrega_trabajador',
                        estado: 'estado_entrega_trabajador',
                        wrap: 'wrap_entrega_trabajador',
                        claseActivo: 'activo',
                        capturaEnVivo: false,
                        capturaEnSuelta: true,
                        dpiAware: false,
                        onCambio: function(v) {
                            firmaEntregaTrabajador = v;
                        },
                    });
                    initFirmaCanvas({
                        canvas: 'canvas_entrega_encargado',
                        hidden: null,
                        btnLimpiar: 'limpiar_entrega_encargado',
                        estado: 'estado_entrega_encargado',
                        wrap: 'wrap_entrega_encargado',
                        claseActivo: 'activo',
                        capturaEnVivo: false,
                        capturaEnSuelta: true,
                        dpiAware: false,
                        onCambio: function(v) {
                            firmaEntregaEncargado = v;
                        },
                    });
                    canvasInicializados = true;
                }
            });

            document.getElementById('btn-cancelar-confirmar-entrega').addEventListener('click', function() {
                panel.style.display = 'none';
                btnMostrar.style.display = 'inline-flex';
            });

            document.getElementById('btn-confirmar-entrega-final').addEventListener('click', function() {
                var divMsg = document.getElementById('mensaje-confirmar-entrega');
                if (!firmaEntregaTrabajador || !firmaEntregaEncargado) {
                    divMsg.textContent = 'Se requieren ambas firmas (trabajador y encargado) para confirmar la entrega.';
                    divMsg.className = 'mensaje-error';
                    divMsg.style.display = 'block';
                    return;
                }

                var btn = this;
                btn.disabled = true;
                btn.textContent = 'Confirmando...';

                var formData = new FormData();
                formData.append('id', r.id);
                formData.append('firma_entrega_trabajador', firmaEntregaTrabajador);
                formData.append('firma_entrega_encargado', firmaEntregaEncargado);

                fetch('../Controller/DotacionController.php?action=confirmar_entrega', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            divMsg.textContent = '✅ ' + data.message;
                            divMsg.className = 'mensaje-exito';
                            divMsg.style.display = 'block';
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                            return;
                        }
                        divMsg.textContent = '❌ ' + (data.message || 'Error al confirmar la entrega.');
                        divMsg.className = 'mensaje-error';
                        divMsg.style.display = 'block';
                        btn.disabled = false;
                        btn.textContent = '✓ Confirmar';
                    })
                    .catch(function() {
                        divMsg.textContent = '❌ Error de conexión.';
                        divMsg.className = 'mensaje-error';
                        divMsg.style.display = 'block';
                        btn.disabled = false;
                        btn.textContent = '✓ Confirmar';
                    });
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
            document.getElementById('contenido').innerHTML =
                '<div style="text-align:center;color:#e53e3e;padding:40px;">❌ ' + msg + '</div>';
        }
    </script>
    <script src="../JS/ui-common.js"></script>
    <script src="../JS/firma-canvas.js"></script>
    <script src="../JS/Script.js"></script>
    <?php include __DIR__ . '/partials/hamburger_script.php'; ?>

    <?php include __DIR__ . '/partials/modal_logout.php'; ?>

<script src="../JS/demo.js"></script>
</body>

</html>
