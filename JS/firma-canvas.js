// ═══════════════════════════════════════════════════════════════════
// firma-canvas.js
// Módulo compartido para los canvas de firma manuscrita.
//
// Centraliza el dibujo del canvas (mouse/touch, marcar "firmado", exportar a
// base64, limpiar), pero acepta opciones para que cada vista se comporte
// exactamente como necesite — no fuerza un único comportamiento para todas.
//
// Uso:
//   var firma = initFirmaCanvas({
//     canvas: 'canvas_trabajador',       // id o elemento <canvas>
//     hidden: 'firma_trabajador_data',   // id o elemento <input type="hidden">
//     wrap: 'wrap_trabajador',           // opcional, default: canvas.parentElement
//     btnLimpiar: 'limpiar_trabajador',  // opcional, id o elemento del botón limpiar
//     estado: 'estado_trabajador',       // opcional, id o elemento del texto de estado
//   });
//   firma.capturar();  // exporta manualmente (usado por vistas que capturan solo al enviar)
//   firma.limpiar();   // limpia manualmente
//   firma.tieneFirma() // true/false
// ═══════════════════════════════════════════════════════════════════

function initFirmaCanvas(opciones) {
  function resolverEl(valor) {
    if (!valor) return null;
    return typeof valor === "string" ? document.getElementById(valor) : valor;
  }

  var canvas = resolverEl(opciones.canvas);
  if (!canvas) return null;

  var hidden = resolverEl(opciones.hidden);
  var wrap = resolverEl(opciones.wrap) || canvas.parentElement;
  var btnLimpiar = resolverEl(opciones.btnLimpiar);
  var estadoEl = resolverEl(opciones.estado);

  var formato = opciones.formato || "image/jpeg";
  var calidad = opciones.calidad !== undefined ? opciones.calidad : 0.85;
  var claseFirmado = opciones.claseFirmado || "firmado";
  var claseActivo = opciones.claseActivo || null;
  var textoFirmado = opciones.textoFirmado || "✔ Firmado";
  var textoVacio = opciones.textoVacio || "Sin firma";
  var claseTextoOk = opciones.claseTextoOk || "ok";
  var dpiAware = opciones.dpiAware !== false;
  var tamanoFijo = opciones.tamanoFijo || null; // { width, height } en px CSS
  var capturaEnVivo = !!opciones.capturaEnVivo;
  var capturaEnSuelta = !!opciones.capturaEnSuelta;
  var restaurarDesdeHidden = !!opciones.restaurarDesdeHidden;
  var onCambio = typeof opciones.onCambio === "function" ? opciones.onCambio : function () {};

  var ctx = canvas.getContext("2d");
  var dibujando = false;
  var tieneFirma = false;

  function marcarEstado() {
    if (wrap) wrap.classList.add(claseFirmado);
    if (estadoEl) {
      estadoEl.textContent = textoFirmado;
      estadoEl.classList.add(claseTextoOk);
    }
  }

  function marcar() {
    if (!tieneFirma) {
      tieneFirma = true;
      marcarEstado();
    }
  }

  // Exporta el canvas a base64 sobre un lienzo temporal con fondo blanco
  // (evita el bug de imágenes JPEG negras: JPEG no soporta transparencia,
  // así que un canvas vacío/transparente se exportaba en negro sólido si no
  // se compone antes sobre blanco).
  function exportarConFondoBlanco() {
    var temp = document.createElement("canvas");
    temp.width = canvas.width;
    temp.height = canvas.height;
    var tctx = temp.getContext("2d");
    tctx.fillStyle = "#ffffff";
    tctx.fillRect(0, 0, temp.width, temp.height);
    tctx.drawImage(canvas, 0, 0);
    return temp.toDataURL(formato, calidad);
  }

  // Captura manual/segura: solo exporta si de verdad hay trazo dibujado,
  // nunca sobre un canvas vacío.
  function capturar() {
    if (!tieneFirma) return null;
    var dataUrl = exportarConFondoBlanco();
    if (hidden) hidden.value = dataUrl;
    onCambio(dataUrl);
    return dataUrl;
  }

  var primerLlamado = true;

  function redimensionar() {
    var rect = canvas.getBoundingClientRect();
    var width = tamanoFijo ? tamanoFijo.width : rect.width;
    var height = tamanoFijo ? tamanoFijo.height : rect.height || 180;

    if (!tamanoFijo && (width === 0 || height === 0)) {
      // El canvas todavía no tiene tamaño (ej. está dentro de un paso
      // oculto del wizard) — reintentar en el próximo tick.
      setTimeout(redimensionar, 200);
      return;
    }

    // Si ya había un trazo dibujado, se preserva en un snapshot antes de
    // redimensionar (para no perder la firma si el usuario cambia el
    // tamaño de la ventana mientras dibuja).
    var snapshotPrevio =
      !primerLlamado && tieneFirma && canvas.width > 0 && canvas.height > 0
        ? canvas.toDataURL("image/png")
        : null;

    var ratio = dpiAware ? window.devicePixelRatio || 1 : 1;

    canvas.width = width * ratio;
    canvas.height = height * ratio;

    if (dpiAware || tamanoFijo) {
      canvas.style.width = width + "px";
      canvas.style.height = height + "px";
    }

    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(ratio, ratio);

    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, width, height);

    ctx.strokeStyle = "#1a202c";
    ctx.lineWidth = 2;
    ctx.lineCap = "round";
    ctx.lineJoin = "round";

    if (snapshotPrevio) {
      var imgSnapshot = new Image();
      imgSnapshot.onload = function () {
        ctx.drawImage(imgSnapshot, 0, 0, width, height);
      };
      imgSnapshot.src = snapshotPrevio;
    } else if (
      primerLlamado &&
      restaurarDesdeHidden &&
      hidden &&
      hidden.value &&
      hidden.value !== "data:,"
    ) {
      var valorPrevio = hidden.value;
      var img = new Image();
      img.onload = function () {
        ctx.fillStyle = "#ffffff";
        ctx.fillRect(0, 0, width, height);
        ctx.drawImage(img, 0, 0, width, height);
        hidden.value = valorPrevio;
        marcar();
      };
      img.src = valorPrevio;
    }

    primerLlamado = false;
  }

  redimensionar();
  if (!tamanoFijo) {
    window.addEventListener("resize", redimensionar);
  }

  function posMouse(e) {
    var r = canvas.getBoundingClientRect();
    return { x: e.clientX - r.left, y: e.clientY - r.top };
  }

  function posTouch(e) {
    var r = canvas.getBoundingClientRect();
    var t = e.touches[0];
    return { x: t.clientX - r.left, y: t.clientY - r.top };
  }

  function iniciarTrazo(p) {
    dibujando = true;
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
    if (claseActivo && wrap) wrap.classList.add(claseActivo);
  }

  function trazar(p) {
    if (!dibujando) return;
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    marcar();
    if (capturaEnVivo) {
      if (hidden) hidden.value = exportarConFondoBlanco();
      onCambio(hidden ? hidden.value : null);
    }
  }

  function soltarTrazo() {
    dibujando = false;
    if (capturaEnSuelta) capturar();
  }

  canvas.addEventListener("mousedown", function (e) {
    iniciarTrazo(posMouse(e));
  });
  canvas.addEventListener("mousemove", function (e) {
    trazar(posMouse(e));
  });
  canvas.addEventListener("mouseup", soltarTrazo);
  canvas.addEventListener("mouseleave", function () {
    dibujando = false;
  });

  canvas.addEventListener(
    "touchstart",
    function (e) {
      e.preventDefault();
      iniciarTrazo(posTouch(e));
    },
    { passive: false },
  );
  canvas.addEventListener(
    "touchmove",
    function (e) {
      e.preventDefault();
      trazar(posTouch(e));
    },
    { passive: false },
  );
  canvas.addEventListener("touchend", soltarTrazo);

  function limpiar() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = "#ffffff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    if (hidden) hidden.value = "";
    tieneFirma = false;
    if (wrap) {
      wrap.classList.remove(claseFirmado);
      if (claseActivo) wrap.classList.remove(claseActivo);
    }
    if (estadoEl) {
      estadoEl.textContent = textoVacio;
      estadoEl.classList.remove(claseTextoOk);
    }
    onCambio(null);
  }

  if (btnLimpiar) {
    btnLimpiar.addEventListener("click", limpiar);
  }

  return {
    capturar: capturar,
    limpiar: limpiar,
    tieneFirma: function () {
      return tieneFirma;
    },
  };
}
