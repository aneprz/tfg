
<?php

$client_id = "9ykmmxyti1eqox2urtbxoni602460q";
$client_secret = "s938ze47eehsz6e1qltwg11hxzs47o";

/* -------------------------
1️⃣ Obtener token de Twitch
--------------------------*/

$url = "https://id.twitch.tv/oauth2/token";

$data = [
    "client_id" => $client_id,
    "client_secret" => $client_secret,
    "grant_type" => "client_credentials"
];

$options = [
    "http" => [
        "header"  => "Content-type: application/x-www-form-urlencoded",
        "method"  => "POST",
        "content" => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

$result = json_decode($response, true);

$token = $result["access_token"];


/* -------------------------
2️⃣ Consultar IGDB
--------------------------*/

$query = "fields name,summary,cover.url; limit 10;";

$ch = curl_init("https://api.igdb.com/v4/games");

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Client-ID: $client_id",
    "Authorization: Bearer $token"
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

$games = json_decode($response, true);


/* -------------------------
3️⃣ Mostrar juegos
--------------------------*/

foreach ($games as $game) {

    echo "Juego: " . $game["name"] . "\n";

    if (isset($game["cover"]["url"])) {
        echo "Portada: https:" . $game["cover"]["url"] . "\n";
    }

    echo "\n";
}