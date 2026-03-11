<?php

require "../db/conexiones.php";
require "credenciales.php";

/* =========================
   PREPARAR INSERT
   ========================= */

$stmt = $conexion->prepare("
INSERT IGNORE INTO Logros
(id_videojuego,nombre_logro,descripcion,puntos_logro,icono,icono_gris,porcentaje_global,steam_api_name)
VALUES (?,?,?,?,?,?,?,?)
");


/* =========================
   OBTENER JUEGOS CON APPID
   ========================= */

$result = mysqli_query($conexion,"
SELECT id_videojuego, steam_appid, titulo
FROM Videojuego
WHERE steam_appid IS NOT NULL
");

while($row = mysqli_fetch_assoc($result)){

    $id_videojuego = $row["id_videojuego"];
    $appid = $row["steam_appid"];
    $titulo = $row["titulo"];

    echo "\n🎮 $titulo ($appid)\n";

    /* =========================
       SCHEMA LOGROS
       ========================= */

    $url = "https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key=$steam_api_key&appid=$appid";

    $json = @file_get_contents($url);

    if(!$json){
        echo "Error API\n";
        continue;
    }

    $data = json_decode($json,true);

    if(!isset($data["game"]["availableGameStats"]["achievements"])){

        echo "Sin logros\n";
        continue;
    }

    $achievements = $data["game"]["availableGameStats"]["achievements"];


    /* =========================
       PORCENTAJE GLOBAL
       ========================= */

    $stats_url = "https://api.steampowered.com/ISteamUserStats/GetGlobalAchievementPercentagesForApp/v2/?gameid=$appid";

    $stats_json = @file_get_contents($stats_url);
    $stats_data = json_decode($stats_json,true);

    $stats = [];

    if(isset($stats_data["achievementpercentages"]["achievements"])){

        foreach($stats_data["achievementpercentages"]["achievements"] as $s){

            $stats[$s["name"]] = $s["percent"];
        }
    }

    /* =========================
       TRANSACCION (MUCHO MAS RAPIDO)
       ========================= */

    $conexion->begin_transaction();

    foreach($achievements as $a){

        $nombre = $a["displayName"] ?? "";
        $descripcion = $a["description"] ?? "";
        $icono = $a["icon"] ?? "";
        $icono_gris = $a["icongray"] ?? "";
        $steam_api_name = $a["name"];

        $porcentaje = $stats[$steam_api_name] ?? null;

        if(!$nombre) continue;


        /* =========================
           CALCULAR PUNTOS
           ========================= */

        if($porcentaje === null){
            $puntos = 1;
        }
        elseif($porcentaje >= 75){
            $puntos = 1;
        }
        elseif($porcentaje >= 50){
            $puntos = 2;
        }
        elseif($porcentaje >= 25){
            $puntos = 3;
        }
        elseif($porcentaje >= 10){
            $puntos = 4;
        }
        elseif($porcentaje >= 5){
            $puntos = 6;
        }
        else{
            $puntos = 8;
        }


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

        echo "🏆 $nombre ($porcentaje%) → $puntos puntos\n";
    }

    $conexion->commit();
}

echo "\nIMPORTACION DE LOGROS FINALIZADA\n";