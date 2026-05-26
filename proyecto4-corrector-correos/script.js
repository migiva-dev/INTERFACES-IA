const correoOriginal = document.getElementById("correoOriginal");
const correoCorregido = document.getElementById("correoCorregido");

const btnCorregir = document.getElementById("btnCorregir");
const btnLimpiar = document.getElementById("btnLimpiar");
const btnCopiar = document.getElementById("btnCopiar");
const btnEjemplo = document.getElementById("btnEjemplo");
const btnDictar = document.getElementById("btnDictar");

const estadoEntrada = document.getElementById("estadoEntrada");
const estadoSalida = document.getElementById("estadoSalida");

btnCorregir.addEventListener("click", corregirCorreo);
btnLimpiar.addEventListener("click", limpiarCampos);
btnCopiar.addEventListener("click", copiarResultado);
btnEjemplo.addEventListener("click", cargarEjemplo);

/* ============================= */
/* Corrección con PHP + Ollama   */
/* ============================= */

async function corregirCorreo() {
    const texto = correoOriginal.value.trim();

    if (texto === "") {
        estadoEntrada.textContent = "Texto vacío";
        correoOriginal.focus();
        return;
    }

    estadoEntrada.textContent = "Texto enviado";
    estadoSalida.textContent = "Corrigiendo...";
    btnCorregir.disabled = true;
    btnCorregir.textContent = "Corrigiendo...";

    correoCorregido.innerHTML = `
        <p><strong>Procesando correo...</strong></p>
        <p>La inteligencia artificial está corrigiendo y formateando el mensaje.</p>
    `;

    try {
        const peticion = await fetch("corregir.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ correo: texto })
        });

        if (!peticion.ok) {
            throw new Error(`El servidor PHP respondió con error HTTP ${peticion.status}`);
        }

        const datos = await peticion.json();

        if (datos.error) {
            correoCorregido.innerHTML = `<p>${escaparHTML(datos.error)}</p>`;
            estadoSalida.textContent = "Error";
            return;
        }

        correoCorregido.innerHTML = formatearSalida(datos.respuesta);
        estadoSalida.textContent = "Correo corregido";

    } catch (error) {
        correoCorregido.innerHTML = `
            <p><strong>Error:</strong> ${escaparHTML(error.message)}</p>
            <p>Comprueba que Apache y Ollama están activos.</p>
        `;
        estadoSalida.textContent = "Error de conexión";
    } finally {
        btnCorregir.disabled = false;
        btnCorregir.textContent = "Corregir correo";
    }
}

/* ============================= */
/* Acciones de interfaz          */
/* ============================= */

function limpiarCampos() {
    detenerDictado();

    correoOriginal.value = "";
    correoCorregido.innerHTML = `
        <p>
            Aquí aparecerá el correo corregido y formateado por la inteligencia artificial.
        </p>
    `;

    estadoEntrada.textContent = "Esperando texto";
    estadoSalida.textContent = "Sin resultado";

    correoOriginal.focus();
}

function copiarResultado() {
    const texto = correoCorregido.textContent.trim();

    if (texto === "" || texto.includes("Aquí aparecerá")) {
        estadoSalida.textContent = "No hay contenido para copiar";
        return;
    }

    navigator.clipboard.writeText(texto)
        .then(function () {
            estadoSalida.textContent = "Copiado al portapapeles";
        })
        .catch(function () {
            estadoSalida.textContent = "No se pudo copiar";
        });
}

function cargarEjemplo() {
    correoOriginal.value = `hola buenos dias soy miguel queria preguntaros si seria posible cambiar la fecha de la reunion porque ese dia no puedo asistir tengo clase por la tarde y me vendria mejor hacerla el jueves o viernes si podeis confirmarme cuando os viene bien gracias un saludo`;

    estadoEntrada.textContent = "Ejemplo cargado";
    estadoSalida.textContent = "Listo para corregir";
    correoOriginal.focus();
}

/* ============================= */
/* Helpers                       */
/* ============================= */

