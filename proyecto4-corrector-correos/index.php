<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyecto 4 - Corrector de correos con IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Forzamos recarga del CSS -->
    <link rel="stylesheet" href="style.css?v=20">
</head>
<body>

    <main class="app">

        <section class="card">

            <header class="header">
                <div>
                    <span class="badge">Proyecto 4</span>
                    <h1>Corrector de correos con IA</h1>
                    <p>
                        Aplicación práctica para corregir, mejorar y formatear correos electrónicos
                        usando inteligencia artificial local con Ollama.
                    </p>
                </div>

                <div class="estado-ia">
                    <span></span>
                    Ollama activo
                </div>
            </header>

            <section class="contenido">

                <aside class="panel-info">
                    <h2>¿Qué hace?</h2>

                    <p>
                        Esta herramienta recibe un texto escrito como correo electrónico
                        y lo devuelve más claro, ordenado y profesional.
                    </p>

                    <div class="lista-info">
                        <div>
                            <span>1</span>
                            <p>Corrige puntuación y estructura.</p>
                        </div>

                        <div>
                            <span>2</span>
                            <p>Separa el contenido por párrafos.</p>
                        </div>

                        <div>
                            <span>3</span>
                            <p>Añade saludo y despedida si corresponde.</p>
                        </div>

                        <div>
                            <span>4</span>
                            <p>Permite dictar el correo por voz.</p>
                        </div>
                    </div>

                    <button type="button" id="btnEjemplo" class="btn-secundario">
                        Cargar ejemplo
                    </button>
                </aside>

                <section class="zona-trabajo">

                    <section class="columna">
                        <div class="columna-header">
                            <h2>Correo original</h2>
                            <span id="estadoEntrada">Esperando texto</span>
                        </div>

                        <textarea 
                            id="correoOriginal"
                            placeholder="Escribe aquí el correo que quieres corregir o usa el botón de dictado por voz..."
                        ></textarea>

                        <div class="acciones">
                            <button type="button" id="btnDictar" class="btn-voz">
                                🎤 Dictar correo
                            </button>

                            <button type="button" id="btnCorregir" class="btn-principal">
                                Corregir correo
                            </button>

                            <button type="button" id="btnLimpiar" class="btn-limpiar">
                                Limpiar
                            </button>
                        </div>
                    </section>

                    <section class="columna">
                        <div class="columna-header">
                            <h2>Correo corregido</h2>
                            <span id="estadoSalida">Sin resultado</span>
                        </div>

                        <div id="correoCorregido" class="salida">
                            <p>
                                Aquí aparecerá el correo corregido y formateado por la inteligencia artificial.
                            </p>
                        </div>

                        <div class="acciones">
                            <button type="button" id="btnCopiar" class="btn-principal">
                                Copiar resultado
                            </button>
                        </div>
                    </section>

                </section>

            </section>

            <footer class="footer">
                <a href="../" class="volver">Volver al listado</a>
            </footer>

        </section>

    </main>

    <!-- Forzamos recarga del JS -->
    <script src="script.js?v=20"></script>
</body>
</html>