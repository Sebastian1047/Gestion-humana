(function () {
    "use strict";

    var tbody = document.getElementById("tbodyPapelera");
    var estadoVacio = document.getElementById("estadoVacioPapelera");
    var estadoCargando = document.getElementById("estadoCargandoPapelera");
    var tablaWrap = document.querySelector(".tabla-wrap-papelera");

    var buscador = document.getElementById("buscadorPapelera");
    var filtroCategoria = document.getElementById("filtroCategoriaPapelera");
    var filtroDesde = document.getElementById("filtroDesdePapelera");
    var filtroHasta = document.getElementById("filtroHastaPapelera");
    var btnLimpiarFiltros = document.getElementById("btnLimpiarFiltrosPapelera");
    var btnVaciarPapelera = document.getElementById("btnVaciarPapelera");

    var modalConfirmar = document.getElementById("modalConfirmarPapelera");
    var confirmarIcono = document.getElementById("confirmarPapeleraIcono");
    var confirmarTitulo = document.getElementById("confirmarPapeleraTitulo");
    var confirmarTexto = document.getElementById("confirmarPapeleraTexto");
    var btnCancelarConfirmar = document.getElementById("btnCancelarConfirmarPapelera");
    var btnAceptarConfirmar = document.getElementById("btnAceptarConfirmarPapelera");

    var categoriasCargadas = false;
    var accionPendiente = null; // function a ejecutar si el usuario confirma
    var timeoutBusqueda = null;

    function formatearTamano(bytes) {
        bytes = Number(bytes) || 0;
        if (bytes < 1024) return bytes + " B";
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
        return (bytes / (1024 * 1024)).toFixed(1) + " MB";
    }

    function formatearFecha(fechaTexto) {
        if (!fechaTexto) return "—";
        var partes = fechaTexto.split(" ");
        var fecha = partes[0] ? partes[0].split("-") : null;
        if (!fecha || fecha.length !== 3) return fechaTexto;
        return fecha[2] + "/" + fecha[1] + "/" + fecha[0] + (partes[1] ? " " + partes[1].substring(0, 5) : "");
    }

    function escaparHtml(texto) {
        var div = document.createElement("div");
        div.textContent = texto == null ? "" : String(texto);
        return div.innerHTML;
    }

    function construirUrl() {
        var params = new URLSearchParams();
        params.set("action", "listar");
        if (buscador.value.trim()) params.set("buscar", buscador.value.trim());
        if (filtroCategoria.value) params.set("categoria", filtroCategoria.value);
        if (filtroDesde.value) params.set("desde", filtroDesde.value);
        if (filtroHasta.value) params.set("hasta", filtroHasta.value);
        return "../Controller/PapeleraController.php?" + params.toString();
    }

    function cargarCategorias(categorias) {
        if (categoriasCargadas) return;
        categoriasCargadas = true;
        Object.keys(categorias).forEach(function (clave) {
            var opt = document.createElement("option");
            opt.value = clave;
            opt.textContent = categorias[clave];
            filtroCategoria.appendChild(opt);
        });
    }

    function pintarEstadisticas(estadisticas) {
        document.getElementById("statTotalArchivos").textContent = estadisticas.total_archivos;
        document.getElementById("statEspacioTotal").textContent = formatearTamano(estadisticas.espacio_total);

        var top = null;
        Object.keys(estadisticas.por_categoria || {}).forEach(function (clave) {
            var item = estadisticas.por_categoria[clave];
            if (item.total > 0 && (!top || item.total > top.total)) {
                top = item;
            }
        });
        document.getElementById("statCategoriaTop").textContent = top ? top.etiqueta + " (" + top.total + ")" : "—";
    }

    function pintarFila(entrada) {
        var tr = document.createElement("tr");

        var contexto = entrada.contexto
            ? '<span class="contexto-archivo-papelera">' + escaparHtml(entrada.contexto) + "</span>"
            : "";

        tr.innerHTML =
            '<td><span class="badge-modulo-papelera">' + escaparHtml(entrada.modulo) + "</span></td>" +
            '<td><span class="nombre-archivo-papelera">' + escaparHtml(entrada.nombre_original) + "</span>" + contexto + "</td>" +
            "<td>" + escaparHtml(entrada.eliminado_por) + "</td>" +
            "<td>" + escaparHtml(formatearFecha(entrada.fecha_eliminacion)) + "</td>" +
            "<td>" + formatearTamano(entrada.tamano) + "</td>" +
            '<td class="col-acciones-papelera"><div class="acciones-fila-papelera">' +
            '<button class="btn-restaurar-papelera" data-id="' + escaparHtml(entrada.id) + '">↩️ Restaurar</button>' +
            '<button class="btn-purgar-papelera" data-id="' + escaparHtml(entrada.id) + '">🗑️ Purgar</button>' +
            "</div></td>";

        tr.querySelector(".btn-restaurar-papelera").addEventListener("click", function () {
            confirmarAccion({
                icono: "↩️",
                titulo: "¿Restaurar este archivo?",
                texto: entrada.tipo === "dotacion"
                    ? '"' + entrada.nombre_original + '" se volverá a crear como registro de Dotaciones, con sus firmas.'
                    : '"' + entrada.nombre_original + '" volverá a su carpeta original en ' + entrada.modulo + ".",
                textoBoton: "Sí, restaurar",
                claseBoton: "btn-restaurar-papelera",
                onConfirmar: function () {
                    ejecutarAccion("restaurar", entrada.id, "Archivo restaurado");
                },
            });
        });

        tr.querySelector(".btn-purgar-papelera").addEventListener("click", function () {
            confirmarAccion({
                icono: "🗑️",
                titulo: "¿Eliminar definitivamente?",
                texto: entrada.tipo === "dotacion"
                    ? '"' + entrada.nombre_original + '" (registro y firmas) se borrará para siempre. Esta acción NO se puede deshacer.'
                    : '"' + entrada.nombre_original + '" se borrará para siempre. Esta acción NO se puede deshacer.',
                textoBoton: "Sí, eliminar definitivamente",
                onConfirmar: function () {
                    ejecutarAccion("purgar", entrada.id, "Archivo eliminado definitivamente");
                },
            });
        });

        return tr;
    }

    function cargarPapelera() {
        estadoCargando.style.display = "block";
        tablaWrap.style.display = "none";
        estadoVacio.style.display = "none";

        fetch(construirUrl())
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                estadoCargando.style.display = "none";

                if (!data.success) {
                    mostrarToast(data.message || "No se pudo cargar la papelera", "error");
                    return;
                }

                cargarCategorias(data.categorias || {});
                pintarEstadisticas(data.estadisticas || { total_archivos: 0, espacio_total: 0, por_categoria: {} });

                tbody.innerHTML = "";

                if (!data.entradas || data.entradas.length === 0) {
                    estadoVacio.style.display = "block";
                    tablaWrap.style.display = "none";
                    return;
                }

                tablaWrap.style.display = "block";
                data.entradas.forEach(function (entrada) {
                    tbody.appendChild(pintarFila(entrada));
                });
            })
            .catch(function () {
                estadoCargando.style.display = "none";
                mostrarToast("Error de conexión al cargar la papelera", "error");
            });
    }

    function ejecutarAccion(action, id, mensajeExito) {
        var form = new URLSearchParams();
        form.set("action", action);
        form.set("id", id);

        fetch("../Controller/PapeleraController.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: form.toString(),
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (data.success) {
                    mostrarToast(data.message || mensajeExito, "exito");
                    cargarPapelera();
                } else {
                    mostrarToast(data.message || "No se pudo completar la acción", "error");
                }
            })
            .catch(function () {
                mostrarToast("Error de conexión", "error");
            });
    }

    function confirmarAccion(opciones) {
        confirmarIcono.textContent = opciones.icono;
        confirmarTitulo.textContent = opciones.titulo;
        confirmarTexto.textContent = opciones.texto;
        btnAceptarConfirmar.textContent = opciones.textoBoton;
        accionPendiente = opciones.onConfirmar;
        modalConfirmar.classList.add("visible");
    }

    function cerrarModalConfirmar() {
        modalConfirmar.classList.remove("visible");
        accionPendiente = null;
    }

    btnCancelarConfirmar.addEventListener("click", cerrarModalConfirmar);
    modalConfirmar.addEventListener("click", function (e) {
        if (e.target === modalConfirmar) cerrarModalConfirmar();
    });

    btnAceptarConfirmar.addEventListener("click", function () {
        var accion = accionPendiente;
        cerrarModalConfirmar();
        if (accion) accion();
    });

    btnVaciarPapelera.addEventListener("click", function () {
        confirmarAccion({
            icono: "🔥",
            titulo: "¿Vaciar toda la papelera?",
            texto: "Se eliminarán definitivamente TODOS los archivos que están en la papelera. Esta acción NO se puede deshacer.",
            textoBoton: "Sí, vaciar todo",
            onConfirmar: function () {
                fetch("../Controller/PapeleraController.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=vaciar",
                })
                    .then(function (r) {
                        return r.json();
                    })
                    .then(function (data) {
                        mostrarToast(data.message || "Papelera vaciada", data.success ? "exito" : "error");
                        cargarPapelera();
                    })
                    .catch(function () {
                        mostrarToast("Error de conexión", "error");
                    });
            },
        });
    });

    buscador.addEventListener("input", function () {
        clearTimeout(timeoutBusqueda);
        timeoutBusqueda = setTimeout(cargarPapelera, 350);
    });
    filtroCategoria.addEventListener("change", cargarPapelera);
    filtroDesde.addEventListener("change", cargarPapelera);
    filtroHasta.addEventListener("change", cargarPapelera);

    btnLimpiarFiltros.addEventListener("click", function () {
        buscador.value = "";
        filtroCategoria.value = "";
        filtroDesde.value = "";
        filtroHasta.value = "";
        cargarPapelera();
    });

    cargarPapelera();
})();
