<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto 2 - MicroChatGPT con PHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Forzamos recarga del CSS con ?v=10 -->
    <link rel="stylesheet" href="style.css?v=10">
</head>
<body>

    <main class="app">

        <section class="chat-container">

            <header class="chat-header">
                <div>
                    <span class="badge">Proyecto 2</span>
                    <h1>MicroChatGPT con PHP</h1>
                    <p>Chat local conectado con Ollama mediante PHP</p>
                </div>

                <div class="status">
                    <span></span>
                    Ollama activo
                </div>
            </header>

            <section class="chat-wrapper">

                <section id="chat" class="chat-box">
                    <div class="message bot">
                        <div class="avatar">IA</div>
                        <div class="bubble">
                            <strong>MicroChatGPT</strong>
                            <p>Hola, soy una IA local conectada con Ollama. Escríbeme una pregunta.</p>
                        </div>
                    </div>
                </section>

                <button id="btnBajar" class="btn-bajar" title="Bajar al último mensaje">
                    ↓
                </button>

            </section>

            <form id="formulario" class="chat-form">
                <input 
                    type="text" 
                    id="mensaje" 
                    placeholder="Pregunta lo que quieras..." 
                    autocomplete="off"
                    required
                >

                <button type="submit">
                    Enviar
                </button>
            </form>

        </section>

        <section class="info-panel">
            <h2>¿Qué hace este proyecto?</h2>

            <p>
                Este proyecto crea un pequeño ChatGPT local usando una interfaz web.
                El usuario escribe una pregunta, JavaScript la envía a PHP y PHP se comunica
                con Ollama para obtener la respuesta de la IA.
            </p>

            <div class="steps">
                <div>
                    <span>1</span>
                    <p>El usuario escribe una pregunta.</p>
                </div>

                <div>
                    <span>2</span>
                    <p>JavaScript envía el mensaje a PHP.</p>
                </div>

                <div>
                    <span>3</span>
                    <p>PHP consulta la API local de Ollama.</p>
                </div>

                <div>
                    <span>4</span>
                    <p>La respuesta aparece en el chat.</p>
                </div>
            </div>

            <a href="../" class="volver">Volver al listado</a>
        </section>

    </main>

    <script src="script.js?v=10"></script>
</body>
</html>