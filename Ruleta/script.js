document.addEventListener("DOMContentLoaded", function () {
  const casillas = document.querySelectorAll("[data-tipo][data-eleccion]");

  const tipoApuesta = document.getElementById("tipoApuesta");
  const eleccionApuesta = document.getElementById("eleccionApuesta");

  const apuestaSeleccionada = document.getElementById("apuestaSeleccionada");

  const botonPreparar = document.getElementById("botonPreparar");

  const cantidad = document.getElementById("cantidad");

  const formularioApuesta = document.getElementById("formularioApuesta");

  const ruletaRotor = document.getElementById("ruletaRotor");

  const bola = document.getElementById("bola");

  const botonGirar = document.getElementById("botonGirar");

  const resultado = document.body.dataset.resultado;

  const ordenRuleta = [
    0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5,
    24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26,
  ];

  /*
        =====================================================
        SELECCIONAR APUESTA
        =====================================================
    */

  casillas.forEach(function (casilla) {
    casilla.addEventListener("click", function () {
      casillas.forEach(function (elemento) {
        elemento.classList.remove("seleccionada");
      });

      casilla.classList.add("seleccionada");

      const tipo = casilla.dataset.tipo;
      const eleccion = casilla.dataset.eleccion;

      tipoApuesta.value = tipo;
      eleccionApuesta.value = eleccion;

      apuestaSeleccionada.textContent = obtenerTextoApuesta(tipo, eleccion);

      botonPreparar.disabled = false;

      cantidad.focus();
    });
  });

  /*
        =====================================================
        TEXTO DE LA APUESTA
        =====================================================
    */

  function obtenerTextoApuesta(tipo, eleccion) {
    switch (tipo) {
      case "numero":
        return "Número " + eleccion;

      case "color":
        return eleccion === "rojo" ? "Color rojo" : "Color negro";

      case "paridad":
        return eleccion === "par" ? "Número par" : "Número impar";

      case "docena":
        if (eleccion === "primera") {
          return "1ª docena · 1 - 12";
        }

        if (eleccion === "segunda") {
          return "2ª docena · 13 - 24";
        }

        return "3ª docena · 25 - 36";

      case "altoBajo":
        if (eleccion === "bajo") {
          return "Bajo · 1 - 18";
        }

        return "Alto · 19 - 36";

      default:
        return "Selecciona una casilla";
    }
  }

  /*
        =====================================================
        VALIDACION VISUAL DEL FORMULARIO
        =====================================================
    */

  if (cantidad) {
    cantidad.addEventListener("input", function () {
      const valor = parseFloat(cantidad.value);

      if (
        !isNaN(valor) &&
        valor >= 1 &&
        tipoApuesta.value !== "" &&
        eleccionApuesta.value !== ""
      ) {
        botonPreparar.disabled = false;
      } else {
        botonPreparar.disabled = true;
      }
    });
  }

  /*
        =====================================================
        AL ENVIAR UNA APUESTA
        =====================================================
    */

  if (formularioApuesta) {
    formularioApuesta.addEventListener("submit", function (event) {
      const valor = parseFloat(cantidad.value);

      if (tipoApuesta.value === "" || eleccionApuesta.value === "") {
        event.preventDefault();

        alert("Selecciona primero una apuesta en la mesa.");

        return;
      }

      if (isNaN(valor) || valor < 1) {
        event.preventDefault();

        alert("La cantidad mínima es de 1 €.");

        return;
      }
    });
  }

  /*
        =====================================================
        ANIMACION DE LA RULETA
        =====================================================
    */

  if (resultado !== "") {
    const numeroGanador = parseInt(resultado, 10);

    const indice = ordenRuleta.indexOf(numeroGanador);

    if (indice !== -1 && ruletaRotor) {
      const gradosPorCasilla = 360 / ordenRuleta.length;

      const posicionObjetivo = indice * gradosPorCasilla;

      const vueltas = 360 * 7;

      const rotacionFinal = vueltas - posicionObjetivo;

      setTimeout(function () {
        ruletaRotor.classList.add("girando");

        ruletaRotor.style.transform = "rotate(" + rotacionFinal + "deg)";

        if (bola) {
          bola.classList.add("girando");
        }
      }, 350);
    }
  }

  /*
        =====================================================
        ANIMACION AL PULSAR GIRAR
        =====================================================
    */

  if (botonGirar) {
    botonGirar.addEventListener("click", function () {
      botonGirar.disabled = true;

      botonGirar.innerHTML =
        '<span class="boton-girar-icono">◉</span>' + " GIRANDO...";
    });
  }
});