function formatearSalida(texto) {
    if (!texto) return "";

    let textoSeguro = escaparHTML(texto);
    textoSeguro = textoSeguro.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

    const parrafos = textoSeguro
        .split(/\n\s*\n/)
        .map(p => p.trim())
        .filter(p => p !== "");

    if (parrafos.length === 0) {
        return `<p>${textoSeguro.replace(/\n/g, "<br>")}</p>`;
    }

    return parrafos
        .map(p => `<p>${p.replace(/\n/g, "<br>")}</p>`)
        .join("");
}

function escaparHTML(texto) {
    return texto
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/* ============================= */
/* Dictado por voz               */
/* ============================= */

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

let reconocimiento = null;
let grabando = false;
let textoBase = "";
let textoFinalDictado = "";

if (!btnDictar) {
    console.error("No se ha encontrado el botón btnDictar.");
} else if (!SpeechRecognition) {
    btnDictar.disabled = true;
    btnDictar.textContent = "Voz no disponible";
    estadoEntrada.textContent = "Este navegador no soporta dictado por voz";
} else {
    reconocimiento = new SpeechRecognition();
    reconocimiento.lang = "es-ES";
    reconocimiento.continuous = true;
    reconocimiento.interimResults = true;

    btnDictar.addEventListener("click", function () {
        if (!grabando) {
            iniciarDictado();
        } else {
            detenerDictado();
        }
    });

    reconocimiento.onstart = function () {
        grabando = true;
        btnDictar.classList.add("activo");
        btnDictar.textContent = "⏹ Detener dictado";
        estadoEntrada.textContent = "Escuchando dictado...";
    };

    reconocimiento.onresult = function (evento) {
        let textoTemporal = "";

        for (let i = evento.resultIndex; i < evento.results.length; i++) {
            const texto = evento.results[i][0].transcript;

            if (evento.results[i].isFinal) {
                textoFinalDictado += texto + " ";
            } else {
                textoTemporal += texto;
            }
        }

        correoOriginal.value = textoBase + textoFinalDictado + textoTemporal;
        estadoEntrada.textContent = "Escuchando dictado...";
    };

    reconocimiento.onerror = function (evento) {
        console.error("Error de reconocimiento de voz:", evento.error);

        const mensajes = {
            "not-allowed": "Permiso de micrófono denegado",
            "no-speech": "No se ha detectado voz",
            "audio-capture": "No se ha detectado micrófono",
            "network": "Error de red en el dictado"
        };

        estadoEntrada.textContent = mensajes[evento.error] || "Error en el dictado por voz";

        // No llamamos reconocimiento.stop() aquí porque ya está parado
        // Solo actualizamos el estado visual
        grabando = false;
        btnDictar.classList.remove("activo");
        btnDictar.textContent = "🎤 Dictar correo";
    };

    reconocimiento.onend = function () {
        // onend siempre se dispara al terminar, tanto por stop() como por timeout o error
        // Solo tocamos la UI si grabando sigue true (onerror no lo reseteó ya)
        if (grabando) {
            grabando = false;
            btnDictar.classList.remove("activo");
            btnDictar.textContent = "🎤 Dictar correo";
        }

        if (correoOriginal.value.trim() !== "") {
            estadoEntrada.textContent = "Dictado finalizado";
        } else {
            estadoEntrada.textContent = "Esperando texto";
        }
    };
}

function iniciarDictado() {
    if (!reconocimiento) {
        estadoEntrada.textContent = "Dictado no disponible";
        return;
    }

    textoBase = correoOriginal.value.trim();
    if (textoBase !== "") textoBase += " ";
    textoFinalDictado = "";

    try {
        reconocimiento.start();
    } catch (error) {
        console.error(error);
        estadoEntrada.textContent = "No se pudo iniciar el dictado";
    }
}

function detenerDictado() {
    if (reconocimiento && grabando) {
        reconocimiento.stop();
        // grabando y la UI se actualizan en onend
    }
}