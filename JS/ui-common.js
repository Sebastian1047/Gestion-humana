// =============================================
// ui-common.js — Utilidades de interfaz compartidas
// =============================================
// Punto único para "avisar algo al usuario" mediante una notificación flotante
// (toast). Requiere que la hoja de estilo cargue las clases .toast / .toast.visible /
// .toast.exito / .toast.error / .toast.aviso. Si no existe un <div id="toast">
// en la página, esta función crea uno automáticamente la primera vez que se usa.

// Fuerza un campo a contener solo dígitos mientras el usuario escribe (ej.
// campos de cédula) — evita que lleguen caracteres inválidos al backend.
// Uso: <input ... inputmode="numeric" pattern="[0-9]*" oninput="soloNumeros(this)">
function soloNumeros(input) {
  var limpio = input.value.replace(/[^0-9]/g, "");
  if (limpio !== input.value) {
    input.value = limpio;
  }
}

function mostrarToast(mensaje, tipo) {
  var mapaTipos = { ok: "exito", exito: "exito", error: "error", aviso: "aviso" };
  var claseTipo = mapaTipos[tipo] || "aviso";

  var toast = document.getElementById("toast");
  if (!toast) {
    toast = document.createElement("div");
    toast.id = "toast";
    document.body.appendChild(toast);
  }

  toast.textContent = mensaje;
  toast.className = "toast " + claseTipo;
  toast.classList.add("visible");

  clearTimeout(toast._mostrarToastTimeoutId);
  toast._mostrarToastTimeoutId = setTimeout(function () {
    toast.classList.remove("visible");
  }, 3500);
}
