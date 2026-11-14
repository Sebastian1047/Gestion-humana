// =============================================
//  EquivalenciasBodega.js
//  Módulo "Tallas vs. Existencias"
// =============================================

document.addEventListener("DOMContentLoaded", function () {
  var ETIQUETAS_PRENDA = {
    camisa: "Camibuso",
    camisa_oxford: "Camisa Oxford",
    camisa_polo: "Camisa Polo",
    pantalon: "Pantalón",
    sudadera: "Sudadera",
    jean: "Jean",
    overol: "Overol",
    zapatos_seguridad: "Zapatos Seguridad",
    zapatos: "Zapatos",
    botas: "Botas",
  };

  var form = document.getElementById("form-equivalencia");
  var campoId = document.getElementById("equiv_id");
  var campoCodigo = document.getElementById("equiv_codigo");
  var campoPrenda = document.getElementById("equiv_prenda");
  var campoTalla = document.getElementById("equiv_talla");
  var campoGenero = document.getElementById("equiv_genero");
  var campoDescripcion = document.getElementById("equiv_descripcion");
  var btnGuardar = document.getElementById("btnGuardarEquiv");
  var btnCancelar = document.getElementById("btnCancelarEquiv");
  var titulo = document.getElementById("tituloFormEquiv");
  var tabla = document.getElementById("tablaEquivalencias");
  var divMensaje = document.getElementById("mensaje-resultado");

  function escapeHtml(str) {
    return String(str == null ? "" : str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function mostrarMensaje(texto, tipo) {
    var clase =
      tipo === "exito" ? "mensaje-exito" : tipo === "aviso" ? "mensaje-aviso" : "mensaje-error";
    divMensaje.textContent = texto;
    divMensaje.className = clase;
    divMensaje.style.display = "block";
    divMensaje.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  function cargarTabla() {
    fetch("../Controller/EquivalenciasController.php?action=listar")
      .then(function (r) {
        return r.json();
      })
      .then(function (datos) {
        if (!Array.isArray(datos) || datos.length === 0) {
          tabla.innerHTML =
            '<tr><td colspan="6" style="text-align:center;color:#718096;padding:24px;">Todavía no hay equivalencias registradas.</td></tr>';
          return;
        }
        tabla.innerHTML = datos
          .map(function (fila) {
            return (
              "<tr>" +
              "<td><strong>" + escapeHtml(fila.codigo_bodega) + "</strong></td>" +
              "<td>" + (ETIQUETAS_PRENDA[fila.prenda] || fila.prenda) + "</td>" +
              "<td>" + escapeHtml(fila.talla) + "</td>" +
              "<td>" + (fila.genero === "M" ? "Masculino" : fila.genero === "F" ? "Femenino" : "Ambos") + "</td>" +
              "<td>" + escapeHtml(fila.descripcion || "—") + "</td>" +
              '<td class="acciones-col">' +
              '<button type="button" class="btn-accion btn-editar" data-editar="' + fila.id + '">Editar</button>' +
              '<button type="button" class="btn-accion btn-eliminar" data-eliminar="' + fila.id + '">Eliminar</button>' +
              "</td>" +
              "</tr>"
            );
          })
          .join("");

        tabla.querySelectorAll("[data-editar]").forEach(function (btn) {
          btn.addEventListener("click", function () {
            var fila = datos.find(function (d) {
              return String(d.id) === btn.getAttribute("data-editar");
            });
            if (fila) entrarModoEdicion(fila);
          });
        });
        tabla.querySelectorAll("[data-eliminar]").forEach(function (btn) {
          btn.addEventListener("click", function () {
            eliminar(btn.getAttribute("data-eliminar"));
          });
        });
      })
      .catch(function () {
        tabla.innerHTML =
          '<tr><td colspan="6" style="text-align:center;color:#e53e3e;padding:24px;">Error al cargar las equivalencias.</td></tr>';
      });
  }

  function entrarModoEdicion(fila) {
    campoId.value = fila.id;
    campoCodigo.value = fila.codigo_bodega;
    campoPrenda.value = fila.prenda;
    campoTalla.value = fila.talla;
    campoGenero.value = fila.genero;
    campoDescripcion.value = fila.descripcion || "";
    titulo.textContent = "Editar equivalencia";
    btnGuardar.innerHTML = "&#10003; Guardar cambios";
    btnCancelar.style.display = "inline-flex";
    form.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  function salirModoEdicion() {
    campoId.value = "";
    form.reset();
    titulo.textContent = "Agregar equivalencia";
    btnGuardar.innerHTML = "&#10003; Agregar";
    btnCancelar.style.display = "none";
  }

  btnCancelar.addEventListener("click", salirModoEdicion);

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    if (!campoCodigo.value.trim() || !campoPrenda.value || !campoTalla.value.trim()) {
      mostrarMensaje("Completa código, prenda y talla.", "error");
      return;
    }

    var esEdicion = !!campoId.value;
    var formData = new FormData();
    if (esEdicion) formData.append("id", campoId.value);
    formData.append("codigo_bodega", campoCodigo.value.trim());
    formData.append("prenda", campoPrenda.value);
    formData.append("talla", campoTalla.value.trim());
    formData.append("genero", campoGenero.value);
    formData.append("descripcion", campoDescripcion.value.trim());

    btnGuardar.disabled = true;

    fetch(
      "../Controller/EquivalenciasController.php?action=" + (esEdicion ? "actualizar" : "crear"),
      { method: "POST", body: formData }
    )
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        btnGuardar.disabled = false;
        if (data.success) {
          mostrarMensaje("✅ " + data.message, "exito");
          salirModoEdicion();
          cargarTabla();
          return;
        }
        mostrarMensaje("❌ " + (data.message || "Error al guardar."), "error");
      })
      .catch(function () {
        btnGuardar.disabled = false;
        mostrarMensaje("❌ Error de conexión.", "error");
      });
  });

  function eliminar(id) {
    if (!confirm("¿Eliminar esta equivalencia?")) return;

    var formData = new FormData();
    formData.append("id", id);

    fetch("../Controller/EquivalenciasController.php?action=eliminar", {
      method: "POST",
      body: formData,
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (data) {
        if (data.success) {
          mostrarMensaje("✅ " + data.message, "exito");
          cargarTabla();
          return;
        }
        mostrarMensaje("❌ " + (data.message || "Error al eliminar."), "error");
      })
      .catch(function () {
        mostrarMensaje("❌ Error de conexión.", "error");
      });
  }

  cargarTabla();
});
