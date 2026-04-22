<?php

require "../db/conexiones.php";
require "credenciales.php";

/* =========================
TOKEN TWITCH
========================= */

$url = "https://id.twitch.tv/oauth2/token";

$data = [
    "client_id"=>$client_id,
    "client_secret"=>$client_secret,
    "grant_type"=>"client_credentials"
];

$options = [
"http"=>[
"header"=>"Content-Type: application/x-www-form-urlencoded",
"method"=>"POST",
"content"=>http_build_query($data)
]];

$response=file_get_contents($url,false,stream_context_create($options));
$token=json_decode($response,true)["access_token"];


/* =========================
PREPARED INSERT
========================= */

$stmt=$conexion->prepare("
INSERT IGNORE INTO Videojuego
(titulo,descripcion,fecha_lanzamiento,developer,rating_medio,portada,genero,plataforma,trailer_youtube_id)
VALUES (?,?,?,?,?,?,?,?,?)
");


/* =========================
CONFIG
========================= */

$limite=500;
$max_offset=10000;


/* =========================
MULTI CURL
========================= */

$mh=curl_multi_init();
$handles=[];

for($offset=0;$offset<$max_offset;$offset+=$limite){

$query="
fields
name,
summary,
first_release_date,
rating,
aggregated_rating,
cover.url,
genres.name,
platforms.name,
involved_companies.company.name,
videos.video_id;
where rating != null
& cover != null
& summary != null;
limit $limite;
offset $offset;
";

$ch=curl_init("https://api.igdb.com/v4/games");

curl_setopt_array($ch,[
CURLOPT_HTTPHEADER=>[
"Client-ID: $client_id",
"Authorization: Bearer $token"
],
CURLOPT_POSTFIELDS=>$query,
CURLOPT_RETURNTRANSFER=>true
]);

curl_multi_add_handle($mh,$ch);
$handles[]=$ch;
}


/* =========================
EXEC MULTI
========================= */

$running=null;

do{
curl_multi_exec($mh,$running);
curl_multi_select($mh);
}while($running);


/* =========================
PROCESAR RESPUESTAS
========================= */

foreach($handles as $ch){

$response=curl_multi_getcontent($ch);
$games=json_decode($response,true);

if(!$games) continue;

$conexion->begin_transaction();

foreach($games as $game){

$titulo=$game["name"]??'';
$descripcion=$game["summary"]??'';

$fecha=null;
if(isset($game["first_release_date"]))
$fecha=date("Y-m-d",$game["first_release_date"]);

$rating=null;

if(isset($game["rating"]))
$rating=round($game["rating"]/10,1);
elseif(isset($game["aggregated_rating"]))
$rating=round($game["aggregated_rating"]/10,1);

$developer=$game["involved_companies"][0]["company"]["name"]??null;

$portada=null;
if(isset($game["cover"]["url"])){
$portada="https:".$game["cover"]["url"];
$portada=str_replace("t_thumb","t_1080p",$portada);
}

$trailer=$game["videos"][0]["video_id"]??null;

$generos=[];
if(isset($game["genres"]))
foreach($game["genres"] as $g)
$generos[]=$g["name"];

$genero=implode(", ",$generos);

$plataformas=[];
if(isset($game["platforms"]))
foreach($game["platforms"] as $p)
$plataformas[]=$p["name"];

$plataforma=implode(", ",$plataformas);

if(!$titulo||!$descripcion||!$fecha||!$developer||!$rating||!$portada||!$genero||!$plataforma)
continue;

$stmt->bind_param(
"sssssssss",
$titulo,
$descripcion,
$fecha,
$developer,
$rating,
$portada,
$genero,
$plataforma,
$trailer
);

$stmt->execute();

}

$conexion->commit();

curl_multi_remove_handle($mh,$ch);
}

curl_multi_close($mh);

echo "IMPORTACION VIDEOJUEGOS FINALIZADA\n";