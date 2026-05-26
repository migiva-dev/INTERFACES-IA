const formulario = document.getElementById("formulario");
const texto = document.getElementById("texto");
const resultado = document.getElementById("resultado");
const estado = document.getElementById("estado");
const btnEnviar = document.getElementById("btnEnviar");
const btnEjemplo = document.getElementById("btnEjemplo");

formulario.addEventListener("submit", async function (evento) {
    evento.preventDefault();

    const textoUsuario = texto.value.trim();

    if (textoUsuario === "") {
        return;
    }

    btnEnviar.disabled = true;
    btnEnviar.textContent = "Analizando...";
    estado.textContent = "Ejecutando Python";

    resultado.textContent = "PHP está enviando el texto a Python para analizarlo...";

    try {
        const peticion = await fetch("procesar.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                texto: textoUsuario
            })
        });

        const datos = await peticion.json();

        if (datos.error) {
            resultado.textContent = datos.error;

            if (datos.debug) {
                resultado.textContent += "\n\nDEBUG:\n" + datos.debug;
            }

            estado.textContent = "Error";
            return;
        }

        resultado.innerHTML = pintarResultado(datos);
        estado.textContent = "Análisis generado";

    } catch (error) {
        resultado.textContent = "No se ha podido conectar con procesar.php.";
        estado.textContent = "Error";
    } finally {
        btnEnviar.disabled = false;
        btnEnviar.textContent = "Analizar con Python";
    }
});

btnEjemplo.addEventListener("click", function () {
    texto.value = `PHP es un lenguaje de programación del lado del servidor. Python es un lenguaje muy utilizado para análisis de datos, automatización e inteligencia artificial. En este proyecto, PHP se encarga de recibir la información desde la web y Python realiza el análisis del texto.`;
    texto.focus();
});

function pintarResultado(datos) {
    const palabrasFrecuentes = datos.palabras_frecuentes
        .map(item => `<li>${escaparHTML(item.palabra)}: ${item.veces} veces</li>`)
        .join("");

    return `
        <div class="grid-datos">
            <div class="dato">
                <strong>${datos.caracteres}</strong>
                <span>Caracteres</span>
            </div>

            <div class="dato">
                <strong>${datos.palabras}</strong>
                <span>Palabras</span>
            </div>

            <div class="dato">
                <strong>${datos.frases}</strong>
                <span>Frases</span>
            </div>

            <div class="dato">
                <strong>${datos.palabras_unicas}</strong>
                <span>Palabras únicas</span>
            </div>
        </div>

        <div class="bloque">
            <h3>Valoración</h3>
            <p>${escaparHTML(datos.valoracion)}</p>
        </div>

        <div class="bloque">
            <h3>Palabras más repetidas</h3>
            <ul>
                ${palabrasFrecuentes}
            </ul>
        </div>

        <div class="bloque">
            <h3>Resumen técnico</h3>
            <p>
                El texto ha sido recibido por PHP, procesado por Python y devuelto a la web
                en formato JSON.
            </p>
        </div>
    `;
}

function escaparHTML(texto) {
    return String(texto)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}