// =============================================
//  TallasExistencias.js
//  Módulo "Tallas vs. Existencias"
// =============================================

document.addEventListener("DOMContentLoaded", function () {
  var divMensaje = document.getElementById("mensaje-resultado");

  function mostrarMensaje(texto, tipo) {
    var clase =
      tipo === "exito" ? "mensaje-exito" : tipo === "aviso" ? "mensaje-aviso" : "mensaje-error";
    divMensaje.textContent = texto;
    divMensaje.className = clase;
    divMensaje.style.display = "block";
    divMensaje.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  function escapeHtml(str) {
    return String(str == null ? "" : str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  // ── Paso 1: descargar Excel de tallas ──────────────────────────
  document.getElementById("btnDescargarTallas").addEventListener("click", function () {
    var fi = document.getElementById("tallasFechaInicio").value;
    var ff = document.getElementById("tallasFechaFin").value;
    if (!fi || !ff) {
      mostrarMensaje("Selecciona el rango de fechas para exportar.", "error");
      return;
    }
    var url =
      "../Controller/TallasExistenciasController.php?action=exportar_tallas&fechaInicio=" +
      encodeURIComponent(fi) +
      "&fechaFin=" +
      encodeURIComponent(ff);
    window.location.href = url;
  });

  // ── Paso 2: subir y calcular ────────────────────────────────────
  var form = document.getElementById("form-comparar");
  var btnCalcular = document.getElementById("btnCalcular");
  var seccionResultado = document.getElementById("seccionResultado");
  var tablaResultado = document.getElementById("tablaResultado");
  var cardSinMapear = document.getElementById("cardSinMapear");
  var tablaSinMapear = document.getElementById("tablaSinMapear");
  var btnInformeCompra = document.getElementById("btnInformeCompra");

  var ultimoInforme = []; // guarda el último resultado calculado, para el Informe de Compra

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    var archivoTallas = document.getElementById("archivoTallas").files[0];
    var archivoExistencias = document.getElementById("archivoExistencias").files[0];

    if (!archivoTallas || !archivoExistencias) {
      mostrarMensaje("Selecciona los 2 archivos Excel.", "error");
      return;
    }

    var formData = new FormData();
    formData.append("archivo_tallas", archivoTallas);
    formData.append("archivo_existencias", archivoExistencias);

    btnCalcular.disabled = true;
    btnCalcular.textContent = "Calculando...";
    seccionResultado.style.display = "none";
    divMensaje.style.display = "none";

    fetch("../Controller/TallasExistenciasController.php?action=comparar", {
      method: "POST",
      body: formData,
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        btnCalcular.disabled = false;
        btnCalcular.innerHTML = "&#10003; Calcular";

        if (!data.success) {
          mostrarMensaje("❌ " + (data.message || "Error al procesar los archivos."), "error");
          return;
        }

        ultimoInforme = Array.isArray(data.informe) ? data.informe : [];
        renderResultado(ultimoInforme);
        renderSinMapear(data.sin_mapear);
        seccionResultado.style.display = "block";
        seccionResultado.scrollIntoView({ behavior: "smooth", block: "start" });
      })
      .catch(function () {
        btnCalcular.disabled = false;
        btnCalcular.innerHTML = "&#10003; Calcular";
        mostrarMensaje("❌ Error de conexión.", "error");
      });
  });

  function renderResultado(informe) {
    var hayFaltantes = informe.some(function (f) {
      return f.faltante > 0;
    });
    btnInformeCompra.disabled = !hayFaltantes;

    if (!Array.isArray(informe) || informe.length === 0) {
      tablaResultado.innerHTML =
        '<tr><td colspan="6" style="text-align:center;color:#718096;padding:24px;">No hay datos para mostrar — revisa que ambos archivos tengan información.</td></tr>';
      return;
    }
    tablaResultado.innerHTML = informe
      .map(function (fila) {
        var hayFaltante = fila.faltante > 0;
        return (
          '<tr class="' + (hayFaltante ? "fila-con-faltante" : "") + '">' +
          "<td>" + escapeHtml(fila.prenda) + "</td>" +
          "<td>" + escapeHtml(fila.talla) + "</td>" +
          "<td>" + escapeHtml(fila.genero) + "</td>" +
          "<td>" + fila.pedido + "</td>" +
          "<td>" + fila.existencia + "</td>" +
          '<td><span class="faltante-badge ' + (hayFaltante ? "si" : "no") + '">' + fila.faltante + "</span></td>" +
          "</tr>"
        );
      })
      .join("");
  }

  function renderSinMapear(sinMapear) {
    if (!Array.isArray(sinMapear) || sinMapear.length === 0) {
      cardSinMapear.style.display = "none";
      return;
    }
    cardSinMapear.style.display = "block";
    tablaSinMapear.innerHTML = sinMapear
      .map(function (fila) {
        return (
          "<tr><td>" + escapeHtml(fila.codigo) + "</td><td>" + fila.existencia + "</td></tr>"
        );
      })
      .join("");
  }

  // ── Informe de Compra (solo lo que falta) ───────────────────────
  btnInformeCompra.addEventListener("click", function () {
    var faltantes = ultimoInforme.filter(function (f) {
      return f.faltante > 0;
    });
    if (faltantes.length === 0) return;

    btnInformeCompra.disabled = true;
    var textoOriginal = btnInformeCompra.innerHTML;
    btnInformeCompra.textContent = "Generando...";

    fetch("../Controller/TallasExistenciasController.php?action=informe_compra", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ informe: faltantes }),
    })
      .then(function (r) {
        if (!r.ok) throw new Error("Error al generar el informe");
        return r.blob();
      })
      .then(function (blob) {
        var url = URL.createObjectURL(blob);
        window.open(url, "_blank");
        setTimeout(function () {
          URL.revokeObjectURL(url);
        }, 30000);
      })
      .catch(function () {
        mostrarMensaje("❌ No se pudo generar el informe de compra.", "error");
      })
      .finally(function () {
        btnInformeCompra.disabled = false;
        btnInformeCompra.innerHTML = textoOriginal;
      });
  });
});
