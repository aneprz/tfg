<?php

require "credenciales.php";

$url = "https://id.twitch.tv/oauth2/token";

$data = [
    "client_id" => $client_id,
    "client_secret" => $client_secret,
    "grant_type" => "client_credentials"
];

$options = [
    "http" => [
        "header" => "Content-Type: application/x-www-form-urlencoded",
        "method" => "POST",
        "content" => http_build_query($data)
    ]
];

$response = file_get_contents($url, false, stream_context_create($options));

if ($response === false) {
    echo "❌ ERROR: No se pudo obtener respuesta del servidor de Twitch.\n";
    exit;
}

echo "Respuesta del token:\n";
var_dump($response);

$tokenData = json_decode($response, true);

if (!isset($tokenData["access_token"])) {
    echo "❌ ERROR: No se pudo obtener el token. Respuesta:\n";
    var_dump($tokenData);
    exit;
}

echo "Token obtenido correctamente:\n";
echo $tokenData["access_token"] . "\n";
