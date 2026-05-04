<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

session_start();

require "../db/conexiones.php";
require "../API/credenciales.php";
require_once __DIR__ . '/../php/user/perfiles/UserProgressService.php';

if (!isset($_SESSION["id_usuario"])) {
    exit(header("Location: ../login.php"));
}

$id_usuario = (int) $_SESSION["id_usuario"];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


/* =========================
   RECONNECT SI MUERE MYSQL
   ========================= */
function db_ping($conexion){
    if (!$conexion->ping()) {
        global $conexion;
        $conexion->close();
        require "../db/conexiones.php";
    }
}


/* =========================
   STEAM API
   ========================= */
function steam_api($url){
    $ch = curl_init($url);
    curl_setopt_array($ch,[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_TIMEOUT=>15
    ]);

    $res = curl_exec($ch);
    curl_close($ch);

    return json_decode($res,true);
}


/* =========================
   STEAMID
   ========================= */
$stmt = $conexion->prepare("SELECT steamid FROM Usuario WHERE id_usuario=?");
$stmt->bind_param("i",$id_usuario);
$stmt->execute();
$steamid = $stmt->get_result()->fetch_assoc()["steamid"] ?? null;

if(!$steamid){
    exit(header("Location: ../perfilSesion.php"));
}


/* =========================
   MAPAS (OPTIMIZADO)
   ========================= */
$juegos_bd = [];
$res = $conexion->query("SELECT id_videojuego, steam_appid FROM Videojuego");
while($r = $res->fetch_assoc()){
    $juegos_bd[(int)$r["steam_appid"]] = (int)$r["id_videojuego"];
}

$logros_bd = [];
$res = $conexion->query("SELECT id_logro, steam_api_name, id_videojuego, puntos_logro FROM Logros");

while($r = $res->fetch_assoc()){
    $logros_bd[$r["id_videojuego"]][$r["steam_api_name"]] = $r;
}


/* =========================
   STEAM GAMES
   ========================= */
$data = steam_api("https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key=$steam_api_key&steamid=$steamid&include_appinfo=1");

if(!$data || empty($data["response"]["games"])) exit;

$games = $data["response"]["games"];


/* =========================
   PREPARE STATEMENTS (fuera loop)
   ========================= */
$insBib = $conexion->prepare("
INSERT IGNORE INTO Biblioteca
(id_usuario,id_videojuego,estado,horas_totales)
VALUES (?,?,?,?)
");

$insLog = $conexion->prepare("
INSERT IGNORE INTO Logros_Usuario
(id_usuario,id_logro,fecha_obtencion)
VALUES (?,?,?)
");

$total = 0;
$counter = 0;
$urls = [];


/* =========================
   JUEGOS
   ========================= */
foreach($games as $g){

    $appid = (int)$g["appid"];

    if(!isset($juegos_bd[$appid])) continue;

    $id_videojuego = $juegos_bd[$appid];
    $horas = ($g["playtime_forever"] ?? 0)/60;
    $estado = $horas>0 ? "jugado":"pendiente";

    $insBib->bind_param("iisd",$id_usuario,$id_videojuego,$estado,$horas);
    $insBib->execute();

    if(isset($logros_bd[$id_videojuego])){
        $urls[$appid] = "https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1/?key=$steam_api_key&steamid=$steamid&appid=$appid";
    }

    // 👇 IMPORTANTE: evita overflow conexión
    if(++$counter % 50 == 0){
        db_ping($conexion);
        usleep(200000);
    }
}


/* =========================
   LOGROS
   ========================= */
foreach($urls as $appid=>$url){

    $data = steam_api($url);
    if(!$data || empty($data["playerstats"]["achievements"])) continue;

    $id_videojuego = $juegos_bd[$appid];

    foreach($data["playerstats"]["achievements"] as $a){

        if($a["achieved"] != 1) continue;

        $api = $a["apiname"];

        if(!isset($logros_bd[$id_videojuego][$api])) continue;

        $log = $logros_bd[$id_videojuego][$api];

        $fecha = !empty($a["unlocktime"])
            ? date("Y-m-d H:i:s",$a["unlocktime"])
            : null;

        $insLog->bind_param("iis",$id_usuario,$log["id_logro"],$fecha);
        $insLog->execute();

        if($insLog->affected_rows){
            $total += (int)$log["puntos_logro"];
        }
    }

    db_ping($conexion);
}


/* =========================
   UPDATE FINAL
   ========================= */
if($total>0){

    $tipo = "logro";
    $desc = "Logro desbloqueado";

    UserProgressService::applyPointDelta($conexion, $id_usuario, $total);
    UserProgressService::registerPointMovement($conexion, $id_usuario, $total, $tipo, $desc);
}


$_SESSION["steam_sync"]="OK";

header("Location: ../php/user/perfiles/perfilSesion.php");
exit;
?>
