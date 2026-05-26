<?php

header("Content-Type: application/json; charset=utf-8");

$entrada = json_decode(file_get_contents("php://input"), true);

if (!isset($entrada["texto"]) || trim($entrada["texto"]) === "") {
    echo json_encode([
        "error" => "No se ha recibido ningún texto."
    ]);
    exit;
}

$textoUsuario = trim($entrada["texto"]);

/*
    Ejecutamos Python y le pasamos el texto como argumento.
*/
$comando = "cd " . escapeshellarg(__DIR__)
    . " && python3 analizador.py "
    . escapeshellarg($textoUsuario);

$salidaPython = shell_exec($comando);

$datosPython = json_decode($salidaPython, true);

if (!is_array($datosPython)) {
    echo json_encode([
        "error" => "Python no ha devuelto un JSON válido.",
        "debug" => $salidaPython
    ]);
    exit;
}

echo json_encode($datosPython);