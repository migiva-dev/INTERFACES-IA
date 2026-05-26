const formulario = document.getElementById("formulario");
const pregunta = document.getElementById("pregunta");
const respuesta = document.getElementById("respuesta");
const estado = document.getElementById("estado");
const btnEnviar = document.getElementById("btnEnviar");
const botonesEjemplo = document.querySelectorAll(".ejemplo");

formulario.addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const textoPregunta = pregunta.value.trim();

    if (textoPregunta === "") {
        return;
    }

    estado.textContent = "Generando respuesta...";
    btnEnviar.disabled = true;
    btnEnviar.textContent = "Pensando...";

    respuesta.innerHTML = `
        <p><strong>Pregunta:</strong> ${escaparHTML(textoPregunta)}</p>
        <p>La inteligencia artificial está preparando una respuesta...</p>
    `;

    try {
        const peticion = await fetch("respuesta.php", {
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
            respuesta.innerHTML = `<p>${escaparHTML(datos.error)}</p>`;
            estado.textContent = "Error";
            return;
        }

        respuesta.innerHTML = formatearRespuesta(datos.respuesta);
        estado.textContent = "Respuesta generada";

        respuesta.scrollIntoView({
            behavior: "smooth",
            block: "start"
        });

    } catch (error) {
        respuesta.innerHTML = `
            <p>No se ha podido conectar con el servidor PHP.</p>
        `;
        estado.textContent = "Error de conexión";
    } finally {
        btnEnviar.disabled = false;
        btnEnviar.textContent = "Generar respuesta";
    }
});

botonesEjemplo.forEach(function (boton) {
    boton.addEventListener("click", function () {
        pregunta.value = boton.textContent;
        pregunta.focus();
    });
});

function formatearRespuesta(texto) {
    if (!texto) {
        return "";
    }

    let textoSeguro = escaparHTML(texto);

    textoSeguro = textoSeguro.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

    const lineas = textoSeguro
        .split("\n")
        .map(linea => linea.trim())
        .filter(linea => linea !== "");

    let html = "";
    let dentroListaNumerada = false;
    let dentroListaPuntos = false;

    lineas.forEach(function (linea) {
        linea = linea.replace(/^:\s*/, "");

        const listaNumerada = linea.match(/^(\d+)\.\s+(.*)$/);
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

function escaparHTML(texto) {
    return texto
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}