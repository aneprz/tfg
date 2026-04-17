<?php

ini_set('display_errors', '1');
error_reporting(E_ALL);

$credPath = __DIR__ . '/API/credenciales.php';
if (!file_exists($credPath)) {
    fwrite(STDERR, "Falta API/credenciales.php\n");
    exit(1);
}
require $credPath;

if (empty($steam_api_key)) {
    fwrite(STDERR, "Falta \$steam_api_key en API/credenciales.php\n");
    exit(1);
}

$steamid = $argv[1] ?? getenv('STEAMID');
if (!$steamid) {
    fwrite(STDERR, "Uso: php test_steam.php <steamid64>\n");
    fwrite(STDERR, "O define la env var STEAMID.\n");
    exit(1);
}
if (!preg_match('/^[0-9]{16,20}$/', $steamid)) {
    fwrite(STDERR, "SteamID inválido. Debe ser el steamid64 (solo números, normalmente 17 dígitos).\n");
    exit(1);
}

$url = 'https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=' .
    rawurlencode($steam_api_key) . '&steamids=' . rawurlencode($steamid);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_USERAGENT, 'tfg-steam-test/1.0');

$response = curl_exec($ch);
if ($response === false) {
    fwrite(STDERR, "cURL error: " . curl_error($ch) . "\n");
    curl_close($ch);
    exit(1);
}
$httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

$data = json_decode($response, true);
if (!is_array($data)) {
    fwrite(STDERR, "Respuesta no JSON.\n");
    exit(1);
}

$players = $data['response']['players'] ?? [];
if (!$players) {
    fwrite(STDERR, "Steam no devolvió jugadores. Revisa la key y el steamid.\n");
    fwrite(STDERR, "HTTP: {$httpCode}\n");
    fwrite(STDERR, "Respuesta: {$response}\n");
    exit(1);
}

$p = $players[0];
echo "OK\n";
echo "steamid: " . ($p['steamid'] ?? 'n/a') . "\n";
echo "nombre: " . ($p['personaname'] ?? 'n/a') . "\n";
echo "perfil: " . ($p['profileurl'] ?? 'n/a') . "\n";
