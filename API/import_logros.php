<?php

require "../db/conexiones.php";
require "credenciales.php";


/* =========================
PREPARED INSERT
========================= */

$stmt=$conexion->prepare("
INSERT IGNORE INTO Logros
(id_videojuego,nombre_logro,descripcion,puntos_logro,icono,icono_gris,porcentaje_global,steam_api_name)
VALUES (?,?,?,?,?,?,?,?)
");


/* =========================
CARGAR JUEGOS
========================= */

$games=[];

$result=mysqli_query($conexion,"
SELECT id_videojuego,steam_appid,titulo
FROM Videojuego
WHERE steam_appid IS NOT NULL
");

while($row=mysqli_fetch_assoc($result)){
$games[]=$row;
}

$total=count($games);

echo "Juegos a procesar: $total\n";


/* =========================
CONFIG
========================= */

$batch_size=20; // juegos simultáneos


/* =========================
PROCESAR LOTES
========================= */

for($i=0;$i<$total;$i+=$batch_size){

$batch=array_slice($games,$i,$batch_size);

$mh=curl_multi_init();
$handles=[];


/* =========================
CREAR PETICIONES
========================= */

foreach($batch as $g){

$appid=$g["steam_appid"];

$schema_url="https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key=$steam_api_key&appid=$appid";

$stats_url="https://api.steampowered.com/ISteamUserStats/GetGlobalAchievementPercentagesForApp/v2/?gameid=$appid";

$ch1=curl_init($schema_url);
$ch2=curl_init($stats_url);

curl_setopt($ch1,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch2,CURLOPT_RETURNTRANSFER,true);

curl_multi_add_handle($mh,$ch1);
curl_multi_add_handle($mh,$ch2);

$handles[]=[
"game"=>$g,
"schema"=>$ch1,
"stats"=>$ch2
];

}


/* =========================
EJECUTAR MULTI CURL
========================= */

$running=null;

do{
curl_multi_exec($mh,$running);
curl_multi_select($mh);
}while($running);


/* =========================
INSERTAR RESULTADOS
========================= */

$conexion->begin_transaction();

foreach($handles as $h){

$g=$h["game"];

$id_videojuego=$g["id_videojuego"];
$titulo=$g["titulo"];

echo "\n🎮 $titulo\n";

$schema=json_decode(curl_multi_getcontent($h["schema"]),true);
$stats_json=json_decode(curl_multi_getcontent($h["stats"]),true);

curl_multi_remove_handle($mh,$h["schema"]);
curl_multi_remove_handle($mh,$h["stats"]);


if(!isset($schema["game"]["availableGameStats"]["achievements"])){
echo "Sin logros\n";
continue;
}

$achievements=$schema["game"]["availableGameStats"]["achievements"];


/* =========================
MAPA PORCENTAJES
========================= */

$stats=[];

if(isset($stats_json["achievementpercentages"]["achievements"])){

foreach($stats_json["achievementpercentages"]["achievements"] as $s){

$stats[$s["name"]]=$s["percent"];

}

}


/* =========================
INSERTAR LOGROS
========================= */

foreach($achievements as $a){

$nombre=$a["displayName"]??"";
if(!$nombre) continue;

$descripcion=$a["description"]??"";
$icono=$a["icon"]??"";
$icono_gris=$a["icongray"]??"";
$steam_api_name=$a["name"];

$porcentaje=$stats[$steam_api_name]??null;


if($porcentaje===null||$porcentaje>=75) $puntos=1;
elseif($porcentaje>=50) $puntos=2;
elseif($porcentaje>=25) $puntos=3;
elseif($porcentaje>=10) $puntos=4;
elseif($porcentaje>=5) $puntos=6;
else $puntos=8;


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

}

$conexion->commit();

curl_multi_close($mh);

}


echo "\nIMPORTACION LOGROS FINALIZADA\n";