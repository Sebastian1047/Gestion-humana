// ═══════════════════════════════════════════════════════════════════
// PreingresoJN.js  —  Entrevista Selección de Personal · Jardines de San Nicolás
// Adaptado de Preingreso.js (Jardines del Sol): mismo comportamiento de
// asistente por pasos, pero con los campos del formato físico de JN.
// ═══════════════════════════════════════════════════════════════════

const modal = document.getElementById("modalRegistros");
const inputBusqueda = document.getElementById("buscarFecha");
let paginaActual = 1;
let archivoAEliminar = null;

// ======================================
// VER REGISTROS
// ======================================
function cargarRegistros(busqueda = "", pagina = 1) {
  fetch(
    `../View/abrirRegistrosEntrevista.php?buscar=${encodeURIComponent(busqueda)}&pagina=${pagina}`,
  )
    .then((res) => res.text())
    .then((data) => {
      document.getElementById("listaRegistros").innerHTML = data;
      paginaActual = pagina;
    })
    .catch((err) => console.error(err));
}
window.cargarRegistros = cargarRegistros;

document.getElementById("btn-registros")?.addEventListener("click", () => {
  cargarRegistros();
  modal.style.display = "flex";
});
document.getElementById("cerrarModal")?.addEventListener("click", () => {
  modal.style.display = "none";
});
window.addEventListener("click", (e) => {
  if (e.target === modal) modal.style.display = "none";
});
inputBusqueda?.addEventListener("keyup", () => {
  cargarRegistros(inputBusqueda.value, 1);
});

window.eliminarRegistro = function (archivo) {
  archivoAEliminar = archivo;
  const modalEliminar = document.getElementById("modalEliminarOverlay");
  if (!modalEliminar) return;
  modalEliminar.classList.add("active");
};

