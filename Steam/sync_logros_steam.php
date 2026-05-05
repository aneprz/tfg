<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

session_start();

require "../db/conexiones.php";
require "../API/credenciales.php";
require_once __DIR__ . '/../php/user/perfiles/UserProgressService.php';
require_once __DIR__ . '/SteamSyncProgress.php';

$mode = $_GET['mode'] ?? 'redirect';
$token = (string) ($_GET['token'] ?? '');

function update_sync_progress(string $token, int $userId, int $progress, string $message): void
{
    if ($token === '' || $userId <= 0) {
        return;
    }

    SteamSyncProgress::advance($token, $userId, $progress, $message);
}

function finish_sync(string $status, string $message, string $mode, string $token = '', int $userId = 0, int $progress = 100): void
{
    if ($token !== '' && $userId > 0) {
        SteamSyncProgress::finish($token, $userId, $status, $progress, $message);
    }

    if ($mode === 'iframe') {
        header('Content-Type: text/html; charset=UTF-8');

        $payload = json_encode([
            'type' => 'steam-sync-result',
            'status' => $status,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Steam Sync</title></head><body>';
        echo '<script>window.parent.postMessage(' . $payload . ', window.location.origin);</script>';
        echo '</body></html>';
        exit;
    }

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION["steam_sync"] = $message;
    session_write_close();
    header("Location: ../php/user/perfiles/perfilSesion.php");
    exit;
}

if (!isset($_SESSION["id_usuario"])) {
    finish_sync('error', 'Necesitas iniciar sesion para sincronizar Steam.', $mode, $token);
}

$id_usuario = (int) $_SESSION["id_usuario"];
session_write_close();
ignore_user_abort(true);

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
    finish_sync('error', 'Primero tienes que vincular tu cuenta de Steam.', $mode, $token, $id_usuario, 0);
}

if ($token !== '') {
    SteamSyncProgress::start($token, $id_usuario, 'Conectando con Steam...');
}

try {
    update_sync_progress($token, $id_usuario, 10, 'Cargando tu catalogo interno de juegos y logros.');

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

    update_sync_progress($token, $id_usuario, 22, 'Consultando tu biblioteca en Steam.');

    /* =========================
       STEAM GAMES
       ========================= */
    $data = steam_api("https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key=$steam_api_key&steamid=$steamid&include_appinfo=1");

    if(!$data){
        finish_sync('error', 'No se pudo conectar con Steam para leer tu biblioteca.', $mode, $token, $id_usuario, 100);
    }

    if(empty($data["response"]["games"])){
        finish_sync('success', 'Sincronizacion completada. No se encontraron juegos para importar.', $mode, $token, $id_usuario, 100);
    }

    $games = $data["response"]["games"];
    $totalGames = max(1, count($games));

    update_sync_progress($token, $id_usuario, 28, 'Actualizando tu biblioteca con los juegos encontrados.');


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
        $counter++;
        $libraryProgress = 28 + (int) floor(($counter / $totalGames) * 24);
        update_sync_progress($token, $id_usuario, min(52, $libraryProgress), 'Sincronizando tu biblioteca de Steam.');

        if(!isset($juegos_bd[$appid])) continue;

        $id_videojuego = $juegos_bd[$appid];
        $horas = ($g["playtime_forever"] ?? 0)/60;
        $estado = $horas>0 ? "jugado":"pendiente";

        $insBib->bind_param("iisd",$id_usuario,$id_videojuego,$estado,$horas);
        $insBib->execute();

        if(isset($logros_bd[$id_videojuego])){
            $urls[$appid] = "https://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v1/?key=$steam_api_key&steamid=$steamid&appid=$appid";
        }

        if($counter % 50 == 0){
            db_ping($conexion);
            usleep(200000);
        }
    }

    update_sync_progress($token, $id_usuario, 55, 'Biblioteca lista. Revisando logros desbloqueados.');


    /* =========================
       LOGROS
       ========================= */
    $totalAchievementRequests = max(1, count($urls));
    $processedAchievementRequests = 0;

    foreach($urls as $appid=>$url){

        $data = steam_api($url);
        if(!$data || empty($data["playerstats"]["achievements"])){
            $processedAchievementRequests++;
            $achievementProgress = 55 + (int) floor(($processedAchievementRequests / $totalAchievementRequests) * 35);
            update_sync_progress($token, $id_usuario, min(90, $achievementProgress), 'Comparando tus logros de Steam con SalsaBox.');
            continue;
        }

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
        $processedAchievementRequests++;
        $achievementProgress = 55 + (int) floor(($processedAchievementRequests / $totalAchievementRequests) * 35);
        update_sync_progress($token, $id_usuario, min(90, $achievementProgress), 'Comparando tus logros de Steam con SalsaBox.');
    }


    /* =========================
       UPDATE FINAL
       ========================= */
    update_sync_progress($token, $id_usuario, 94, 'Guardando los resultados finales de la sincronizacion.');

    if($total>0){

        $tipo = "logro";
        $desc = "Logro desbloqueado";

        UserProgressService::applyPointDelta($conexion, $id_usuario, $total);
        UserProgressService::registerPointMovement($conexion, $id_usuario, $total, $tipo, $desc);
        finish_sync('success', 'Sincronizacion completada. Se han añadido ' . $total . ' puntos nuevos.', $mode, $token, $id_usuario, 100);
    }

    finish_sync('success', 'Sincronizacion completada. No habia logros nuevos para importar.', $mode, $token, $id_usuario, 100);
} catch (Throwable $e) {
    finish_sync('error', 'La sincronizacion de Steam ha fallado. Intentalo de nuevo en unos segundos.', $mode, $token, $id_usuario, 100);
}
?>
