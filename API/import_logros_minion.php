<?php

require "../db/conexiones.php";
require "credenciales.php";

// 1. Evitamos que el script muera por tiempo o memoria
set_time_limit(0); 
ini_set('memory_limit', '512M');

/* =========================
PREPARED INSERT
========================= */
$stmt = $conexion->prepare("
INSERT IGNORE INTO Logros
(id_videojuego, nombre_logro, descripcion, puntos_logro, icono, icono_gris, porcentaje_global, steam_api_name)
VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

/* =========================
FUNCION PETICION CURL (CON PARCHE SSL)
========================= */
function curl_get_json($url, $timeout, $retries) {
    for ($i = 0; $i <= $retries; $i++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        
        // PARCHE CRÍTICO PARA WINDOWS
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $err = curl_errno($ch);

        if (!$err && $response) {
            return json_decode($response, true);
        }
        sleep(1); 
    }
    return null;
}

/* =========================
CARGAR JUEGOS (SIN ARRAY GIGANTE)
========================= */
$result = mysqli_query($conexion,"
    SELECT id_videojuego, steam_appid, titulo
    FROM Videojuego
    WHERE steam_appid IS NOT NULL
");

echo "Iniciando procesamiento...\n";

// 2. Procesamos fila a fila directamente de la base de datos
while ($g = mysqli_fetch_assoc($result)) {

    $id_videojuego = $g["id_videojuego"];
    $appid = $g["steam_appid"];
    $titulo = $g["titulo"];

    echo "\n🎮 $titulo (AppID $appid)... ";

    $schema_url = "https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key=$steam_api_key&appid=$appid";
    $stats_url  = "https://api.steampowered.com/ISteamUserStats/GetGlobalAchievementPercentagesForApp/v2/?gameid=$appid";

    $schema = curl_get_json($schema_url, 10, 2);
    $stats_json = curl_get_json($stats_url, 10, 2);

    if (!$schema || !isset($schema["game"]["availableGameStats"]["achievements"])) {
        echo "❌ Sin logros o error de conexión.";
        continue;
    }

    $achievements = $schema["game"]["availableGameStats"]["achievements"];
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
    $count = 0;

    foreach ($achievements as $a) {
        $nombre = $a["displayName"] ?? "";
        if (!$nombre) continue;

        $steam_api_name = $a["name"];
        $porcentaje = $stats[$steam_api_name] ?? null;

        // Sistema de puntos
        if ($porcentaje === null || $porcentaje >= 75) $puntos = 1;
        elseif ($porcentaje >= 50) $puntos = 2;
        elseif ($porcentaje >= 25) $puntos = 3;
        elseif ($porcentaje >= 10) $puntos = 4;
        elseif ($porcentaje >= 5) $puntos = 6;
        else $puntos = 8;

        $stmt->bind_param("ississds",
            $id_videojuego, $nombre, $a["description"], $puntos,
            $a["icon"], $a["icongray"], $porcentaje, $steam_api_name
        );

        if($stmt->execute()) $count++;
    }

    $conexion->commit();
    echo "✅ $count logros guardados.";
}

echo "\n\n✨ IMPORTACION LOGROS FINALIZADA ✨\n";