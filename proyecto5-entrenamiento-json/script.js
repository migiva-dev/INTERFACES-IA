const preguntaInput  = document.getElementById("pregunta");
const chat           = document.getElementById("chat");
const btnPreguntar   = document.getElementById("btnPreguntar");
const btnLimpiar     = document.getElementById("btnLimpiar");
const estadoEntrada  = document.getElementById("estadoEntrada");
const fuenteBadge    = document.getElementById("fuenteBadge");

let chatVacio = true;

/* ============================= */
/* Eventos                       */
/* ============================= */

btnPreguntar.addEventListener("click", hacerPregunta);
btnLimpiar.addEventListener("click", limpiarChat);

preguntaInput.addEventListener("keydown", function (e) {
    // Enviar con Enter (sin Shift)
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        hacerPregunta();
    }
});

/* ============================= */
/* Lógica principal              */
/* ============================= */

async function hacerPregunta() {
    const texto = preguntaInput.value.trim();

    if (texto === "") {
        estadoEntrada.textContent = "Escribe una pregunta primero";
        preguntaInput.focus();
        return;
    }

    // Limpiar el estado vacío del chat la primera vez
    if (chatVacio) {
        chat.innerHTML = "";
        chatVacio = false;
    }

    // Mostrar mensaje del usuario
    agregarMensaje("usuario", texto);
    preguntaInput.value = "";
    estadoEntrada.textContent = "Buscando respuesta...";
    btnPreguntar.disabled = true;
    btnPreguntar.textContent = "Buscando...";

    // Actualizar badge
    setFuente("buscando");

    // Mostrar burbuja de "pensando"
    const idPensando = agregarMensajePensando();

    try {
        const peticion = await fetch("responder.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ pregunta: texto })
        });

        if (!peticion.ok) {
            throw new Error(`Error del servidor: HTTP ${peticion.status}`);
        }

        const datos = await peticion.json();

        // Eliminar burbuja de pensando
        eliminarMensaje(idPensando);

        if (datos.error) {
            agregarMensajeRespuesta(datos.error, null);
            estadoEntrada.textContent = "Error";
            setFuente(null);
            return;
        }

        agregarMensajeRespuesta(datos.respuesta, datos.fuente);

        if (datos.fuente === "json") {
            estadoEntrada.textContent = "Respuesta desde JSON";
            setFuente("json");
        } else {
            estadoEntrada.textContent = "Respuesta desde Ollama";
            setFuente("ollama");
        }

    } catch (error) {
        eliminarMensaje(idPensando);
        agregarMensajeRespuesta("No se pudo conectar con el servidor. Comprueba que Apache y Ollama están activos.", null);
        estadoEntrada.textContent = "Error de conexión";
        setFuente(null);
    } finally {
        btnPreguntar.disabled = false;
        btnPreguntar.textContent = "Preguntar →";
    }
}

function limpiarChat() {
    chat.innerHTML = `
        <div class="chat-vacio">
            <div class="chat-icono">💬</div>
            <p>Haz tu primera pregunta para comenzar</p>
        </div>
    `;
    chatVacio = true;
    estadoEntrada.textContent = "Esperando pregunta";
    setFuente(null);
    preguntaInput.focus();
}

/* ============================= */
/* Helpers de chat               */
/* ============================= */

function agregarMensaje(tipo, texto) {
    const div = document.createElement("div");
    div.classList.add("mensaje", tipo);

    const burbuja = document.createElement("div");
    burbuja.classList.add("burbuja");
    burbuja.textContent = texto;

    div.appendChild(burbuja);
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;

    return div;
}

function agregarMensajeRespuesta(texto, fuente) {
    const div = document.createElement("div");
    div.classList.add("mensaje", "respuesta");

    const burbuja = document.createElement("div");
    burbuja.classList.add("burbuja");
    burbuja.innerHTML = formatearTexto(texto);

    div.appendChild(burbuja);

    if (fuente) {
        const etiqueta = document.createElement("span");
        etiqueta.classList.add("etiqueta-fuente", fuente);
        etiqueta.textContent = fuente === "json" ? "📁 Desde JSON" : "🤖 Desde Ollama";
        div.appendChild(etiqueta);
    }

    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;
}

let pensandoId = 0;

function agregarMensajePensando() {
    const id = ++pensandoId;

    const div = document.createElement("div");
    div.classList.add("mensaje", "respuesta");
    div.dataset.pensandoId = id;

    const burbuja = document.createElement("div");
    burbuja.classList.add("burbuja", "pensando");
    burbuja.textContent = "Buscando respuesta...";

    div.appendChild(burbuja);
    chat.appendChild(div);
    chat.scrollTop = chat.scrollHeight;

    return id;
}

function eliminarMensaje(id) {
    const el = chat.querySelector(`[data-pensando-id="${id}"]`);
    if (el) el.remove();
}

/* ============================= */
/* Badge de fuente               */
/* ============================= */

function setFuente(tipo) {
    fuenteBadge.className = "fuente-badge";
    const dot   = fuenteBadge.querySelector(".dot");
    const texto = fuenteBadge.querySelector(".texto");

    if (tipo === "json") {
        fuenteBadge.classList.add("json");
        texto.textContent = "Respuesta desde JSON";
    } else if (tipo === "ollama") {
        fuenteBadge.classList.add("ollama");
        texto.textContent = "Respuesta desde Ollama";
    } else if (tipo === "buscando") {
        texto.textContent = "Buscando...";
    } else {
        texto.textContent = "Esperando pregunta";
    }
}

/* ============================= */
/* Helpers de texto              */
/* ============================= */

function formatearTexto(texto) {
    if (!texto) return "";

    let seguro = escaparHTML(texto);
    seguro = seguro.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

    const parrafos = seguro
        .split(/\n\s*\n/)
        .map(p => p.trim())
        .filter(p => p !== "");

    if (parrafos.length === 0) {
        return seguro.replace(/\n/g, "<br>");
    }

    return parrafos
        .map(p => p.replace(/\n/g, "<br>"))
        .join("<br><br>");
}

function escaparHTML(texto) {
    return texto
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}