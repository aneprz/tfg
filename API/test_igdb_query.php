<?php

require "credenciales.php";

// Obtener token
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
$token = json_decode($response, true)["access_token"];

// Query mínima
$query = "fields name; limit 5;";

$ch = curl_init("https://api.igdb.com/v4/games");

curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "Client-ID: $client_id",
        "Authorization: Bearer $token",
        "Accept: application/json",
        "User-Agent: Salsabox/1.0"
    ],
    CURLOPT_POSTFIELDS => $query,
    CURLOPT_RETURNTRANSFER => true
]);

$result = curl_exec($ch);

echo "Respuesta IGDB:\n";
var_dump($result);
