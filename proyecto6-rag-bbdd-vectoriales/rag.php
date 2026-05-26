<?php

header("Content-Type: application/json; charset=utf-8");

date_default_timezone_set("Europe/Madrid");

$modeloOllama = "llama3.2:1b";

$entrada = json_decode(file_get_contents("php://input"), true);

if (!isset($entrada["pregunta"]) || trim($entrada["pregunta"]) === "") {
    echo json_encode([
        "error" => "No se ha recibido ninguna pregunta."
    ]);
    exit;
}

$preguntaUsuario = trim($entrada["pregunta"]);

function limpiarRespuestaModelo($texto) {
    $tokens = [
        "<|start_header_id|>",
        "<|end_header_id|>",
        "<|eot_id|>",
        "<|begin_of_text|>",
        "<|end_of_text|>",
        "assistant<|end_header_id|>",
        "user<|end_header_id|>",
        "system<|end_header_id|>"
    ];

    $texto = str_replace($tokens, "", $texto);
    $texto = preg_replace('/^\s*(assistant|user|system)\s*/i', '', $texto);

    return trim($texto);
}

$cachePath = __DIR__ . "/.cache";

if (!file_exists($cachePath)) {
    mkdir($cachePath, 0775, true);
}

$comando = "cd " . escapeshellarg(__DIR__)
    . " && HOME=" . escapeshellarg(__DIR__)
    . " XDG_CACHE_HOME=" . escapeshellarg($cachePath)
    . " timeout 30 ./venv/bin/python buscar_contexto.py "
    . escapeshellarg($preguntaUsuario);

$salidaPython = shell_exec($comando);

$datosPython = json_decode($salidaPython, true);

if (!is_array($datosPython)) {
    echo json_encode([
        "error" => "Python no ha devuelto un JSON válido.",
        "debug" => $salidaPython
    ]);
    exit;
}

if (!isset($datosPython["ok"]) || $datosPython["ok"] !== true) {
    echo json_encode([
        "error" => $datosPython["error"] ?? "No se ha podido recuperar contexto desde ChromaDB.",
        "contexto" => []
    ]);
    exit;
}

$contexto = $datosPython["contexto"];

if (!is_array($contexto) || count($contexto) === 0) {
    echo json_encode([
        "error" => "No se ha encontrado contexto relacionado.",
        "contexto" => []
    ]);
    exit;
}

$contextoTexto = "";

$contextoReducido = array_slice($contexto, 0, 3);

foreach ($contextoReducido as $item) {
    $etiqueta = $item["etiqueta"] ?? "Sin etiqueta";
    $indice = $item["indice"] ?? "-";
    $contenido = $item["contenido"] ?? "";

    $contenido = mb_substr($contenido, 0, 900, "UTF-8");

    $contextoTexto .= "[" . $etiqueta . " - índice " . $indice . "]\n";
    $contextoTexto .= $contenido . "\n\n";
}

$prompt = "
Eres un profesor de ciclos formativos de informática.

IMPORTANTE:
En este proyecto, cuando el usuario escriba DAW, siempre significa Desarrollo de Aplicaciones Web.
No significa Digital Audio Workstation ni programas de audio.
No respondas sobre música, sonido, Ableton, Logic, Cubase ni software de audio.

Debes responder usando únicamente el contexto recuperado desde la base vectorial.

Contexto recuperado:
$contextoTexto

Pregunta del usuario:
$preguntaUsuario

Instrucciones:
- Responde siempre en español.
- Responde como profesor ayudando a un alumno.
- Usa solo la información del contexto.
- Si el contexto habla de Desarrollo de Aplicaciones Web, responde sobre ese ciclo.
- Si el contexto no es suficiente, dilo claramente.
- No inventes información.
- Máximo 12 líneas.
";

$datosOllama = [
    "model" => $modeloOllama,
    "prompt" => $prompt,
    "stream" => false,
    "options" => [
        "num_predict" => 250,
        "temperature" => 0.2,
        "top_p" => 0.8
    ]
];

$ch = curl_init("http://localhost:11434/api/generate");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datosOllama));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$respuestaOllama = curl_exec($ch);

if ($respuestaOllama === false) {
    echo json_encode([
        "error" => "No se ha podido conectar con Ollama: " . curl_error($ch),
        "contexto" => $contexto
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$respuestaDecodificada = json_decode($respuestaOllama, true);

if (!isset($respuestaDecodificada["response"])) {
    echo json_encode([
        "error" => "Ollama no ha devuelto una respuesta válida.",
        "debug" => $respuestaDecodificada,
        "contexto" => $contexto
    ]);
    exit;
}

echo json_encode([
    "respuesta" => limpiarRespuestaModelo($respuestaDecodificada["response"]),
    "contexto" => $contexto,
    "indice_central" => $datosPython["indice_central"] ?? null
]);
