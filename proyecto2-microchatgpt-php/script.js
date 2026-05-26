const formulario = document.getElementById("formulario");
const inputMensaje = document.getElementById("mensaje");
const chat = document.getElementById("chat");
const btnBajar = document.getElementById("btnBajar");

// Historial de conversación
let historial = [];

formulario.addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const mensajeUsuario = inputMensaje.value.trim();

    if (mensajeUsuario === "") {
        return;
    }

    agregarMensaje("user", "Tú", mensajeUsuario);

    historial.push({
        role: "user",
        content: mensajeUsuario
    });

    inputMensaje.value = "";

    const mensajeCargando = agregarMensaje("bot", "MicroChatGPT", "Pensando...");

    try {
        const respuesta = await fetch("chat.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                mensaje: mensajeUsuario,
                historial: historial
            })
        });

        const datos = await respuesta.json();

        if (datos.error) {
            cambiarTextoMensaje(mensajeCargando, datos.error);
            return;
        }

        cambiarTextoMensaje(mensajeCargando, datos.respuesta);

        historial.push({
            role: "assistant",
            content: datos.respuesta
        });

    } catch (error) {
        cambiarTextoMensaje(
            mensajeCargando,
            "Error al conectar con el servidor PHP."
        );
    }
});

// Botón para bajar al último mensaje
btnBajar.addEventListener("click", function () {
    bajarChat();
});

// Mostrar el botón solo cuando el usuario sube en el chat
chat.addEventListener("scroll", function () {
    comprobarScroll();
});

function agregarMensaje(tipo, autor, texto) {
    const div = document.createElement("div");
    div.classList.add("message", tipo);

    const avatar = document.createElement("div");
    avatar.classList.add("avatar");
    avatar.textContent = tipo === "user" ? "TÚ" : "IA";

    const bubble = document.createElement("div");
    bubble.classList.add("bubble");

    const strong = document.createElement("strong");
    strong.textContent = autor;

    const contenido = document.createElement("div");
    contenido.classList.add("contenido-respuesta");
    contenido.innerHTML = formatearRespuesta(texto);

    bubble.appendChild(strong);
    bubble.appendChild(contenido);

    div.appendChild(avatar);
    div.appendChild(bubble);

    chat.appendChild(div);

    bajarChat();

    return div;
}

function cambiarTextoMensaje(elementoMensaje, nuevoTexto) {
    const contenido = elementoMensaje.querySelector(".contenido-respuesta");
    contenido.innerHTML = formatearRespuesta(nuevoTexto);

    bajarChat();
}

function bajarChat() {
    setTimeout(() => {
        chat.scrollTo({
            top: chat.scrollHeight,
            behavior: "smooth"
        });

        btnBajar.classList.remove("visible");
    }, 80);
}

function comprobarScroll() {
    const distanciaAlFinal = chat.scrollHeight - chat.scrollTop - chat.clientHeight;

    if (distanciaAlFinal > 120) {
        btnBajar.classList.add("visible");
    } else {
        btnBajar.classList.remove("visible");
    }
}

/*
    Convierte la respuesta de la IA en HTML más limpio:
    - **texto** pasa a negrita
    - Listas numeradas se convierten en <ol>
    - Listas con guion se convierten en <ul>
    - Párrafos separados se muestran correctamente
*/
function formatearRespuesta(texto) {
    if (!texto) {
        return "";
    }

    let textoSeguro = escaparHTML(texto);

    // Negrita: **texto**
    textoSeguro = textoSeguro.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

    const lineas = textoSeguro
        .split("\n")
        .map(linea => linea.trim())
        .filter(linea => linea !== "");

    let html = "";
    let dentroListaNumerada = false;
    let dentroListaPuntos = false;

    lineas.forEach(linea => {

        // Limpia casos raros donde la IA pone ":" al principio
        linea = linea.replace(/^:\s*/, "");

        // Detecta lista numerada: 1. texto
        const listaNumerada = linea.match(/^(\d+)\.\s+(.*)$/);

        // Detecta lista con guion: - texto
        const listaPuntos = linea.match(/^[-*]\s+(.*)$/);

        if (listaNumerada) {
            if (!dentroListaNumerada) {
                cerrarListas();
                html += "<ol>";
                dentroListaNumerada = true;
            }

            html += `<li>${listaNumerada[2]}</li>`;
            return;
        }

        if (listaPuntos) {
            if (!dentroListaPuntos) {
                cerrarListas();
                html += "<ul>";
                dentroListaPuntos = true;
            }

            html += `<li>${listaPuntos[1]}</li>`;
            return;
        }

        cerrarListas();

        // Si es una línea tipo título corto, la ponemos como subtítulo
        if (
            linea.length < 80 &&
            !linea.endsWith(".") &&
            !linea.endsWith(",") &&
            !linea.includes(":")
        ) {
            html += `<h3>${linea}</h3>`;
        } else {
            html += `<p>${linea}</p>`;
        }
    });

    cerrarListas();

    function cerrarListas() {
        if (dentroListaNumerada) {
            html += "</ol>";
            dentroListaNumerada = false;
        }

        if (dentroListaPuntos) {
            html += "</ul>";
            dentroListaPuntos = false;
        }
    }

    return html;
}

/*
    Evita que el navegador interprete HTML peligroso escrito por la IA.
*/
function escaparHTML(texto) {
    return texto
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}