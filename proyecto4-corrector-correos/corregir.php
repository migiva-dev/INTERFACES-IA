<?php

header("Content-Type: application/json; charset=utf-8");

date_default_timezone_set("Europe/Madrid");

/*
    Proyecto 4 - Corrector de correos con IA

    Recibe un correo escrito por el usuario,
    lo envía a Ollama y devuelve una versión corregida,
    ordenada y mejor formateada.
*/

$modeloOllama = "llama3.2:1b";

$entradaRaw = file_get_contents("php://input");
$entrada = json_decode($entradaRaw, true);

if ($entrada === null) {
    echo json_encode([
        "error" => "No se ha recibido un JSON válido desde JavaScript."
    ]);
    exit;
}

if (!isset($entrada["correo"]) || trim($entrada["correo"]) === "") {
    echo json_encode([
        "error" => "No se ha recibido ningún correo para corregir."
    ]);
    exit;
}

$correoUsuario = trim($entrada["correo"]);

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

$prompt = "
Corrige y formatea el siguiente correo electrónico.

Normas:
- Devuelve únicamente el correo corregido.
- No expliques lo que has hecho.
- Mantén la idea original.
- No inventes información nueva.
- Corrige ortografía, tildes y signos de puntuación.
- Organiza el correo con saludo, párrafos y despedida si corresponde.
- El resultado debe sonar claro, correcto y profesional.

Correo original:
$correoUsuario
";

$datos = [
    "model" => $modeloOllama,
    "prompt" => $prompt,
    "stream" => false,
    "options" => [
        "num_predict" => 500,
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
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$respuestaOllama = curl_exec($ch);

if ($respuestaOllama === false) {
    echo json_encode([
        "error" => "No se ha podido conectar con Ollama: " . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($codigoHttp !== 200) {
    echo json_encode([
        "error" => "Ollama ha respondido con código HTTP $codigoHttp.",
        "debug" => $respuestaOllama
    ]);
    exit;
}

$respuestaDecodificada = json_decode($respuestaOllama, true);

if (!is_array($respuestaDecodificada)) {
    echo json_encode([
        "error" => "La respuesta de Ollama no es un JSON válido.",
        "debug" => $respuestaOllama
    ]);
    exit;
}

if (!isset($respuestaDecodificada["response"])) {
    echo json_encode([
        "error" => "Ollama no ha devuelto el campo response.",
        "debug" => $respuestaDecodificada
    ]);
    exit;
}

$respuestaLimpia = limpiarRespuestaModelo($respuestaDecodificada["response"]);

echo json_encode([
    "respuesta" => $respuestaLimpia
]);