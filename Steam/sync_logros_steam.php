<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require "../db/conexiones.php";
require "../API/credenciales.php";

if(!isset($_SESSION["id_usuario"])){
    header("Location: ../login.php");
    exit;
}

$id_usuario = $_SESSION["id_usuario"];

/* =========================
   OBTENER STEAMID
   ========================= */

$res = mysqli_query($conexion,"
SELECT steamid
FROM Usuario
WHERE id_usuario='$id_usuario'
");

$row = mysqli_fetch_assoc($res);

if(!$row || !$row["steamid"]){
    $_SESSION["steam_sync"] = "No tienes una cuenta de Steam vinculada";
    header("Location: ../perfilSesion.php");
    exit;
}

$steamid = $row["steamid"];


/* =========================
   OBTENER JUEGOS STEAM
   ========================= */

$url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key=$steam_api_key&steamid=$steamid&include_appinfo=true&include_played_free_games=true";

$data = json_decode(file_get_contents($url), true);

if(!isset($data["response"]["games"])){
    $_SESSION["steam_sync"] = "No se pudieron obtener juegos de Steam";
    header("Location: ../perfilSesion.php");
    exit;
}

$games = $data["response"]["games"];


/* =========================
   RECORRER JUEGOS
   ========================= */

foreach($games as $game){

    $appid = $game["appid"];

    /* comprobar si ese juego está en tu BD */

    $res = mysqli_query($conexion,"
    SELECT id_videojuego
    FROM Videojuego
    WHERE steam_appid='$appid'
    ");

    if(!$row = mysqli_fetch_assoc($res)){
        continue;
    }

    $id_videojuego = $row["id_videojuego"];


    /* =========================
       GUARDAR EN BIBLIOTECA
       ========================= */

    $horas = 0;

    if(isset($game["playtime_forever"])){
        $horas = $game["playtime_forever"] / 60; // minutos → horas
    }

    $estado = "pendiente";

    if($horas > 0){
        $estado = "jugado";
    }

    mysqli_query($conexion,"
    INSERT IGNORE INTO Biblioteca
    (id_usuario,id_videojuego,estado,horas_totales)
    VALUES
    ('$id_usuario','$id_videojuego','$estado','$horas')
    ");


    /* =========================
       OBTENER LOGROS DEL JUEGO
       ========================= */

    $url = "https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1/?key=$steam_api_key&steamid=$steamid&appid=$appid";

    $data = @file_get_contents($url);

    if(!$data){
        continue;
    }

    $data = json_decode($data, true);

    if(!isset($data["playerstats"]["achievements"])){
        continue;
    }


    foreach($data["playerstats"]["achievements"] as $ach){

        if($ach["achieved"] != 1){
            continue;
        }

        $api_name = $ach["apiname"];


        /* buscar logro en tu tabla */

        $res = mysqli_query($conexion,"
        SELECT id_logro
        FROM Logros
        WHERE steam_api_name='$api_name'
        AND id_videojuego='$id_videojuego'
        ");

        if(!$row = mysqli_fetch_assoc($res)){
            continue;
        }

        $id_logro = $row["id_logro"];


        /* guardar logro */

        $fecha = null;

        if(isset($ach["unlocktime"]) && $ach["unlocktime"] > 0){
            $fecha = date("Y-m-d H:i:s", $ach["unlocktime"]);
        }

        mysqli_query($conexion,"
        INSERT IGNORE INTO Logros_Usuario
        (id_usuario,id_logro,fecha_obtencion)
        VALUES
        ('$id_usuario','$id_logro','$fecha')
        ");
    }
}


/* =========================
   FINALIZAR
   ========================= */

$_SESSION["steam_sync"] = "Biblioteca y logros sincronizados correctamente";

header("Location: ../php/user/perfiles/perfilSesion.php");
exit;