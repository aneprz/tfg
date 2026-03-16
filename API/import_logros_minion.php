<?php

require "../db/conexiones.php";
require "credenciales.php";

/* =========================
CONFIG
========================= */

$batch_size = 5; // Lotes pequeños para evitar bloqueos
$timeout = 10;   // Timeout por petición
$retries = 2;    // Reintentos si Steam no responde


/* =========================
PREPARED INSERT
========================= */

$stmt = $conexion->prepare("
INSERT IGNORE INTO Logros
(id_videojuego, nombre_logro, descripcion, puntos_logro, icono, icono_gris, porcentaje_global, steam_api_name)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");


/* =========================
CARGAR JUEGOS
========================= */

$games = [];

$result = mysqli_query($conexion,"
    SELECT id_videojuego, steam_appid, titulo
    FROM Videojuego
    WHERE steam_appid IS NOT NULL
");

while ($row = mysqli_fetch_assoc($result)) {
    $games[] = $row;
}

$total = count($games);
echo "Juegos a procesar: $total\n";


/* =========================
FUNCION PETICION CURL
========================= */

function curl_get_json($url, $timeout, $retries) {

    for ($i = 0; $i <= $retries; $i++) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $response = curl_exec($ch);
        $err = curl_errno($ch);

        // NO curl_close() — PHP 8.5 lo cierra solo

        if (!$err && $response) {
            return json_decode($response, true);
        }

        sleep(1); // Reintento
    }

    return null;
}


/* =========================
PROCESAR JUEGOS
========================= */

foreach ($games as $g) {

    $id_videojuego = $g["id_videojuego"];
    $appid = $g["steam_appid"];
    $titulo = $g["titulo"];

    echo "\n🎮 $titulo (AppID $appid)\n";

    /* =========================
    PETICIONES A STEAM
    ========================= */

    $schema_url = "https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key=$steam_api_key&appid=$appid";
    $stats_url  = "https://api.steampowered.com/ISteamUserStats/GetGlobalAchievementPercentagesForApp/v2/?gameid=$appid";

    $schema = curl_get_json($schema_url, $timeout, $retries);
    $stats_json = curl_get_json($stats_url, $timeout, $retries);

    if (!$schema || !isset($schema["game"]["availableGameStats"]["achievements"])) {
        echo "Sin logros o Steam no responde\n";
        continue;
    }

    $achievements = $schema["game"]["availableGameStats"]["achievements"];

    /* =========================
    MAPA PORCENTAJES
    ========================= */

    $stats = [];

    if ($stats_json && isset($stats_json["achievementpercentages"]["achievements"])) {
        foreach ($stats_json["achievementpercentages"]["achievements"] as $s) {
            $stats[$s["name"]] = $s["percent"];
        }
    }

    /* =========================
    INSERTAR LOGROS
    ========================= */

    $conexion->begin_transaction();

    foreach ($achievements as $a) {

        $nombre = $a["displayName"] ?? "";
        if (!$nombre) continue;

        $descripcion = $a["description"] ?? "";
        $icono = $a["icon"] ?? "";
        $icono_gris = $a["icongray"] ?? "";
        $steam_api_name = $a["name"];

        $porcentaje = $stats[$steam_api_name] ?? null;

        // Sistema de puntos
        if ($porcentaje === null || $porcentaje >= 75) $puntos = 1;
        elseif ($porcentaje >= 50) $puntos = 2;
        elseif ($porcentaje >= 25) $puntos = 3;
        elseif ($porcentaje >= 10) $puntos = 4;
        elseif ($porcentaje >= 5) $puntos = 6;
        else $puntos = 8;

        $stmt->bind_param(
            "ississds",
            $id_videojuego,
            $nombre,
            $descripcion,
            $puntos,
            $icono,
            $icono_gris,
            $porcentaje,
            $steam_api_name
        );

        $stmt->execute();
    }

    $conexion->commit();

    echo "✔ Logros importados: " . count($achievements) . "\n";
}

echo "\nIMPORTACION LOGROS FINALIZADA\n";
