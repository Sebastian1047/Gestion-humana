// =============================================
//  NuevaDotacion.js
// =============================================

document.addEventListener("DOMContentLoaded", function () {
  // ── Elementos de búsqueda ──────────────────
  var inputBusqueda = document.getElementById("busqueda");
  var listaSugerencias = document.getElementById("sugerencias");

  // ── Campos autocompletados ─────────────────
  var campoCodigo = document.getElementById("codigo");
  var campoNombre = document.getElementById("nombre");
  var campoCargo = document.getElementById("cargo");

  // ── Canvas firmas ──────────────────────────
  var canvasTrabajador = document.getElementById("canvas_trabajador");
  var canvasEncargado = document.getElementById("canvas_encargado");
  var dataTrabajador = document.getElementById("firma_trabajador_data");
  var dataEncargado = document.getElementById("firma_encargado_data");
  var estadoTrabajador = document.getElementById("estado_trabajador");
  var estadoEncargado = document.getElementById("estado_encargado");

  // ── Form y botón ───────────────────────────
  var form = document.getElementById("form-dotacion");
  var btnGuardar = document.getElementById("btn-guardar");
  var divMensaje = document.getElementById("mensaje-resultado");

  // ── Memoria de firmas (scope del DOMContentLoaded) ──
  var firmaGuardadaTrabajador = null;
  var firmaGuardadaEncargado = null;

  // ===========================================
  //  BUSCADOR DE EMPLEADOS
  // ===========================================
  var timeoutBusqueda = null;

  inputBusqueda.addEventListener("input", function () {
    clearTimeout(timeoutBusqueda);
    var termino = this.value.trim();
    if (termino.length < 2) {
      cerrarSugerencias();
      return;
    }
    timeoutBusqueda = setTimeout(function () {
      buscarEmpleados(termino);
    }, 300);
  });

  function buscarEmpleados(termino) {
    fetch(
      "../Controller/DotacionController.php?action=buscar_empleado&q=" +
        encodeURIComponent(termino),
    )
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        mostrarSugerencias(data);
      })
      .catch(function () {
        cerrarSugerencias();
      });
  }

  function mostrarSugerencias(empleados) {
    listaSugerencias.innerHTML = "";
    if (!Array.isArray(empleados) || empleados.length === 0) {
      listaSugerencias.innerHTML =
        '<div class="sugerencia-vacia">No se encontraron resultados</div>';
      listaSugerencias.style.display = "block";
      return;
    }
    empleados.forEach(function (emp) {
      var item = document.createElement("div");
      item.className = "sugerencia-item";
      item.innerHTML =
        '<span class="sugerencia-codigo">' +
        emp.codigo +
        "</span>" +
        emp.nombre +
        '<small style="color:#718096;display:block;margin-top:2px;">' +
        (emp.cargo || "") +
        "</small>";
      item.addEventListener("mousedown", function (e) {
        e.preventDefault();
        seleccionarEmpleado(emp);
      });
      listaSugerencias.appendChild(item);
    });
    listaSugerencias.style.display = "block";
  }

  function seleccionarEmpleado(emp) {
    inputBusqueda.value = emp.nombre;
    campoCodigo.value = emp.codigo || "";
    campoNombre.value = emp.nombre || "";
    campoCargo.value = emp.cargo || "";
    cerrarSugerencias();
    divMensaje.style.display = "none";
  }

  function cerrarSugerencias() {
    listaSugerencias.style.display = "none";
    listaSugerencias.innerHTML = "";
  }

  document.addEventListener("click", function (e) {
    if (
      !inputBusqueda.contains(e.target) &&
      !listaSugerencias.contains(e.target)
    ) {
      cerrarSugerencias();
    }
  });

  // ===========================================
  //  CHECKBOXES DE PRENDAS
  // ===========================================
  var NOMBRES_PRENDAS = [
    "camisa",
    "camisa_oxford",
    "camisa_polo",
    "pantalon",
    "sudadera",
    "jean",
    "overol",
    "zapatos_seguridad",
    "zapatos",
    "botas",
  ];

  NOMBRES_PRENDAS.forEach(function (nombre) {
    var chk = document.getElementById("check_" + nombre);
    var sel = document.getElementById(nombre + "_talla");
    var chkSexoOpuesto = document.getElementById("sexo_opuesto_" + nombre);
    if (!chk || !sel) return;
    var bloque = chk.closest(".prenda-bloque");
    chk.addEventListener("change", function () {
      sel.disabled = !this.checked;
      if (!this.checked) sel.value = "";
      bloque.classList.toggle("activa", this.checked);
      if (chkSexoOpuesto) {
        chkSexoOpuesto.disabled = !this.checked;
        if (!this.checked) chkSexoOpuesto.checked = false;
      }
    });
  });

  // ── Género de la persona (necesario para resolver "sexo opuesto" por prenda) ──
  var opuesto = { M: "F", F: "M" };
  function generoResueltoPrenda(nombrePrenda) {
    var genero = document.getElementById("genero").value;
    var chkSexoOpuesto = document.getElementById("sexo_opuesto_" + nombrePrenda);
    if (!genero) return "";
    return chkSexoOpuesto && chkSexoOpuesto.checked ? opuesto[genero] : genero;
  }

  // ===========================================
  //  CANVAS DE FIRMAS (JS/firma-canvas.js)
  // ===========================================
  initFirmaCanvas({
    canvas: canvasTrabajador,
    hidden: dataTrabajador,
    btnLimpiar: "limpiar_trabajador",
    estado: estadoTrabajador,
    claseActivo: "activo",
    capturaEnVivo: true,
    capturaEnSuelta: true,
    dpiAware: false,
    onCambio: function (v) {
      firmaGuardadaTrabajador = v;
    },
  });
  initFirmaCanvas({
    canvas: canvasEncargado,
    hidden: dataEncargado,
    btnLimpiar: "limpiar_encargado",
    estado: estadoEncargado,
    claseActivo: "activo",
    capturaEnVivo: true,
    capturaEnSuelta: true,
    dpiAware: false,
    onCambio: function (v) {
      firmaGuardadaEncargado = v;
    },
  });

  // ===========================================
  //  ENVÍO DEL FORMULARIO
  // ===========================================
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    if (!campoCodigo.value.trim()) {
      mostrarMensaje(
        "Por favor selecciona un trabajador usando el buscador.",
        "error",
      );
      inputBusqueda.focus();
      return;
    }

    var fechaInput = document.getElementById("fecha_entrega");
    if (!fechaInput || !fechaInput.value) {
      mostrarMensaje("Por favor selecciona la fecha de entrega.", "error");
      return;
    }

    var campoGenero = document.getElementById("genero");
    if (!campoGenero || !campoGenero.value) {
      mostrarMensaje("Por favor selecciona el género de la persona.", "error");
      return;
    }

    var checksPrendas = [
      "check_camisa",
      "check_camisa_oxford",
      "check_camisa_polo",
      "check_pantalon",
      "check_sudadera",
      "check_jean",
      "check_overol",
      "check_zapatos_seguridad",
      "check_zapatos",
      "check_botas",
    ];
    var algunaMarcada = checksPrendas.some(function (id) {
      var el = document.getElementById(id);
      return el && el.checked;
    });
    if (!algunaMarcada) {
      mostrarMensaje(
        "Debes marcar al menos una prenda para guardar el registro.",
        "error",
      );
      return;
    }

    var validacionTallas = [
      { check: "check_camisa", talla: "camisa_talla", nombre: "camisa" },
      {
        check: "check_camisa_oxford",
        talla: "camisa_oxford_talla",
        nombre: "camisa oxford",
      },
      {
        check: "check_camisa_polo",
        talla: "camisa_polo_talla",
        nombre: "camisa polo",
      },
      { check: "check_pantalon", talla: "pantalon_talla", nombre: "pantalón" },
      { check: "check_sudadera", talla: "sudadera_talla", nombre: "sudadera" },
      { check: "check_jean", talla: "jean_talla", nombre: "jean" },
      { check: "check_overol", talla: "overol_talla", nombre: "overol" },
      { check: "check_zapatos_seguridad", talla: "zapatos_seguridad_talla", nombre: "zapatos seguridad" },
      {
        check: "check_zapatos",
        talla: "zapatos_talla",
        nombre: "zapatos",
      },
      { check: "check_botas", talla: "botas_talla", nombre: "botas" },
    ];
    for (var i = 0; i < validacionTallas.length; i++) {
      var v = validacionTallas[i];
      var chkEl = document.getElementById(v.check);
      if (chkEl && chkEl.checked && !document.getElementById(v.talla).value) {
        mostrarMensaje("Selecciona la talla de " + v.nombre + ".", "error");
        return;
      }
    }

    // Usar copia en memoria; si no hay firma real, dejar el campo vacío
    // (antes se caía a canvas.toDataURL() sobre un canvas vacío, generando
    // siempre una imagen JPEG negra por la falta de soporte de transparencia)
    dataTrabajador.value = firmaGuardadaTrabajador || "";
    dataEncargado.value = firmaGuardadaEncargado || "";

    btnGuardar.disabled = true;
    btnGuardar.classList.add("loading");
    btnGuardar.textContent = "Guardando...";

    var formData = new FormData(form);

    // Género resuelto por prenda (cruza "sexo opuesto" con el género de la
    // persona, ya calculado aquí — el backend solo persiste el valor final).
    NOMBRES_PRENDAS.forEach(function (nombre) {
      formData.append(nombre + "_genero", generoResueltoPrenda(nombre));
    });

    fetch("../Controller/DotacionController.php?action=guardar", {
      method: "POST",
      body: formData,
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.success) {
          mostrarMensaje("✅ " + data.message, "exito");
          setTimeout(function () {
            window.location.href = "Dotacion.php";
          }, 1800);
          return;
        }
        if (data.error === "duplicado") {
          mostrarMensaje("⚠️ " + data.message, "aviso");
          resetBoton();
          return;
        }
        mostrarMensaje("❌ " + (data.message || "Error al guardar"), "error");
        resetBoton();
      })
      .catch(function () {
        mostrarMensaje("❌ Error de conexión. Intenta de nuevo.", "error");
        resetBoton();
      });
  });

  // ===========================================
  //  HELPERS
  // ===========================================
  function resetBoton() {
    btnGuardar.disabled = false;
    btnGuardar.classList.remove("loading");
    btnGuardar.innerHTML = "&#10003; Guardar Dotación";
  }

  function mostrarMensaje(texto, tipo) {
    var claseCSS =
      tipo === "exito"
        ? "mensaje-exito"
        : tipo === "aviso"
          ? "mensaje-aviso"
          : "mensaje-error";
    divMensaje.textContent = texto;
    divMensaje.className = claseCSS;
    divMensaje.style.display = "block";
    divMensaje.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }
}); // fin DOMContentLoaded
