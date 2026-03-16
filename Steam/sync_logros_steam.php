<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
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
   FUNCION API NORMAL
   ========================= */

function steam_api($url){

    $ch = curl_init();

    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,10);

    $response = curl_exec($ch);

    curl_close($ch);

    return json_decode($response,true);
}


/* =========================
   FUNCION MULTI CURL
   ========================= */

function steam_multi_api($urls){

    $multi = curl_multi_init();
    $channels = [];

    foreach($urls as $key => $url){

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_TIMEOUT,10);

        curl_multi_add_handle($multi,$ch);

        $channels[$key] = $ch;
    }

    $running = null;

    do{
        curl_multi_exec($multi,$running);
        curl_multi_select($multi);
    }while($running > 0);

    $responses = [];

    foreach($channels as $key => $ch){

        $responses[$key] = json_decode(curl_multi_getcontent($ch),true);

        curl_multi_remove_handle($multi,$ch);
        curl_close($ch);
    }

    curl_multi_close($multi);

    return $responses;
}


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
   CARGAR JUEGOS DE TU BD
   ========================= */

$juegos_bd = [];

$res = mysqli_query($conexion,"
SELECT id_videojuego, steam_appid
FROM Videojuego
");

while($row = mysqli_fetch_assoc($res)){
    $juegos_bd[$row["steam_appid"]] = $row["id_videojuego"];
}


/* =========================
   CARGAR LOGROS DE TU BD
   ========================= */

$logros_bd = [];

$res = mysqli_query($conexion,"
SELECT id_logro, steam_api_name, id_videojuego
FROM Logros
");

while($row = mysqli_fetch_assoc($res)){
    $logros_bd[$row["id_videojuego"]][$row["steam_api_name"]] = $row["id_logro"];
}


/* =========================
   OBTENER JUEGOS STEAM
   ========================= */

$url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key=$steam_api_key&steamid=$steamid&include_appinfo=true&include_played_free_games=true";

$data = steam_api($url);

if(!isset($data["response"]["games"])){

    $_SESSION["steam_sync"] = "No se pudieron obtener juegos de Steam";

    header("Location: ../perfilSesion.php");
    exit;
}

$games = $data["response"]["games"];


/* =========================
   PREPARAR URLS LOGROS
   ========================= */

$achievement_urls = [];

foreach($games as $game){

    $appid = $game["appid"];

    if(!isset($juegos_bd[$appid])){
        continue;
    }

    $id_videojuego = $juegos_bd[$appid];

    $horas = 0;

    if(isset($game["playtime_forever"])){
        $horas = $game["playtime_forever"] / 60;
    }

    $estado = ($horas > 0) ? "jugado" : "pendiente";

    mysqli_query($conexion,"
    INSERT IGNORE INTO Biblioteca
    (id_usuario,id_videojuego,estado,horas_totales)
    VALUES
    ('$id_usuario','$id_videojuego','$estado','$horas')
    ");

    if(!isset($logros_bd[$id_videojuego])){
        continue;
    }

    $achievement_urls[$appid] =
    "https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1/?key=$steam_api_key&steamid=$steamid&appid=$appid";
}


/* =========================
   OBTENER LOGROS EN PARALELO
   ========================= */

$achievements_data = steam_multi_api($achievement_urls);


/* =========================
   PROCESAR LOGROS
   ========================= */

foreach($achievements_data as $appid => $data){

    $id_videojuego = $juegos_bd[$appid];

    if(!isset($data["playerstats"]["achievements"])){
        continue;
    }

    foreach($data["playerstats"]["achievements"] as $ach){

        if($ach["achieved"] != 1){
            continue;
        }

        $api_name = $ach["apiname"];

        if(!isset($logros_bd[$id_videojuego][$api_name])){
            continue;
        }

        $id_logro = $logros_bd[$id_videojuego][$api_name];

        $fecha = null;

        if(isset($ach["unlocktime"]) && $ach["unlocktime"] > 0){
            $fecha = date("Y-m-d H:i:s",$ach["unlocktime"]);
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