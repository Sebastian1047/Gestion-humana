// =============================================
//  Script.js  —  Dotacion.php
//  Nueva lógica: búsqueda por persona → ver sus registros
// =============================================

document.addEventListener("DOMContentLoaded", function () {
  // ── Botón nuevo registro ───────────────────
  var btnNuevo = document.getElementById("btnNuevoRegistro");
  if (btnNuevo) {
    btnNuevo.addEventListener("click", function () {
      window.location.href = "NuevaDotacion.php";
    });
  }

  // ── Solo inicializar el resto si estamos en Dotacion.php ──
  var personasGrid = document.getElementById("personasGrid");
  var vistaPersonas = document.getElementById("vistaPersonas");
  var vistaRegistros = document.getElementById("vistaRegistros");
  var tablaRegistros = document.getElementById("tablaRegistros");
  var buscadorPersona = document.getElementById("buscadorPersona");
  var ppNombre = document.getElementById("ppNombre");
  var ppMeta = document.getElementById("ppMeta");
  var btnVolver = document.getElementById("btnVolverPersonas");

  if (!personasGrid) return; // no estamos en la página correcta

  var codigoActual = null;
  var timeoutBusqueda = null;

  var todasLasPersonas = []; // aquí guardas el array completo
  var paginaActual = 1;
  var porPagina = 12; // cuántas tarjetas por página
  var todasLasPersonasOriginal = [];
  var filtroSinNinguna = document.getElementById("filtroSinNinguna");
  var filtroSinFirmaTrabajador = document.getElementById("filtroSinFirmaTrabajador");
  var filtroSinFirmaEncargado = document.getElementById("filtroSinFirmaEncargado");
  var contadorPersonas = document.getElementById("contadorPersonas");

  // "registros_sin_firma" (las 4 firmas vacías a la vez) ya no sirve para
  // detectar "le falta SOLO trabajador" o "le falta SOLO encargado" — desde
  // que se completó la firma de encargado en todos los registros existentes,
  // ese conteo quedó permanentemente en 0. Se evalúan trabajador y encargado
  // por separado (la API ya trae registros_sin_firma_trabajador/_encargado).
  function faltaFirmaTrabajador(p) {
    return (parseInt(p.registros_sin_firma_trabajador) || 0) > 0;
  }
  function faltaFirmaEncargado(p) {
    return (parseInt(p.registros_sin_firma_encargado) || 0) > 0;
  }
  function faltaAlgunaFirma(p) {
    return faltaFirmaTrabajador(p) || faltaFirmaEncargado(p);
  }
  function estaCompleto(p) {
    return !faltaAlgunaFirma(p) && (parseInt(p.total_registros) || 0) > 0;
  }
  // "registros_sin_firma" (las 4 firmas vacías a la vez) sigue siendo
  // exactamente lo que significa "sin ninguna firma".
  function sinNingunaFirma(p) {
    return (parseInt(p.registros_sin_firma) || 0) > 0;
  }

  // ── Carga inicial: mostrar todas las personas ──
  cargarPersonas("");

  // ── Aplica búsqueda por texto + filtros de firma juntos, sobre los datos
  //    ya cargados (sin volver a pedirlos al servidor) ──
  function aplicarFiltrosLocales() {
    var termino = buscadorPersona.value.trim().toLowerCase();
    var soloSinNinguna = !!(filtroSinNinguna && filtroSinNinguna.checked);
    var soloSinTrabajador = !!(filtroSinFirmaTrabajador && filtroSinFirmaTrabajador.checked);
    var soloSinEncargado = !!(filtroSinFirmaEncargado && filtroSinFirmaEncargado.checked);

    todasLasPersonas = todasLasPersonasOriginal.filter(function (p) {
      var coincideTexto =
        termino === "" ||
        (p.nombre_empleado || "").toLowerCase().includes(termino) ||
        (p.codigo || "").toLowerCase().includes(termino);

      var coincideSinNinguna = !soloSinNinguna || sinNingunaFirma(p);
      var coincideSinTrabajador = !soloSinTrabajador || faltaFirmaTrabajador(p);
      var coincideSinEncargado = !soloSinEncargado || faltaFirmaEncargado(p);

      return (
        coincideTexto &&
        coincideSinNinguna &&
        coincideSinTrabajador &&
        coincideSinEncargado
      );
    });

    actualizarContador(termino);

    paginaActual = 1;
    renderPersonas();
  }

  // ── Contador: total / firmados / sin firmar, sobre el resultado de la
  //    búsqueda de texto (sin importar cuál checkbox esté marcado, para
  //    mostrar siempre el desglose completo) ──────────────────────────
  function actualizarContador(termino) {
    if (!contadorPersonas) return;

    var base = todasLasPersonasOriginal.filter(function (p) {
      return (
        termino === "" ||
        (p.nombre_empleado || "").toLowerCase().includes(termino) ||
        (p.codigo || "").toLowerCase().includes(termino)
      );
    });

    var firmados = base.filter(estaCompleto).length;
    var sinFirmar = base.filter(faltaAlgunaFirma).length;

    contadorPersonas.innerHTML =
      "Total: " +
      base.length +
      " &nbsp;·&nbsp; ✅ Firmados: " +
      firmados +
      " &nbsp;·&nbsp; ⚠️ Sin firmar: " +
      sinFirmar;
  }

  // ── Buscador: filtrar en tiempo real ──────────
  buscadorPersona.addEventListener("input", function () {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(aplicarFiltrosLocales, 350);
  });

  // ── Checkboxes de firma: todos se pueden combinar entre sí (no son
  //    excluyentes) ──
  [filtroSinNinguna, filtroSinFirmaTrabajador, filtroSinFirmaEncargado].forEach(function (chk) {
    if (chk) chk.addEventListener("change", aplicarFiltrosLocales);
  });

  // ── Volver al grid de personas ────────────────
  btnVolver.addEventListener("click", function () {
    mostrarVistaPersonas();
  });

  document
    .getElementById("btnConsultarFechas")
    .addEventListener("click", consultarPorFechas);

  // ===========================================
  //  CARGAR PERSONAS
  // ===========================================
  function cargarPersonas(termino) {
    personasGrid.innerHTML =
      '<div style="grid-column:1/-1;text-align:center;color:#718096;padding:32px 0;">Cargando...</div>';

    var url = "../Controller/DotacionController.php?action=listar_personas";
    if (termino) url += "&q=" + encodeURIComponent(termino);

    fetch(url)
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.error) {
          personasGrid.innerHTML =
            '<div style="grid-column:1/-1;text-align:center;color:#e53e3e;padding:32px 0;">❌ ' +
            data.error +
            "</div>";
          return;
        }
        todasLasPersonasOriginal = data;
        aplicarFiltrosLocales(); // respeta el checkbox "sin firma" si ya estaba marcado
      })
      .catch(function () {
        personasGrid.innerHTML =
          '<div style="grid-column:1/-1;text-align:center;color:#e53e3e;padding:32px 0;">❌ Error de conexión.</div>';
      });
  }

  // ===========================================
  // CONSULTAR POR RANGO DE FECHAS
  // ===========================================
  function consultarPorFechas() {
    var fechaInicio = document.getElementById("exportFechaInicio").value;
    var fechaFin = document.getElementById("exportFechaFin").value;

    if (!fechaInicio || !fechaFin) {
      mostrarToast("Seleccione la fecha inicial y la fecha final.", "aviso");
      return;
    }

    personasGrid.innerHTML =
      '<div style="grid-column:1/-1;text-align:center;color:#718096;padding:32px 0;">Consultando registros...</div>';

    fetch(
      "../Controller/DotacionController.php?action=listar_por_fecha" +
        "&fechaInicio=" +
        encodeURIComponent(fechaInicio) +
        "&fechaFin=" +
        encodeURIComponent(fechaFin),
    )
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.error) {
          personasGrid.innerHTML =
            '<div style="grid-column:1/-1;text-align:center;color:#e53e3e;padding:32px 0;">❌ ' +
            data.error +
            "</div>";

          return;
        }

        todasLasPersonasOriginal = data;
        aplicarFiltrosLocales(); // respeta el checkbox "sin firma" si ya estaba marcado
      })
      .catch(function () {
        personasGrid.innerHTML =
          '<div style="grid-column:1/-1;text-align:center;color:#e53e3e;padding:32px 0;">❌ Error de conexión.</div>';
      });
  }

  // ===========================================
  //  RENDERIZAR TARJETAS DE PERSONAS
  // ===========================================
  function renderPersonas() {
    var personas = todasLasPersonas;

    var totalPaginas = Math.ceil(personas.length / porPagina);
    var inicio = (paginaActual - 1) * porPagina;
    var slice = personas.slice(inicio, inicio + porPagina);

    // primero limpias el grid
    personasGrid.innerHTML = "";

    // verificación de vacío VA AQUÍ, antes de cualquier forEach
    if (!personas.length) {
      personasGrid.innerHTML =
        '<div style="grid-column:1/-1;" class="estado-vacio">' +
        '<div class="ev-icon">🔍</div>' +
        "<p>No se encontraron colaboradores con dotaciones registradas.</p>" +
        "</div>";
      return;
    }

    // UN solo forEach, usando slice
    slice.forEach(function (p) {
      var ultimaFecha = p.ultima_entrega ? p.ultima_entrega.split("T")[0] : "—";
      var sinFirma = parseInt(p.registros_sin_firma) || 0;

      var card = document.createElement("div");
      card.className = "persona-card";
      card.innerHTML =
        '<div class="pc-codigo">' +
        p.codigo +
        "</div>" +
        '<div class="pc-nombre">' +
        (p.nombre_empleado || "(Sin nombre)") +
        "</div>" +
        '<div class="pc-meta">' +
        "<span>" +
        (p.nombre_centro_costo || "—") +
        "</span>" +
        '<span class="pc-badge">' +
        p.total_registros +
        " registro" +
        (p.total_registros != 1 ? "s" : "") +
        "</span>" +
        "</div>" +
        (sinFirma > 0
          ? '<div style="font-size:0.78rem;color:#c53030;font-weight:600;margin-top:6px;">⚠️ ' +
            sinFirma +
            " sin ninguna firma</div>"
          : "") +
        '<div style="font-size:0.78rem;color:#a0aec0;margin-top:8px;">Última entrega: ' +
        ultimaFecha +
        "</div>";

      card.addEventListener("click", function () {
        abrirPersona(
          p.codigo,
          p.nombre_empleado,
          p.nombre_centro_costo,
          p.total_registros,
        );
      });

      personasGrid.appendChild(card);
    });

    // al final, los botones de paginación
    renderPaginacion(totalPaginas);
  }

  function renderPaginacion(totalPaginas) {
    // Busca si ya existe el contenedor, si no lo crea
    var contenedor = document.getElementById("paginacionPersonas");
    if (!contenedor) {
      contenedor = document.createElement("div");
      contenedor.id = "paginacionPersonas";
      contenedor.style.cssText =
        "display:flex;gap:6px;justify-content:center;margin-top:20px;flex-wrap:wrap;";
      personasGrid.parentNode.insertBefore(
        contenedor,
        personasGrid.nextSibling,
      );
    }

    contenedor.innerHTML = "";

    if (totalPaginas <= 1) return; // si cabe en una página, no muestres nada

    for (var i = 1; i <= totalPaginas; i++) {
      var btn = document.createElement("button");
      btn.textContent = i;
      btn.style.cssText =
        "padding:6px 12px;border-radius:6px;border:1px solid #e2e8f0;cursor:pointer;font-size:14px;background:" +
        (i === paginaActual ? "#780606" : "#fff") +
        ";color:" +
        (i === paginaActual ? "#fff" : "#333") +
        ";";

      (function (pagina) {
        btn.addEventListener("click", function () {
          paginaActual = pagina;
          renderPersonas();
          personasGrid.scrollIntoView({ behavior: "smooth" });
        });
      })(i);

      contenedor.appendChild(btn);
    }
  }

  // ===========================================
  //  ABRIR REGISTROS DE UNA PERSONA
  // ===========================================
  function abrirPersona(codigo, nombre, centroCosto, totalReg) {
    codigoActual = codigo;
    ppNombre.textContent = nombre || codigo;
    ppMeta.textContent =
      (centroCosto || "—") + " · " + totalReg + " registro(s)";

    vistaPersonas.style.display = "none";
    vistaRegistros.style.display = "block";

    tablaRegistros.innerHTML =
      '<tr><td colspan="4" style="text-align:center;color:#718096;padding:24px;">Cargando registros...</td></tr>';

    fetch(
      "../Controller/DotacionController.php?action=listar_por_persona&codigo=" +
        encodeURIComponent(codigo),
    )
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.error) {
          tablaRegistros.innerHTML =
            '<tr><td colspan="4" style="text-align:center;color:#e53e3e;padding:24px;">❌ ' +
            data.error +
            "</td></tr>";
          return;
        }
        renderRegistros(data);
      })
      .catch(function () {
        tablaRegistros.innerHTML =
          '<tr><td colspan="4" style="text-align:center;color:#e53e3e;padding:24px;">❌ Error de conexión.</td></tr>';
      });
  }

  // ===========================================
  //  RENDERIZAR REGISTROS DE LA PERSONA
  // ===========================================
  function renderRegistros(registros) {
    tablaRegistros.innerHTML = "";

    if (!registros.length) {
      tablaRegistros.innerHTML =
        '<tr><td colspan="4" style="text-align:center;color:#718096;padding:32px;">Sin registros activos.</td></tr>';
      return;
    }

    registros.forEach(function (r) {
      var fecha = r.fecha_entrega ? r.fecha_entrega.split("T")[0] : "—";

      var prendas = [];
      if (r.camisa == 1) prendas.push("Camisa " + (r.camisa_talla || ""));
      if (r.camisa_oxford == 1)
        prendas.push("Camisa Oxford " + (r.camisa_oxford_talla || ""));
      if (r.camisa_polo == 1)
        prendas.push("Camisa Polo " + (r.camisa_polo_talla || ""));
      if (r.pantalon == 1) prendas.push("Pantalón " + (r.pantalon_talla || ""));
      if (r.sudadera == 1) prendas.push("Sudadera " + (r.sudadera_talla || ""));
      if (r.jean == 1) prendas.push("Jean " + (r.jean_talla || ""));
      if (r.overol == 1) prendas.push("Overol " + (r.overol_talla || ""));
      if (r.zapatos_seguridad == 1) prendas.push("Zapatos Seguridad " + (r.zapatos_seguridad_talla || ""));
      if (r.zapatos == 1) prendas.push("Zapatos " + (r.zapatos_talla || ""));
      if (r.botas == 1) prendas.push("Botas " + (r.botas_talla || ""));
      var prendasTexto = prendas.length ? prendas.join(" · ") : "—";

      var tr = document.createElement("tr");
      tr.setAttribute("data-id", r.id);
      tr.innerHTML =
        "<td>" +
        fecha +
        "</td>" +
        "<td>" +
        prendasTexto +
        "</td>" +
        '<td style="color:#718096;font-size:0.85rem;">' +
        (r.creado_por || "—") +
        "</td>" +
        '<td class="acciones-col">' +
        '<button class="btn-accion btn-ver"      onclick="verRegistro(' +
        r.id +
        ')">Ver</button>' +
        '<button class="btn-accion btn-editar"   onclick="editarRegistro(' +
        r.id +
        ')">Editar</button>' +
        '<button class="btn-accion btn-eliminar" onclick="pedirEliminar(' +
        r.id +
        ", '" +
        fecha +
        "')\">Eliminar</button>" +
        "</td>";

      tablaRegistros.appendChild(tr);
    });
  }

  // ===========================================
  //  VOLVER A VISTA PERSONAS
  // ===========================================
  function mostrarVistaPersonas() {
    vistaRegistros.style.display = "none";
    vistaPersonas.style.display = "block";
    codigoActual = null;
    // Al volver, siempre se muestra la lista completa de nuevo: si se dejara
    // el término de búsqueda que llevó a esta persona, cargarPersonas()
    // volvía a pedir al servidor solo esa persona y la lista quedaba
    // "pegada" en ese único resultado hasta refrescar la página a mano.
    buscadorPersona.value = "";
    [filtroSinNinguna, filtroSinFirmaTrabajador, filtroSinFirmaEncargado].forEach(function (chk) {
      if (chk) chk.checked = false;
    });
    // Recargar por si se eliminó algo
    cargarPersonas("");
  }

  // ===========================================
  //  MODAL CONFIRMAR ELIMINAR
  // ===========================================
  var modalEliminar = document.getElementById("modalEliminar");
  var modalEliminarTexto = document.getElementById("modalEliminarTexto");
  var btnConfirmarEliminar = document.getElementById("btnConfirmarEliminar");
  var btnCancelarEliminar = document.getElementById("btnCancelarEliminar");
  var idParaEliminar = null;

  // Exponer al scope global para que los botones inline puedan llamarla
  window.pedirEliminar = function (id, fecha) {
    idParaEliminar = id;
    modalEliminarTexto.textContent =
      "Se eliminará el registro de fecha " +
      fecha +
      ". Se guardará una copia en la papelera por si fue un error.";
    modalEliminar.classList.add("activo");
  };

  btnCancelarEliminar.addEventListener("click", function () {
    modalEliminar.classList.remove("activo");
    idParaEliminar = null;
  });

  modalEliminar.addEventListener("click", function (e) {
    if (e.target === modalEliminar) {
      modalEliminar.classList.remove("activo");
      idParaEliminar = null;
    }
  });

  btnConfirmarEliminar.addEventListener("click", function () {
    if (!idParaEliminar) return;
    modalEliminar.classList.remove("activo");
    ejecutarEliminar(idParaEliminar);
    idParaEliminar = null;
  });

  function ejecutarEliminar(id) {
    var formData = new FormData();
    formData.append("id", id);

    fetch("../Controller/DotacionController.php?action=eliminar", {
      method: "POST",
      body: formData,
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.success) {
          // Quitar la fila de la tabla
          var fila = tablaRegistros.querySelector('[data-id="' + id + '"]');
          if (fila) fila.remove();

          // Si no quedan filas, mostrar estado vacío
          if (!tablaRegistros.querySelector("tr[data-id]")) {
            tablaRegistros.innerHTML =
              '<tr><td colspan="4" style="text-align:center;color:#718096;padding:32px;">Sin registros activos.</td></tr>';
          }

          mostrarToast("✅ Registro eliminado correctamente", "exito");
        } else {
          mostrarToast("❌ " + (data.error || "Error al eliminar"), "error");
        }
      })
      .catch(function () {
        mostrarToast("❌ Error de conexión. Intenta de nuevo.", "error");
      });
  }

  // ===========================================
  //  EXPORTAR MASIVO
  // ===========================================
  var btnExportar = document.getElementById("btnExportarMasivo");
  if (btnExportar) {
    btnExportar.addEventListener("click", function () {
      var fi = document.getElementById("exportFechaInicio").value;
      var ff = document.getElementById("exportFechaFin").value;
      var msg = document.getElementById("exportMensaje");

      if (!fi || !ff) {
        msg.textContent = "⚠️ Selecciona un rango de fechas primero.";
        msg.style.color = "#e53e3e";
        return;
      }
      if (fi > ff) {
        msg.textContent =
          "⚠️ La fecha inicial no puede ser mayor que la final.";
        msg.style.color = "#e53e3e";
        return;
      }

      msg.textContent = "Generando Excel...";
      msg.style.color = "#718096";

      window.location.href =
        "../Controller/exportardotacion.php?modo=masivo&fecha_inicio=" +
        fi +
        "&fecha_fin=" +
        ff;

      setTimeout(function () {
        msg.textContent = "";
      }, 3500);
    });
  }
});

// =============================================
//  ACCIONES GLOBALES (llamadas desde HTML inline)
// =============================================
function verRegistro(id) {
  window.location.href = "Verdotacion.php?id=" + id;
}

function editarRegistro(id) {
  window.location.href = "editarDotacion.php?id=" + id;
}

// =============================================
//  TOAST
//  (función compartida mostrarToast() cargada desde JS/ui-common.js)
// =============================================

// ===========================================
//  MODAL CERRAR SESIÓN
// ===========================================
var btnLogout = document.getElementById("btnLogout");
var modalOver = document.getElementById("modalOverlay");
var btnCancel = document.getElementById("btnCancel");

if (btnLogout && modalOver) {
  btnLogout.addEventListener("click", function (e) {
    e.preventDefault();
    modalOver.classList.add("active");
  });
}
if (btnCancel && modalOver) {
  btnCancel.addEventListener("click", function () {
    modalOver.classList.remove("active");
  });
}
if (modalOver) {
  modalOver.addEventListener("click", function (e) {
    if (e.target === modalOver) modalOver.classList.remove("active");
  });
}

