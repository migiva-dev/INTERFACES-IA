<?php

header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set("Europe/Madrid");

/*
    Proyecto 5 - Entrenamiento de IA con JSON

    Lógica:
    1. Recibe una pregunta del usuario.
    2. Busca en conocimiento.json si hay una respuesta parecida
       usando similitud de palabras clave.
    3. Si la encuentra (similitud suficiente), responde desde el JSON.
    4. Si no, envía la pregunta a Ollama y devuelve su respuesta.
*/

$modeloOllama  = "llama3.2:1b";
$umbralSimilitud = 0.3; // Mínimo de palabras coincidentes para considerar match

/* --- Leer entrada --- */

$entradaRaw = file_get_contents("php://input");
$entrada    = json_decode($entradaRaw, true);

if ($entrada === null || !isset($entrada["pregunta"]) || trim($entrada["pregunta"]) === "") {
    echo json_encode(["error" => "No se ha recibido ninguna pregunta válida."]);
    exit;
}

$preguntaUsuario = trim($entrada["pregunta"]);

/* --- Cargar conocimiento.json --- */

$rutaJSON = __DIR__ . "/conocimiento.json";

if (!file_exists($rutaJSON)) {
    echo json_encode(["error" => "No se ha encontrado el archivo conocimiento.json."]);
    exit;
}

$jsonRaw      = file_get_contents($rutaJSON);
$conocimiento = json_decode($jsonRaw, true);

if (!is_array($conocimiento)) {
    echo json_encode(["error" => "El archivo conocimiento.json no tiene un formato válido."]);
    exit;
}

/* --- Función de similitud por palabras clave --- */

function calcularSimilitud($texto1, $texto2) {
    $stopwords = ["el", "la", "los", "las", "un", "una", "de", "del", "en", "que",
                  "es", "se", "por", "con", "para", "una", "son", "como", "qué",
                  "cuál", "cuáles", "cómo", "dónde", "quién", "cuándo", "hay",
                  "tiene", "hace", "puedo", "puedes", "me", "te", "le", "nos",
                  "a", "y", "o", "e", "u", "al", "lo", "su", "sus", "mi", "más"];

    $normalizar = function($texto) use ($stopwords) {
        $texto = mb_strtolower($texto, "UTF-8");
        $texto = preg_replace('/[¿?¡!.,;:()\[\]"\'\/\\\\]/', ' ', $texto);
        $palabras = preg_split('/\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY);
        return array_filter($palabras, function($p) use ($stopwords) {
            return strlen($p) > 2 && !in_array($p, $stopwords);
        });
    };

    $palabras1 = array_values($normalizar($texto1));
    $palabras2 = array_values($normalizar($texto2));

    if (empty($palabras1) || empty($palabras2)) return 0;

    $coincidencias = 0;
    foreach ($palabras1 as $p1) {
        foreach ($palabras2 as $p2) {
            if ($p1 === $p2 || similar_text($p1, $p2) / max(strlen($p1), strlen($p2)) > 0.85) {
                $coincidencias++;
                break;
            }
        }
    }

    return $coincidencias / max(count($palabras1), count($palabras2));
}

/* --- Buscar en el JSON --- */

$mejorPuntuacion = 0;
$mejorRespuesta  = null;

foreach ($conocimiento as $entrada) {
    if (!isset($entrada["pregunta"]) || !isset($entrada["respuesta"])) continue;

    $similitud = calcularSimilitud($preguntaUsuario, $entrada["pregunta"]);

    if ($similitud > $mejorPuntuacion) {
        $mejorPuntuacion = $similitud;
        $mejorRespuesta  = $entrada["respuesta"];
    }
}

/* --- Si hay match suficiente, responder desde JSON --- */

if ($mejorPuntuacion >= $umbralSimilitud && $mejorRespuesta !== null) {
    echo json_encode([
        "respuesta" => $mejorRespuesta,
        "fuente"    => "json",
        "similitud" => round($mejorPuntuacion, 2)
    ]);
    exit;
}

/* --- Si no hay match, preguntar a Ollama --- */

$prompt = "Responde en español de forma clara y concisa a la siguiente pregunta:\n\n$preguntaUsuario";

$datos = [
    "model"   => $modeloOllama,
    "prompt"  => $prompt,
    "stream"  => false,
    "options" => [
        "num_predict" => 400,
        "temperature" => 0.4,
        "top_p"       => 0.85
    ]
];

$ch = curl_init("http://localhost:11434/api/generate");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$respuestaOllama = curl_exec($ch);

if ($respuestaOllama === false) {
    echo json_encode(["error" => "No se pudo conectar con Ollama: " . curl_error($ch)]);
    curl_close($ch);
    exit;
}

$codigoHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($codigoHttp !== 200) {
    echo json_encode(["error" => "Ollama respondió con código HTTP $codigoHttp."]);
    exit;
}

$respuestaDecodificada = json_decode($respuestaOllama, true);

if (!isset($respuestaDecodificada["response"])) {
    echo json_encode(["error" => "Ollama no devolvió el campo response."]);
    exit;
}

$tokens = [
    "<|start_header_id|>", "<|end_header_id|>", "<|eot_id|>",
    "<|begin_of_text|>", "<|end_of_text|>"
];
$respuestaLimpia = trim(str_replace($tokens, "", $respuestaDecodificada["response"]));

echo json_encode([
    "respuesta" => $respuestaLimpia,
    "fuente"    => "ollama"
]);