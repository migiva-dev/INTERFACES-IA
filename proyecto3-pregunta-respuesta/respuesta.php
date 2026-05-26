<?php

header("Content-Type: application/json; charset=utf-8");

date_default_timezone_set("Europe/Madrid");

/*
    Proyecto 3 - Pregunta y respuesta con IA

    Esta aplicación recibe una pregunta de repaso,
    la envía a Ollama y devuelve una respuesta educativa.
*/

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

$instruccionesSistema = "
Eres un asistente educativo para alumnado de DAW2.

Tu tarea es responder preguntas de repaso de forma clara, profesional y útil.

Normas:
- Responde siempre en español.
- Contesta directamente a la pregunta del usuario.
- Usa un tono educativo, claro y profesional.
- No digas que eres una IA salvo que sea necesario.
- No menciones Ollama ni PHP salvo que la pregunta trate de eso.
- No inventes información si no estás seguro.
- Si la pregunta es ambigua, explica la interpretación más probable.
- Evita respuestas demasiado largas.
- No uses símbolos raros ni etiquetas internas.

Estructura de respuesta:
1. Empieza con una explicación directa.
2. Añade una explicación breve del concepto.
3. Incluye un ejemplo práctico si ayuda.
4. Termina con una conclusión corta.

Formato:
- Usa párrafos claros.
- Si usas listas, escribe cada punto en una sola línea completa.
- No separes el número, el título y la explicación en líneas diferentes.
";

$mensajes = [
    [
        "role" => "system",
        "content" => $instruccionesSistema
    ],
    [
        "role" => "user",
        "content" => $preguntaUsuario
    ]
];

$urlOllama = "http://localhost:11434/api/chat";

$datos = [
    "model" => $modeloOllama,
    "messages" => $mensajes,
    "stream" => false,
    "options" => [
        "num_predict" => 450,
        "temperature" => 0.2,
        "top_p" => 0.8
    ]
];

$ch = curl_init($urlOllama);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$respuestaOllama = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        "error" => "No se ha podido conectar con Ollama. Comprueba que Ollama está iniciado."
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$respuestaDecodificada = json_decode($respuestaOllama, true);

if (!isset($respuestaDecodificada["message"]["content"])) {
    echo json_encode([
        "error" => "Ollama no ha devuelto una respuesta válida.",
        "debug" => $respuestaDecodificada
    ]);
    exit;
}

$respuestaLimpia = limpiarRespuestaModelo($respuestaDecodificada["message"]["content"]);

echo json_encode([
    "respuesta" => $respuestaLimpia
]);