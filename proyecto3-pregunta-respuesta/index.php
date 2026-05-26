<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto 3 - Pregunta y respuesta con IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Forzamos recarga del CSS -->
    <link rel="stylesheet" href="style.css?v=20">
</head>
<body>

    <main class="app">

        <section class="card">

            <header class="header">
                <div>
                    <span class="badge">Proyecto 3</span>
                    <h1>Pregunta y respuesta con IA</h1>
                    <p>
                        Aplicación de repaso donde el usuario escribe una pregunta
                        y recibe una respuesta generada por inteligencia artificial local.
                    </p>
                </div>

                <div class="estado-ia">
                    <span></span>
                    Ollama activo
                </div>
            </header>

            <section class="contenido">

                <aside class="bloque-info">
                    <h2>Modo repaso</h2>
                    <p>
                        Escribe una pregunta sobre cualquier tema de clase.
                        La IA responderá de forma clara, ordenada y con un enfoque educativo.
                    </p>

                    <div class="ejemplos">
                        <button type="button" class="ejemplo">¿Qué es una API?</button>
                        <button type="button" class="ejemplo">¿Qué es JavaScript?</button>
                        <button type="button" class="ejemplo">Explícame qué es JSON</button>
                        <button type="button" class="ejemplo">¿Qué es una API REST?</button>
                    </div>
                </aside>

                <section class="zona-principal">

                    <form id="formulario" class="formulario">
                        <label for="pregunta">Escribe tu pregunta</label>

                        <textarea 
                            id="pregunta" 
                            placeholder="Ejemplo: ¿Qué es una API REST?"
                            required
                        ></textarea>

                        <button type="submit" id="btnEnviar">
                            Generar respuesta
                        </button>
                    </form>

                    <section class="resultado">
                        <div class="resultado-header">
                            <h2>Respuesta de la IA</h2>
                            <span id="estado">Esperando pregunta</span>
                        </div>

                        <div id="respuesta" class="respuesta">
                            <p>
                                Aquí aparecerá la respuesta generada por la inteligencia artificial.
                            </p>
                        </div>
                    </section>

                </section>

            </section>

            <footer class="footer">
                <a href="../" class="volver">Volver al listado</a>
            </footer>

        </section>

    </main>

    <script src="script.js?v=20"></script>
</body>
</html>