document.addEventListener("DOMContentLoaded", function () {
  function _exportarFirma(canvas) {
    var t = document.createElement("canvas");
    t.width = canvas.width;
    t.height = canvas.height;
    var c = t.getContext("2d");
    c.fillStyle = "#ffffff";
    c.fillRect(0, 0, t.width, t.height);
    c.drawImage(canvas, 0, 0);
    return t.toDataURL("image/jpeg", 0.85);
  }

  var pasoActual = 1;
  var totalPasos = 11;

  var btnSiguiente = document.getElementById("btnSiguiente");
  var btnAnterior = document.getElementById("btnAnterior");
  var btnGuardar = document.getElementById("btnGuardar");
  var contador = document.getElementById("pasoContador");
  var barra = document.getElementById("progresoBarra");

  barra.style.width = (1 / totalPasos) * 100 + "%";

  // =============================================
  //  NAVEGACIÓN ENTRE PASOS
  // =============================================
  btnSiguiente.addEventListener("click", function () {
    if (!validarPasoActual()) return;
    if (pasoActual < totalPasos) irAPaso(pasoActual + 1);
  });
  btnAnterior.addEventListener("click", function () {
    if (pasoActual > 1) irAPaso(pasoActual - 1);
  });

  function capturarFirmas() {
    ["canvas_firma_prueba", "canvas_firma_aspirante", "canvas_firma_entrevistador"].forEach(
      function (canvasId) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        var hidden = document.getElementById(
          canvasId.replace("canvas_", "") + "_data",
        );
        if (!hidden) return;
        if (canvas.width > 0 && canvas.height > 0) {
          var exported = _exportarFirma(canvas);
          if (exported && exported !== "data:," && exported.length > 100) {
            hidden.value = exported;
          }
        }
      },
    );
  }

  function irAPaso(nuevoPaso) {
    capturarFirmas();
    document.getElementById("paso-" + pasoActual).classList.add("oculto");

    var circuloActual = document.querySelector(
      '.paso[data-paso="' + pasoActual + '"]',
    );
    if (nuevoPaso > pasoActual) {
      circuloActual.classList.remove("activo");
      circuloActual.classList.add("completado");
      circuloActual.querySelector(".paso-circulo").innerHTML = "✔";
    } else {
      circuloActual.classList.remove("activo", "completado");
      circuloActual.querySelector(".paso-circulo").innerHTML = pasoActual;
    }

    actualizarLineas(nuevoPaso);
    pasoActual = nuevoPaso;
    document.getElementById("paso-" + pasoActual).classList.remove("oculto");

    setTimeout(function () {
      document.querySelectorAll("canvas").forEach(function (c) {
        c.dispatchEvent(new Event("resize"));
      });
    }, 100);

    var circuloNuevo = document.querySelector(
      '.paso[data-paso="' + pasoActual + '"]',
    );
    circuloNuevo.classList.remove("completado");
    circuloNuevo.classList.add("activo");
    var circulo = circuloNuevo.querySelector(".paso-circulo");
    if (circulo.innerHTML !== "✔") circulo.innerHTML = pasoActual;

    barra.style.width = (pasoActual / totalPasos) * 100 + "%";
    contador.textContent = "Paso " + pasoActual + " de " + totalPasos;

    btnAnterior.style.display = pasoActual > 1 ? "inline-flex" : "none";
    btnSiguiente.style.display =
      pasoActual < totalPasos ? "inline-flex" : "none";
    btnGuardar.style.display =
      pasoActual === totalPasos ? "inline-flex" : "none";

    setTimeout(function () {
      document.getElementById("paso-" + pasoActual).scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }, 50);
  }

  function actualizarLineas(nuevoPaso) {
    document.querySelectorAll(".paso-linea").forEach(function (linea, i) {
      if (i < nuevoPaso - 1) linea.classList.add("completada");
      else linea.classList.remove("completada");
    });
  }

  // =============================================
  //  VALIDACIÓN POR PASO
  // =============================================
  function validarPasoActual() {
    var paso = document.getElementById("paso-" + pasoActual);
    var requeridos = paso.querySelectorAll("[required]");
    var valido = true;
    var primero = null;
    requeridos.forEach(function (campo) {
      campo.style.borderColor = "";
      campo.style.boxShadow = "";
      if (!campo.value.trim()) {
        campo.style.borderColor = "#e53e3e";
        campo.style.boxShadow = "0 0 0 3px rgba(229,62,62,0.15)";
        if (!primero) primero = campo;
        valido = false;
      }
    });
    if (primero) primero.scrollIntoView({ behavior: "smooth", block: "center" });
    return valido;
  }

  // =============================================
  //  CAMPOS CONDICIONALES
  // =============================================
  function condicional(nombreRadio, valorMostrar, idWrap) {
    var radios = document.querySelectorAll('input[name="' + nombreRadio + '"]');
    var wrap = document.getElementById(idWrap);
    if (!wrap) return;
    radios.forEach(function (radio) {
      radio.addEventListener("change", function () {
        wrap.style.display = this.value === valorMostrar ? "block" : "none";
      });
    });
  }

  // Sección 2
  condicional("cuenta_ahorros", "si", "wrap_banco");
  condicional("tiene_eps", "si", "wrap_eps");
  condicional("tiene_pension", "si", "wrap_fondo");
  condicional("continua_estudiando", "si", "wrap_estudio_detalle");
  // Sección 3
  condicional("modalidad_vivienda", "otra", "wrap_otra_vivienda");
  condicional("modalidad_vivienda", "arrendada", "wrap_valor_arrendo");
  condicional("tiene_animales", "si", "wrap_animales");
  condicional("tiene_banos", "si", "wrap_num_banos");
  condicional("tiene_alcobas", "si", "wrap_num_alcobas");
  condicional("tiene_patio", "si", "wrap_num_patios");
  // Sección 4
  condicional("estado_civil", "otro", "wrap_estado_civil_otro");
  condicional("cuota_hijos", "si", "wrap_medio_pago");
  condicional("hijos_adiccion", "si", "wrap_hijos_adiccion");
  condicional("padre_vive", "no", "wrap_causa_padre");
  condicional("madre_vive", "no", "wrap_causa_madre");
  // Sección 5
  condicional("fuma", "si", "wrap_frecuencia_fuma");
  condicional("consume_licor", "si", "wrap_frecuencia_licor");
  condicional("consume_drogas", "si", "wrap_frecuencia_drogas");
  // Sección 7
  condicional("otras_actividades", "si", "wrap_otras_actividades");
  condicional("otros_procesos", "si", "wrap_otros_procesos");
  condicional("aspiracion_salarial", "si", "wrap_salario");
  condicional("involucrado_instituciones", "si", "wrap_instituciones");
  condicional("tiene_pep", "si", "wrap_pep");
  condicional("sanciones", "si", "wrap_sanciones");
  condicional("practica_deporte", "si", "wrap_deporte");
  condicional("practica_deporte", "si", "wrap_frecuencia_deporte");
  condicional("tiene_arte", "si", "wrap_arte");
  condicional("pertenece_grupo", "si", "wrap_grupo");
  // Sección 9
  (function () {
    var radios = document.querySelectorAll('input[name="aplica_prueba"]');
    var wrap = document.getElementById("wrap_detalle_prueba");
    if (!wrap) return;
    radios.forEach(function (r) {
      r.addEventListener("change", function () {
        wrap.style.display = this.value === "no" ? "none" : "block";
      });
    });
  })();

  // =============================================
  //  CÁLCULO DE EDAD
  // =============================================
  var fechaNac = document.getElementById("fecha_nacimiento");
  var campoEdad = document.getElementById("edad");
  if (fechaNac && campoEdad) {
    fechaNac.addEventListener("change", function () {
      if (!this.value) return;
      var hoy = new Date();
      var nac = new Date(this.value);
      var edad = hoy.getFullYear() - nac.getFullYear();
      var mes = hoy.getMonth() - nac.getMonth();
      if (mes < 0 || (mes === 0 && hoy.getDate() < nac.getDate())) edad--;
      campoEdad.value = edad > 0 ? edad : "";
    });
  }

  // =============================================
  //  TABLAS DINÁMICAS
  // =============================================
  function bindTablaDinamica(btnId, tbodyId, colCount, plantilla, prefijo) {
    var cont = 0;
    var btn = document.getElementById(btnId);
    if (!btn) return;
    btn.addEventListener("click", function () {
      var tbody = document.getElementById(tbodyId);
      var vacia = tbody.querySelector(".fila-vacia");
      if (vacia) vacia.remove();
      cont++;
      var idx = cont;
      var tr = document.createElement("tr");
      tr.id = prefijo + "-fila-" + idx;
      tr.innerHTML =
        plantilla +
        '<td><button type="button" class="btn-eliminar-fila" onclick="eliminarFila(\'' +
        prefijo +
        "-fila-" +
        idx +
        "','" +
        tbodyId +
        "'," +
        colCount +
        ')">✕</button></td>';
      tbody.appendChild(tr);
    });
  }

  bindTablaDinamica(
    "btnAgregarHijo",
    "bodyHijos",
    5,
    '<td><input type="text" name="hijo_nombre[]" class="input input-tabla" placeholder="Nombre"></td>' +
      '<td><input type="number" name="hijo_edad[]" class="input input-tabla" min="0" placeholder="Edad" style="width:70px;"></td>' +
      '<td><input type="text" name="hijo_hace[]" class="input input-tabla" placeholder="Estudia / trabaja..."></td>' +
      '<td><input type="text" name="hijo_enfermedad[]" class="input input-tabla" placeholder="Ninguna / especificar"></td>',
    "hijo",
  );

  bindTablaDinamica(
    "btnAgregarHermano",
    "bodyHermanos",
    5,
    '<td><input type="text" name="hermano_nombre[]" class="input input-tabla" placeholder="Nombre"></td>' +
      '<td><input type="text" name="hermano_hace[]" class="input input-tabla" placeholder="Ocupación"></td>' +
      '<td><input type="text" name="hermano_donde[]" class="input input-tabla" placeholder="Ciudad"></td>' +
      '<td><input type="text" name="hermano_enfermedad[]" class="input input-tabla" placeholder="Ninguna / especificar"></td>',
    "hermano",
  );

  bindTablaDinamica(
    "btnAgregarEmpresa",
    "bodyEmpresas",
    6,
    '<td><input type="text" name="empresa_nombre[]" class="input input-tabla" placeholder="Empresa"></td>' +
      '<td><input type="text" name="empresa_hace[]" class="input input-tabla" placeholder="Funciones"></td>' +
      '<td><input type="text" name="empresa_tiempo[]" class="input input-tabla" placeholder="Ej: 1 año" style="width:90px;"></td>' +
      '<td><input type="date" name="empresa_salida[]" class="input input-tabla" style="width:130px;"></td>' +
      '<td><input type="text" name="empresa_porque[]" class="input input-tabla" placeholder="Motivo"></td>',
    "empresa",
  );

  window.eliminarFila = function (idFila, idTbody, colCount) {
    var fila = document.getElementById(idFila);
    var tbody = document.getElementById(idTbody);
    if (fila) fila.remove();
    if (tbody && tbody.querySelectorAll("tr:not(.fila-vacia)").length === 0) {
      var tr = document.createElement("tr");
      tr.className = "fila-vacia";
      tr.innerHTML =
        '<td colspan="' + colCount + '">Sin registros. Haz clic en "+ Agregar".</td>';
      tbody.appendChild(tr);
    }
  };

  // =============================================
  //  CANVAS DE FIRMAS
  // =============================================
  ["firma_prueba", "firma_aspirante", "firma_entrevistador"].forEach(function (campo) {
    initFirmaCanvas({
      canvas: "canvas_" + campo,
      hidden: campo + "_data",
      btnLimpiar: "limpiar_" + campo,
      estado: "estado_" + campo,
      wrap: "wrap_" + campo,
      claseFirmado: "firmado-pi",
      capturaEnVivo: true,
      capturaEnSuelta: true,
      dpiAware: true,
      restaurarDesdeHidden: true,
    });
  });

  // =============================================
  //  AUTOGUARDADO EN LOCALSTORAGE
  // =============================================
  var form = document.getElementById("form-preingreso");
  var LS_KEY = "entrevista_jn_formulario";

  function guardarLocal() {
    var formData = new FormData(form);
    var data = {};
    formData.forEach(function (value, key) {
      data[key] = value;
    });
    try {
      localStorage.setItem(LS_KEY, JSON.stringify(data));
    } catch (e) {}
  }
  form.addEventListener("input", guardarLocal);
  form.addEventListener("change", guardarLocal);

  function restaurarFormulario() {
    var guardado;
    try {
      guardado = localStorage.getItem(LS_KEY);
    } catch (e) {
      return;
    }
    if (!guardado) return;
    var data = JSON.parse(guardado);
    Object.keys(data).forEach(function (key) {
      var campo = form.querySelector('[name="' + key + '"]');
      if (!campo) return;
      if (campo.type === "radio") {
        var radio = form.querySelector(
          '[name="' + key + '"][value="' + data[key] + '"]',
        );
        if (radio) {
          radio.checked = true;
          radio.dispatchEvent(new Event("change"));
        }
      } else if (campo.type === "checkbox") {
        campo.checked = data[key] === "on";
      } else {
        campo.value = data[key];
      }
    });
  }
  restaurarFormulario();

  // =============================================
  //  GUARDAR → PDF
  // =============================================
  btnGuardar.addEventListener("click", async function () {
    if (!validarPasoActual()) return;
    var btn = this;
    capturarFirmas();

    var formData = new FormData(form);

    btn.disabled = true;
    btn.textContent = "⏳ Guardando...";
    btn.style.background = "";

    try {
      var response = await fetch("../Controller/guardarEntrevista.php", {
        method: "POST",
        body: formData,
      });
      var text = await response.text();
      var jsonStart = text.indexOf("{");
      if (jsonStart > 0) text = text.slice(jsonStart);
      var data = JSON.parse(text);

      if (data.success) {
        btn.textContent = "✔ Guardado";
        btn.style.background = "#38a169";
        btn.disabled = false;
        try {
          localStorage.removeItem(LS_KEY);
        } catch (e) {}
        mostrarToast("✔ Entrevista guardada: " + data.data.archivo, "ok");
      } else {
        btn.textContent = "✓ Guardar";
        btn.disabled = false;
        mostrarToast("❌ Error: " + data.message, "error");
      }
    } catch (err) {
      console.error(err);
      btn.textContent = "✓ Guardar";
      btn.disabled = false;
      mostrarToast("❌ Error de conexión. Revisa la consola.", "error");
    }
  });

  // =============================================
  //  MODAL ELIMINAR
  // =============================================
  const modalEl = document.getElementById("modalEliminarOverlay");
  const btnCancel = document.getElementById("btnCancelEliminar");
  const btnConfirm = document.getElementById("btnConfirmEliminar");
  if (!modalEl || !btnCancel || !btnConfirm) return;

  btnCancel.addEventListener("click", function () {
    modalEl.classList.remove("active");
    archivoAEliminar = null;
  });

  btnConfirm.addEventListener("click", async function () {
    if (!archivoAEliminar) return;
    try {
      const fd = new FormData();
      fd.append("eliminar", "1");
      fd.append("archivo", archivoAEliminar);
      const res = await fetch("../Controller/guardarEntrevista.php", {
        method: "POST",
        body: fd,
      });
      const data = await res.json();
      if (data.success) {
        document.querySelector(`[data-file="${archivoAEliminar}"]`)?.remove();
        modalEl.classList.remove("active");
        archivoAEliminar = null;
      } else {
        mostrarToast("No se pudo eliminar", "error");
      }
    } catch (e) {
      console.error(e);
      mostrarToast("Error eliminando archivo", "error");
    }
  });

  modalEl.addEventListener("click", function (e) {
    if (e.target === modalEl) {
      modalEl.classList.remove("active");
      archivoAEliminar = null;
    }
  });
});
