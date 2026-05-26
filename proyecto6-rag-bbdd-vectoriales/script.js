const formulario = document.getElementById("formulario");
const pregunta = document.getElementById("pregunta");
const respuesta = document.getElementById("respuesta");
const contexto = document.getElementById("contexto");
const estado = document.getElementById("estado");
const numDocumentos = document.getElementById("numDocumentos");
const btnEnviar = document.getElementById("btnEnviar");
const botonesEjemplo = document.querySelectorAll(".ejemplo");

formulario.addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const textoPregunta = pregunta.value.trim();

    if (textoPregunta === "") {
        return;
    }

    btnEnviar.disabled = true;
    btnEnviar.textContent = "Consultando...";
    estado.textContent = "Buscando en ChromaDB";
    numDocumentos.textContent = "Buscando...";

    respuesta.textContent = "Recuperando contexto desde la base vectorial y generando respuesta...";
    contexto.textContent = "Consultando ChromaDB...";

    try {
        const peticion = await fetch("rag.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                pregunta: textoPregunta
            })
        });

        const datos = await peticion.json();

        if (datos.error) {
            respuesta.textContent = datos.error;

            if (datos.debug) {
                contexto.textContent = datos.debug;
            } else {
                contexto.textContent = "No se ha podido recuperar contexto.";
            }

            estado.textContent = "Error";
            numDocumentos.textContent = "0 párrafos";
            return;
        }

        respuesta.innerHTML = formatearRespuesta(datos.respuesta);
        contexto.innerHTML = pintarContexto(datos.contexto);

        estado.textContent = "Respuesta generada";
        numDocumentos.textContent = datos.contexto.length + " párrafos";

    } catch (error) {
        respuesta.textContent = "No se ha podido conectar con el servidor PHP.";
        contexto.textContent = "Error de conexión.";
        estado.textContent = "Error";
        numDocumentos.textContent = "0 párrafos";
    } finally {
        btnEnviar.disabled = false;
        btnEnviar.textContent = "Consultar base vectorial";
    }
});

botonesEjemplo.forEach(function (boton) {
    boton.addEventListener("click", function () {
        pregunta.value = boton.textContent;
        pregunta.focus();
    });
});

function pintarContexto(documentos) {
    if (!documentos || documentos.length === 0) {
        return "No se han encontrado párrafos relacionados.";
    }

    return documentos.map(function (doc) {
        return `
            <div class="contexto-item">
                <strong>${escaparHTML(doc.etiqueta)} - índice ${escaparHTML(doc.indice)}</strong>
                <p>${escaparHTML(doc.contenido)}</p>
            </div>
        `;
    }).join("");
}

function formatearRespuesta(texto) {
    if (!texto) {
        return "";
    }

    let textoSeguro = escaparHTML(texto);

    textoSeguro = textoSeguro.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");
    textoSeguro = textoSeguro.replace(/\n/g, "<br>");

    return textoSeguro;
}

function escaparHTML(texto) {
    return String(texto)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
