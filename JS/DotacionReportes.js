(function () {
  const btnAbrir = document.getElementById("btnInformeDotaciones");

  const modal = document.getElementById("modalInformeDotaciones");

  const btnCerrar = document.getElementById("cerrarModalInforme");

  const btnCancelar = document.getElementById("cancelarInforme");

  const btnBuscar = document.getElementById("btnBuscarInforme");

  const tabla = document.getElementById("tablaInformeDotaciones");

  const btnPdf = document.getElementById("btnGenerarInforme");

  btnPdf?.addEventListener("click", function () {
    const seleccionados = Array.from(
      document.querySelectorAll(".check-persona:checked"),
    ).map((check) => check.value);

    if (!seleccionados.length) {
      return;
    }

    const form = document.createElement("form");

    form.method = "POST";

    form.action = "../Controller/GenerarReporteDotaciones.php";

    form.target = "_blank";

    form.style.display = "none";

    const codigos = document.createElement("input");

    codigos.name = "codigos";

    codigos.value = JSON.stringify(seleccionados);

    form.appendChild(codigos);

    const inicio = document.createElement("input");

    inicio.name = "fechaInicio";

    inicio.value = fechaInicio.value;

    form.appendChild(inicio);

    const fin = document.createElement("input");

    fin.name = "fechaFin";

    fin.value = fechaFin.value;

    form.appendChild(fin);

    document.body.appendChild(form);

    form.submit();

    form.remove();
  });

  const btnSeleccionarTodos = document.getElementById("btnSeleccionarTodos");

  const btnLimpiar = document.getElementById("btnLimpiarSeleccion");

  const contador = document.getElementById("contadorSeleccionados");

  const buscarInput = document.getElementById("buscarEmpleadoInforme");

  const fechaInicio = document.getElementById("fechaInicioInforme");

  const fechaFin = document.getElementById("fechaFinInforme");

  function abrirModal() {
    modal.classList.add("active");

    document.body.style.overflow = "hidden";

    // Fecha actual por defecto
    const hoy = new Date().toISOString().split("T")[0];

    if (!fechaInicio.value) {
      fechaInicio.value = hoy;
    }

    if (!fechaFin.value) {
      fechaFin.value = hoy;
    }
  }

  function cerrarModal() {
    modal.classList.remove("active");
    document.body.style.overflow = "";
  }

  btnAbrir?.addEventListener("click", abrirModal);

  btnCerrar?.addEventListener("click", cerrarModal);

  btnCancelar?.addEventListener("click", cerrarModal);

  modal?.addEventListener("click", function (e) {
    if (e.target === modal) {
      cerrarModal();
    }
  });

  btnSeleccionarTodos?.addEventListener("click", function () {
    document
      .querySelectorAll(".check-persona")
      .forEach((check) => (check.checked = true));

    actualizarContador();
  });

  btnLimpiar?.addEventListener("click", function () {
    buscarInput.value = "";

    tabla.innerHTML = `
            <tr>
                <td colspan="5"
                class="estado-vacio-tabla">
                    Selecciona filtros
                    y pulsa buscar.
                </td>
            </tr>
        `;

    contador.textContent = "0 colaboradores seleccionados";

    btnPdf.disabled = true;
  });

  // Buscar registros
  btnBuscar?.addEventListener("click", async function () {
    if (!fechaInicio.value || !fechaFin.value) {
      mostrarToast("Selecciona un rango de fechas.", "aviso");
      return;
    }

    tabla.innerHTML = `
                <tr>
                    <td colspan="5"
                    class="estado-vacio-tabla">
                        Buscando registros...
                    </td>
                </tr>
            `;

    const formData = new FormData();

    formData.append("fechaInicio", fechaInicio.value);

    formData.append("fechaFin", fechaFin.value);

    formData.append("busqueda", buscarInput.value);

    try {
      const response = await fetch(
        "../Controller/BuscarReporteDotaciones.php",
        {
          method: "POST",
          body: formData,
        },
      );

      const data = await response.json();

      if (!data.success) {
        throw new Error(data.message);
      }

      renderizarTabla(data.data);
    } catch (error) {
      tabla.innerHTML = `
                    <tr>
                        <td colspan="5"
                        class="estado-vacio-tabla">
                            Error al buscar registros
                        </td>
                    </tr>
                `;

      console.error(error);
    }
  });

  function renderizarTabla(personas) {
    btnPdf.disabled = true;

    if (!personas.length) {
      tabla.innerHTML = `
                <tr>
                    <td colspan="5"
                    class="estado-vacio-tabla">
                        No hay registros para
                        esos filtros.
                    </td>
                </tr>
            `;

      return;
    }

    tabla.innerHTML = personas
      .map(
        (persona) => `
                <tr>

                    <td>
                        <input
                            type="checkbox"
                            class="check-persona"
                            value="${persona.codigo}">
                    </td>

                    <td>
                        ${persona.codigo}
                    </td>

                    <td>
                        ${persona.nombre}
                    </td>

                    <td>
                        ${persona.centro_costos ?? "-"}
                    </td>

                    <td>
                        ${persona.total_registros}
                    </td>

                </tr>
            `,
      )
      .join("");

    activarChecks();
  }

  function activarChecks() {
    const checks = document.querySelectorAll(".check-persona");

    actualizarContador();

    checks.forEach((check) => {
      check.addEventListener("change", actualizarContador);
    });
  }

  function actualizarContador() {
    const checksSeleccionados = document.querySelectorAll(
      ".check-persona:checked",
    );

    const cantidad = checksSeleccionados.length;

    contador.textContent = `${cantidad} colaborador${
      cantidad !== 1 ? "es" : ""
    } seleccionado${cantidad !== 1 ? "s" : ""}`;

    btnPdf.disabled = cantidad === 0;
  }
})();
