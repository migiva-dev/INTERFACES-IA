<?php

header("Content-Type: application/json; charset=utf-8");

date_default_timezone_set("Europe/Madrid");

/*
    Proyecto 2 - MicroChatGPT con PHP

    Este archivo recibe el mensaje del usuario desde JavaScript,
    consulta primero una base de conocimiento local en JSON
    y, si no encuentra una respuesta, llama a Ollama.
*/

// Modelo instalado en Ollama
$modeloOllama = "llama3.2:1b";

// Fecha y hora actuales
$fechaActual = date("d/m/Y");
$horaActual = date("H:i");
$diaSemanaIngles = date("l");

$dias = [
    "Monday" => "lunes",
    "Tuesday" => "martes",
    "Wednesday" => "miércoles",
    "Thursday" => "jueves",
    "Friday" => "viernes",
    "Saturday" => "sábado",
    "Sunday" => "domingo"
];

$diaActual = $dias[$diaSemanaIngles] ?? "día desconocido";

// Recibimos los datos enviados desde JavaScript
$entrada = json_decode(file_get_contents("php://input"), true);

if (!isset($entrada["mensaje"]) || trim($entrada["mensaje"]) === "") {
    echo json_encode([
        "error" => "No se ha recibido ningún mensaje."
    ]);
    exit;
}

$mensajeUsuario = trim($entrada["mensaje"]);
$historial = $entrada["historial"] ?? [];

/*
    Función para normalizar textos.
    Sirve para comparar preguntas aunque tengan mayúsculas,
    minúsculas, tildes, espacios de más o signos básicos.
*/
function normalizarTexto($texto) {
    $texto = mb_strtolower($texto, "UTF-8");
    $texto = trim($texto);

    $buscar = ["á", "é", "í", "ó", "ú", "ü", "ñ"];
    $reemplazar = ["a", "e", "i", "o", "u", "u", "n"];
    $texto = str_replace($buscar, $reemplazar, $texto);

    $texto = preg_replace('/[¿?¡!.,;:]/u', '', $texto);
    $texto = preg_replace('/\s+/', ' ', $texto);

    return $texto;
}

/*
    Función para buscar una respuesta dentro de conocimiento.json.
    Si encuentra una pregunta igual o muy parecida, devuelve la respuesta guardada.
*/
function buscarEnConocimiento($mensajeUsuario) {
    $rutaArchivo = __DIR__ . "/conocimiento.json";

    if (!file_exists($rutaArchivo)) {
        return null;
    }

    $contenido = file_get_contents($rutaArchivo);
    $datos = json_decode($contenido, true);

    if (!is_array($datos)) {
        return null;
    }

    $preguntaUsuario = normalizarTexto($mensajeUsuario);

    foreach ($datos as $item) {
        if (!isset($item["pregunta"]) || !isset($item["respuesta"])) {
            continue;
        }

        $preguntaJson = normalizarTexto($item["pregunta"]);

        // Coincidencia exacta
        if ($preguntaUsuario === $preguntaJson) {
            return $item["respuesta"];
        }

        // Coincidencia aproximada
        similar_text($preguntaUsuario, $preguntaJson, $porcentaje);

        if ($porcentaje >= 82) {
            return $item["respuesta"];
        }
    }

    return null;
}

/*
    Función para limpiar posibles tokens raros devueltos por algunos modelos.
*/
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

// Primero buscamos en conocimiento.json
$respuestaConocimiento = buscarEnConocimiento($mensajeUsuario);

if ($respuestaConocimiento !== null) {
    echo json_encode([
        "respuesta" => $respuestaConocimiento,
        "origen" => "conocimiento.json"
    ]);
    exit;
}

/*
    Si no existe una respuesta en conocimiento.json,
    entonces llamamos a Ollama.
*/
$instruccionesSistema = "
Eres un asistente de inteligencia artificial profesional integrado en una aplicación web local.

Tu objetivo es responder de forma clara, precisa y útil, como si fueras un profesor o técnico explicando el tema a un alumno de DAW2.

Normas generales:
- Responde siempre en español.
- Mantén un tono profesional, claro y educativo.
- No respondas de forma infantil ni demasiado informal.
- No cambies de tema.
- No inventes información si no estás seguro.
- Si falta información, indícalo de forma clara.
- No menciones que eres un modelo de IA salvo que sea necesario.
- No hables de Ollama, PHP, MicroChatGPT o del proyecto salvo que el usuario pregunte sobre ello.
- Si el usuario hace una pregunta general, responde directamente a esa pregunta y no expliques qué es esta aplicación.
- Evita respuestas excesivamente largas si no son necesarias.
- No incluyas etiquetas internas ni tokens especiales.
- No empieces siempre igual.
- No repitas literalmente la pregunta del usuario.

Estructura recomendada:
- Empieza con una respuesta directa.
- Después explica el motivo o el concepto.
- Si procede, añade un ejemplo práctico.
- Termina con una conclusión breve.

Formato:
- Usa párrafos claros.
- Usa listas solo cuando ayuden a entender mejor.
- Si explicas código, hazlo paso a paso.
- Si el usuario pide algo técnico, da una solución aplicable.
- Si el usuario pide una explicación breve, responde de forma resumida.
- Si el usuario pide más detalle, amplía la explicación.

Datos actuales:
- Fecha actual: $fechaActual
- Día actual: $diaActual
- Hora aproximada en España: $horaActual
";

$mensajes = [];

$mensajes[] = [
    "role" => "system",
    "content" => $instruccionesSistema
];

// Añadimos el historial de conversación
if (is_array($historial)) {
    foreach ($historial as $mensaje) {
        if (
            isset($mensaje["role"]) &&
            isset($mensaje["content"]) &&
            in_array($mensaje["role"], ["user", "assistant"])
        ) {
            $mensajes[] = [
                "role" => $mensaje["role"],
                "content" => $mensaje["content"]
            ];
        }
    }
}

// Por seguridad, si el último mensaje no se ha añadido al historial, lo añadimos aquí
$ultimoMensaje = end($mensajes);

if (
    !isset($ultimoMensaje["role"]) ||
    $ultimoMensaje["role"] !== "user" ||
    $ultimoMensaje["content"] !== $mensajeUsuario
) {
    $mensajes[] = [
        "role" => "user",
        "content" => $mensajeUsuario
    ];
}

// URL local de Ollama
$urlOllama = "http://localhost:11434/api/chat";

// Datos que se envían a Ollama
$datos = [
    "model" => $modeloOllama,
    "messages" => $mensajes,
    "stream" => false,
    "options" => [
        "num_predict" => 500,
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
    "respuesta" => $respuestaLimpia,
    "origen" => "ollama"
]);