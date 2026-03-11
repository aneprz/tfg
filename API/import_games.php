<?php

require "../db/conexiones.php";
require "credenciales.php";

/* =========================
1️⃣ OBTENER TOKEN TWITCH
========================= */

$url = "https://id.twitch.tv/oauth2/token";

$data = [
    "client_id" => $client_id,
    "client_secret" => $client_secret,
    "grant_type" => "client_credentials"
];

$options = [
    "http" => [
        "header" => "Content-Type: application/x-www-form-urlencoded",
        "method" => "POST",
        "content" => http_build_query($data)
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

$result = json_decode($response, true);
$token = $result["access_token"];


/* =========================
2️⃣ IMPORTAR JUEGOS
========================= */

$limite = 500;

/* PREPARAR INSERT (MUCHO MÁS RÁPIDO) */

$stmt = $conexion->prepare("
INSERT IGNORE INTO Videojuego
(titulo, descripcion, fecha_lanzamiento, developer, rating_medio, portada, genero, plataforma, trailer_youtube_id)
VALUES (?,?,?,?,?,?,?,?,?)
");

for ($offset = 0; $offset < 5000; $offset += $limite) {

    echo "Importando offset $offset...\n";

    $query = "
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

    $ch = curl_init("https://api.igdb.com/v4/games");

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Client-ID: $client_id",
        "Authorization: Bearer $token"
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $games = json_decode($response, true);

    if (!$games) {
        echo "No hay más juegos\n";
        break;
    }

    foreach ($games as $game) {

        $titulo = $game["name"] ?? '';
        $descripcion = $game["summary"] ?? '';

        /* FECHA */

        $fecha = null;
        if(isset($game["first_release_date"])) {
            $fecha = date("Y-m-d", $game["first_release_date"]);
        }

        /* RATING */

        $rating = null;

        if(isset($game["rating"])) {
            $rating = round($game["rating"] / 10, 1);
        }
        elseif(isset($game["aggregated_rating"])) {
            $rating = round($game["aggregated_rating"] / 10, 1);
        }

        /* DEVELOPER */

        $developer = null;

        if(isset($game["involved_companies"][0]["company"]["name"])) {
            $developer = $game["involved_companies"][0]["company"]["name"];
        }

        /* PORTADA HD */

        $portada = null;

        if(isset($game["cover"]["url"])) {

            $portada = "https:" . $game["cover"]["url"];
            $portada = str_replace("t_thumb", "t_1080p", $portada);
        }

        /* TRAILER */

        $trailer = null;

        if(isset($game["videos"][0]["video_id"])) {
            $trailer = $game["videos"][0]["video_id"];
        }

        /* GENEROS */

        $generos = [];

        if(isset($game["genres"])) {
            foreach($game["genres"] as $g){
                $generos[] = $g["name"];
            }
        }

        $genero = implode(", ", $generos);

        /* PLATAFORMAS */

        $plataformas = [];

        if(isset($game["platforms"])) {
            foreach($game["platforms"] as $p){
                $plataformas[] = $p["name"];
            }
        }

        $plataforma = implode(", ", $plataformas);


        /* FILTRO DATOS */

        if(
            empty($titulo) ||
            empty($descripcion) ||
            empty($fecha) ||
            empty($developer) ||
            empty($rating) ||
            empty($portada) ||
            empty($genero) ||
            empty($plataforma)
        ){
            continue;
        }

        /* INSERT */

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

        echo "Insertado: $titulo\n";
    }
}

echo "IMPORTACION FINALIZADA\n";