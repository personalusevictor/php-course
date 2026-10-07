(() => {
  const RED = new Set([
    1, 3, 5, 7, 9, 12, 14, 16, 18, 19, 21, 23, 25, 27, 30, 32, 34, 36,
  ]);
  const ORDER = [
    0, 32, 15, 19, 4, 21, 2, 25, 17, 34, 6, 27, 13, 36, 11, 30, 8, 23, 10, 5,
    24, 16, 33, 1, 20, 14, 31, 9, 22, 18, 29, 7, 28, 12, 35, 3, 26,
  ];
  const fmt = new Intl.NumberFormat("es-ES", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  const $ = (s) => document.querySelector(s);
  const wheelSvg = $("#wheel");
  const rotator = $("#wheelRotator");
  const table = $("#table");
  const selected = $("#selected");
  const total = $("#tableTotal");
  const spin = $("#spin");
  const clear = $("#clear");
  const balance = $("#balance");
  const pending = $("#pendingTotal");
  const current = $("#current");
  const toast = $("#toast");
  const custom = $("#custom");
  const wheelNumber = $("#wheelNumber");
  let chip = 1;
  let busy = false;
  let wheelRotation = 0;

  function money(n) {
    return fmt.format(Number(n) || 0) + " €";
  }
  function key(t, c) {
    return `${t}:${c}`;
  }
  function setChip(value) {
    value = Number(String(value).replace(",", "."));
    if (!Number.isFinite(value) || value < 1 || value > 100000) return false;
    chip = Math.round(value * 100) / 100;
    selected.textContent = money(chip);
    document
      .querySelectorAll("[data-chip]")
      .forEach((b) =>
        b.classList.toggle("active", Number(b.dataset.chip) === chip),
      );
    return true;
  }

  function polar(cx, cy, r, deg) {
    const rad = ((deg - 90) * Math.PI) / 180;
    return [cx + r * Math.cos(rad), cy + r * Math.sin(rad)];
  }
  function buildWheel() {
    rotator.innerHTML = "";
    const cx = 210,
      cy = 210,
      pocketR = 166,
      pocketW = 29,
      pocketH = 48;
    ORDER.forEach((n, i) => {
      const a = (i * 360) / ORDER.length;
      const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
      g.setAttribute("transform", `rotate(${a} ${cx} ${cy})`);
      const rect = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "rect",
      );
      rect.setAttribute("x", cx - pocketW / 2);
      rect.setAttribute("y", cy - pocketR);
      rect.setAttribute("width", pocketW);
      rect.setAttribute("height", pocketH);
      rect.setAttribute("rx", "5");
      rect.setAttribute(
        "class",
        `pocket-svg ${n === 0 ? "green" : RED.has(n) ? "red" : "black"}`,
      );
      const text = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "text",
      );
      text.setAttribute("x", cx);
      text.setAttribute("y", cy - pocketR + 30);
      text.setAttribute("text-anchor", "middle");
      text.setAttribute("class", "pocket-text");
      text.textContent = n;
      g.append(rect, text);
      rotator.appendChild(g);
    });
  }

  function drawServerBets(bets) {
    const byKey = new Map(
      bets.map((b) => [key(String(b.tipo), String(b.eleccion)), b]),
    );
    document.querySelectorAll(".cell").forEach((cell) => {
      const found = byKey.get(key(cell.dataset.type, cell.dataset.choice));
      cell.classList.toggle("draft", Boolean(found));
      cell.querySelectorAll(".betchip").forEach((node) => node.remove());
      if (found) {
        const chipNode = document.createElement("i");
        chipNode.className = "betchip";
        chipNode.setAttribute(
          "aria-label",
          `Apuesta de ${money(found.cantidad)}`,
        );
        chipNode.textContent = money(found.cantidad);
        cell.appendChild(chipNode);
      }
    });
    const stake = bets.reduce((s, b) => s + Number(b.cantidad), 0);
    total.textContent = money(stake);
    current.innerHTML = bets.length
      ? bets
          .map((b) => `<span>${label(b)} · ${money(b.cantidad)}</span>`)
          .join("")
      : "<em>Sin apuestas en mesa</em>";
  }
  function label(b) {
    const labels = {
      rojo: "Rojo",
      negro: "Negro",
      par: "Par",
      impar: "Impar",
      primera: "1ª docena",
      segunda: "2ª docena",
      tercera: "3ª docena",
      bajo: "1–18",
      alto: "19–36",
    };
    return b.tipo === "numero"
      ? `Número ${b.eleccion}`
      : labels[b.eleccion] || b.eleccion;
  }
  function render(d) {
    balance.textContent = money(d.dinero);
    pending.textContent = money(d.saldoPendiente);
    $("#totalMoney").textContent = money(d.totalCapital);
    $("#count").textContent = d.historial.length;
    drawServerBets(d.apuestasPendientes || []);
    renderHistory(d.historial || []);
    if (d.ronda) animateToRound(d.ronda);
    if (d.mensaje) msg(d.mensaje, d.tipoMensaje);
  }
  function renderHistory(list) {
    $("#historyList").innerHTML = list.length
      ? list
          .map((h) => {
            const stake = Number(h.totalApostado),
              net = Number(h.resultado);
            const bets = Array.isArray(h.apuestas) ? h.apuestas : [];
            const betHtml = bets.length
              ? `<div class="history-bets">${bets.map((b) => `<span class="${b.ganada ? "won" : "lost"}">${label(b)} · ${money(b.cantidad)}${b.ganada ? " · +" + money(b.premio) : ""}</span>`).join("")}</div>`
              : "";
            return `<article class="history-card"><div class="hball ${h.color}">${h.numero}</div><div class="history-main"><b>${cap(h.color)}</b><small>${h.fecha} · ${stake > 0 ? money(stake) + " apostados" : "Sin apuesta"}</small>${betHtml}</div><strong class="${stake === 0 ? "muted" : net >= 0 ? "positive" : "negative"}">${stake === 0 ? "—" : (net >= 0 ? "+" : "") + money(net)}</strong></article>`;
          })
          .join("")
      : "<p>Los giros aparecerán aquí, aunque no hayas apostado.</p>";
  }
  function cap(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
  }
  function markWinner(r) {
    document.querySelector(".cell.winner")?.classList.remove("winner");
    const winner = document.querySelector(
      `.cell[data-type="numero"][data-choice="${r.numero}"]`,
    );
    winner?.classList.add("winner");
    setTimeout(() => winner?.classList.remove("winner"), 3200);
  }
  function setTheme(color) {
    document.body.classList.remove("theme-rojo", "theme-negro", "theme-verde");
    document.body.classList.add("theme-" + color);
  }
  function updateResultUI(r) {
    wheelNumber.textContent = r.numero;
    $("#resultBall").className = "ball ball--" + r.color;
    $("#resultBall").textContent = r.numero;
    $("#resultText").textContent = `${cap(r.color)} · ${r.numero}`;
    $("#lastResult").textContent = `${r.numero} · ${cap(r.color)}`;
    const net = Number(r.resultado);
    $("#resultNet").textContent =
      Number(r.totalApostado) > 0
        ? (net > 0 ? "+" : "") + money(net)
        : "Sin apuesta";
    $("#resultNet").className =
      Number(r.totalApostado) === 0
        ? "muted"
        : net > 0
          ? "positive"
          : net < 0
            ? "negative"
            : "";
    setTheme(r.color);
    markWinner(r);
  }
  function ease(t) {
    return 1 - Math.pow(1 - t, 4);
  }
  function animateToRound(r) {
    const index = Number(r.wheelIndex);
    const pocketAngle = index * (360 / ORDER.length);
    const currentMod = ((wheelRotation % 360) + 360) % 360;
    const desiredMod = (((360 - pocketAngle) % 360) + 360) % 360;
    let delta = (desiredMod - currentMod + 360) % 360;
    delta += (7 + Math.floor(Math.random() * 2)) * 360;
    const startWheel = wheelRotation;
    const endWheel = wheelRotation + delta;
    const start = performance.now();
    const duration = 4300;
    wheelSvg.classList.add("spinning");
    const frame = (now) => {
      const p = Math.min(1, (now - start) / duration);
      const e = 1 - Math.pow(1 - p, 4.2);
      wheelRotation = startWheel + (endWheel - startWheel) * e;
      rotator.setAttribute("transform", `rotate(${wheelRotation} 210 210)`);
      if (p < 1) {
        requestAnimationFrame(frame);
        return;
      }
      wheelRotation = endWheel;
      rotator.setAttribute("transform", `rotate(${wheelRotation} 210 210)`);
      wheelSvg.classList.remove("spinning");
      updateResultUI(r);
      busy = false;
      spin.disabled = false;
      clear.disabled = false;
    };
    requestAnimationFrame(frame);
  }
  function msg(text, type = "info") {
    if (!text) return;
    toast.textContent = text;
    toast.className = `toast ${type}`;
    toast.hidden = false;
    clearTimeout(msg.t);
    msg.t = setTimeout(() => (toast.hidden = true), 2300);
  }
  async function post(action, data = {}) {
    const response = await fetch("index.php", {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      body: new URLSearchParams({ accion: action, ...data }),
    });
    if (!response.ok) throw new Error("No se pudo completar la operación.");
    return response.json();
  }
  async function place(cell, mode) {
    if (busy) return;
    try {
      const d = await post("apostar", {
        tipo: cell.dataset.type,
        eleccion: cell.dataset.choice,
        cantidad: chip,
        modo: mode,
      });
      if (!d.ok) throw new Error(d.mensaje);
      render(d);
    } catch (e) {
      msg(e.message, "error");
    }
  }
  async function spinWheel() {
    if (busy) return;
    busy = true;
    spin.disabled = true;
    clear.disabled = true;
    try {
      const d = await post("jugar");
      if (!d.ok) throw new Error(d.mensaje);
      render(d);
    } catch (e) {
      busy = false;
      spin.disabled = false;
      clear.disabled = false;
      msg(e.message, "error");
    }
  }
  document
    .querySelectorAll("[data-chip]")
    .forEach((b) => b.addEventListener("click", () => setChip(b.dataset.chip)));
  $("#useCustom").addEventListener("click", () => {
    if (!setChip(custom.value)) {
      msg("Introduce una cantidad válida de al menos 1 €.", "error");
      custom.focus();
      return;
    }
    msg(`Ficha de ${money(chip)} seleccionada.`, "exito");
  });
  custom.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      $("#useCustom").click();
    }
  });
  table.addEventListener("contextmenu", (e) => {
    const cell = e.target.closest(".cell");
    if (!cell) return;
    e.preventDefault();
    place(cell, "remove");
  });
  table.addEventListener("click", (e) => {
    const cell = e.target.closest(".cell");
    if (!cell) return;
    place(cell, "add");
  });
  clear.addEventListener("click", async () => {
    if (busy) return;
    try {
      const d = await post("vaciar");
      render(d);
    } catch (e) {
      msg(e.message, "error");
    }
  });
  spin.addEventListener("click", spinWheel);
  buildWheel();
  if (window.__INITIAL_LAST__) {
    // Initial render is static; keep the wheel visually aligned with the last result.
    const r = window.__INITIAL_LAST__;
    const angle = Number(r.wheelIndex) * (360 / ORDER.length);
    wheelRotation = (360 - angle) % 360;
    rotator.setAttribute("transform", `rotate(${wheelRotation} 210 210)`);
    setTheme(r.color);
  }
  drawServerBets(window.__INITIAL_BETS__ || []);
})();
