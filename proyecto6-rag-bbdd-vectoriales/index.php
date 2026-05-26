<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto 6 - RAG y bases vectoriales</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>

    <main class="app">

        <section class="card">

            <header class="header">
                <div>
                    <span class="badge">Proyecto 6</span>
                    <h1>RAG y bases vectoriales</h1>
                    <p>
                        Aplicación que usa ChromaDB para recuperar párrafos relevantes
                        y Ollama para generar una respuesta basada en ese contexto.
                    </p>
                </div>

                <div class="estado-ia">
                    <span></span>
                    ChromaDB + Ollama
                </div>
            </header>

            <section class="contenido">

                <aside class="panel-info">
                    <h2>Funcionamiento</h2>

                    <p>
                        Este proyecto entrena una base vectorial con un documento de ciclos formativos.
                        Después, cuando el usuario pregunta, recupera los párrafos más relacionados
                        y los usa como contexto para responder.
                    </p>

                    <div class="lista-info">
                        <div>
                            <span>1</span>
                            <p>Python lee ciclosformativos.txt.</p>
                        </div>

                        <div>
                            <span>2</span>
                            <p>ChromaDB guarda los párrafos como documentos vectoriales.</p>
                        </div>

                        <div>
                            <span>3</span>
                            <p>La web envía una pregunta a PHP.</p>
                        </div>

                        <div>
                            <span>4</span>
                            <p>PHP ejecuta Python, recupera contexto y llama a Ollama.</p>
                        </div>
                    </div>

                    <button type="button" class="btn-secundario ejemplo">
                        ¿Qué se estudia en Desarrollo de Aplicaciones Web?
                    </button>

                    <button type="button" class="btn-secundario ejemplo">
                        ¿Qué es seguridad informática?
                    </button>

                    <button type="button" class="btn-secundario ejemplo">
                        ¿Qué se aprende en redes locales?
                    </button>
                </aside>

                <section class="zona-principal">

                    <section class="formulario-card">
                        <h2>Pregunta al sistema RAG</h2>

                        <form id="formulario">
                            <textarea 
                                id="pregunta" 
                                placeholder="Ejemplo: ¿Qué módulos tiene DAW?"
                                required
                            ></textarea>

                            <button type="submit" id="btnEnviar">
                                Consultar base vectorial
                            </button>
                        </form>
                    </section>

                    <section class="resultado-card">
                        <div class="resultado-header">
                            <h2>Respuesta generada</h2>
                            <span id="estado">Esperando pregunta</span>
                        </div>

                        <div id="respuesta" class="respuesta">
                            Aquí aparecerá la respuesta generada por Ollama usando contexto recuperado de ChromaDB.
                        </div>
                    </section>

                    <section class="contexto-card">
                        <div class="resultado-header">
                            <h2>Contexto recuperado</h2>
                            <span id="numDocumentos">0 párrafos</span>
                        </div>

                        <div id="contexto" class="contexto">
                            Todavía no se ha realizado ninguna búsqueda.
                        </div>
                    </section>

                </section>

            </section>

            <footer class="footer">
                <a href="../" class="volver">Volver al listado</a>
            </footer>

        </section>

    </main>

    <script src="script.js?v=1"></script>
</body>
</html>
