<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto 7 - Stack PHP + Python</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>

    <main class="app">

        <section class="card">

            <header class="header">
                <div>
                    <span class="badge">Proyecto 7</span>
                    <h1>Stack PHP + Python</h1>
                    <p>
                        Mini proyecto donde PHP y Python se comunican para repartir tareas
                        entre ambos lenguajes dentro de una aplicación web.
                    </p>
                </div>

                <div class="estado-ia">
                    <span></span>
                    PHP + Python
                </div>
            </header>

            <section class="contenido">

                <aside class="panel-info">
                    <h2>¿Qué hace?</h2>

                    <p>
                        PHP recibe el texto desde la web y ejecuta un script de Python.
                        Python analiza el contenido y devuelve el resultado en formato JSON.
                    </p>

                    <div class="lista-info">
                        <div>
                            <span>1</span>
                            <p>El usuario escribe un texto.</p>
                        </div>

                        <div>
                            <span>2</span>
                            <p>JavaScript lo envía a PHP.</p>
                        </div>

                        <div>
                            <span>3</span>
                            <p>PHP ejecuta un archivo Python.</p>
                        </div>

                        <div>
                            <span>4</span>
                            <p>Python analiza el texto y devuelve datos.</p>
                        </div>
                    </div>

                    <button type="button" id="btnEjemplo" class="btn-secundario">
                        Cargar ejemplo
                    </button>
                </aside>

                <section class="zona-principal">

                    <section class="formulario-card">
                        <h2>Texto para analizar</h2>

                        <form id="formulario">
                            <textarea 
                                id="texto" 
                                placeholder="Escribe aquí un texto para que Python lo analice..."
                                required
                            ></textarea>

                            <button type="submit" id="btnEnviar">
                                Analizar con Python
                            </button>
                        </form>
                    </section>

                    <section class="resultado-card">
                        <div class="resultado-header">
                            <h2>Resultado del análisis</h2>
                            <span id="estado">Esperando texto</span>
                        </div>

                        <div id="resultado" class="resultado">
                            Aquí aparecerá el análisis generado por Python.
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