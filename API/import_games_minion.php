<?php

require "../db/conexiones.php";
require "credenciales.php";

// Aumentamos memoria por si acaso
ini_set('memory_limit', '512M');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* =========================
TOKEN TWITCH (Seguro para Windows)
========================= */
$url = "https://id.twitch.tv/oauth2/token";
$data = [
    "client_id" => $client_id,
    "client_secret" => $client_secret,
    "grant_type" => "client_credentials"
];

$ch_token = curl_init($url);
curl_setopt($ch_token, CURLOPT_POST, true);
curl_setopt($ch_token, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch_token, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_token, CURLOPT_SSL_VERIFYPEER, false); // Parche Windows
curl_setopt($ch_token, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch_token);
$err = curl_error($ch_token);

if ($err || !$response) {
    die("❌ Error obteniendo el token de Twitch: $err\n");
}

$token = json_decode($response, true)["access_token"];
echo "✅ Token obtenido correctamente.\n";


/* =========================
PREPARED INSERT
========================= */
// Pongo INSERT IGNORE para que si un juego ya existe, no pete el script entero
// SE HAN AÑADIDO LAS 2 COLUMNAS NUEVAS AL FINAL
$stmt = $conexion->prepare("
INSERT IGNORE INTO Videojuego
(titulo, descripcion, fecha_lanzamiento, developer, rating_medio, portada, genero, plataforma, trailer_youtube_id, tiempo_historia, tiempo_completo)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

/* =========================
CONFIG
========================= */
$limite = 500;
$max_offset = 20000;

/* =========================
EJECUCIÓN SECUENCIAL (Paginación por ID para superar el límite de 10K)
========================= */
$last_id = 0; // Empezamos desde el ID 0
$juegos_totales = 0;

while (true) { // El bucle se romperá solo cuando no queden más juegos
    
    echo "Descargando bloque de $limite juegos a partir del ID $last_id... ";

    // CAMBIO CLAVE: Pedimos el campo 'id', filtramos por 'id > last_id', ordenamos y quitamos el 'offset'
    $query = "
        fields
        id,
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
        where summary != null & id > $last_id;
        sort id asc;
        limit $limite;
    ";

    $ch = curl_init("https://api.igdb.com/v4/games");

    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "Client-ID: $client_id",
            "Authorization: Bearer $token",
            "Content-Type: text/plain"
        ],
        CURLOPT_POSTFIELDS => $query,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch); <- ELIMINADO PARA QUITAR EL WARNING DE PHP 8.5

    if ($http_code !== 200) {
        echo "❌ Error API IGDB (Código $http_code)\n";
        break; // Si hay error grave, paramos el bucle
    }

    $games = json_decode($response, true);

    // Si nos devuelve un array vacío, significa que ya hemos descargado toda la base de datos de IGDB
    if (empty($games)) {
        echo "✅ No hay más juegos en IGDB.\n";
        break;
    }

    $conexion->begin_transaction();
    $insertados = 0;

    foreach ($games as $game) {
        $titulo = $game["name"] ?? null;
        $descripcion = $game["summary"] ?? null;

        if (!$titulo || !$descripcion) continue;

        $fecha = isset($game["first_release_date"]) ? date("Y-m-d", $game["first_release_date"]) : null;
        
        $rating = null;
        if (isset($game["rating"])) {
            $rating = round($game["rating"] / 10, 1);
        } elseif (isset($game["aggregated_rating"])) {
            $rating = round($game["aggregated_rating"] / 10, 1);
        }

        $developer = $game["involved_companies"][0]["company"]["name"] ?? null;

        $portada = null;
        if (isset($game["cover"]["url"])) {
            $portada = "https:" . $game["cover"]["url"];
            $portada = str_replace("t_thumb", "t_1080p", $portada);
        }

        $genero = isset($game["genres"]) ? implode(", ", array_column($game["genres"], "name")) : null;
        $plataforma = isset($game["platforms"]) ? implode(", ", array_column($game["platforms"], "name")) : null;
        $trailer = $game["videos"][0]["video_id"] ?? null;

        // Lo dejamos a 0 por ahora para que el script no explote
        $t_historia = 0;
        $t_completo = 0;

        $stmt->bind_param(
            "sssssssssii",
            $titulo, $descripcion, $fecha, $developer, $rating, $portada, $genero, $plataforma, $trailer, $t_historia, $t_completo
        );

        if ($stmt->execute()) {
            $insertados++;
            $juegos_totales++;
        }

        // ACTUALIZAR EL LAST_ID PARA LA SIGUIENTE RONDA
        $last_id = $game["id"]; 
    }

    $conexion->commit();
    echo "✔ $insertados guardados.\n";

    // PAUSA OBLIGATORIA
    usleep(250000); 

    // Si quieres poner un límite para que no descargue 300.000 juegos, quita el comentario de abajo
    // if ($juegos_totales >= 20000) break; 
}
// =========================================================================
// NUEVO BLOQUE: GENERACIÓN AUTOMÁTICA DE HORAS
// =========================================================================
echo "\n⏳ Generando horas de juego realistas para el catálogo...\n";

// 1. RPGs y Aventuras (Juegos largos)
$conexion->query("UPDATE Videojuego SET tiempo_historia = FLOOR(RAND() * (60 - 30 + 1) + 30), tiempo_completo = tiempo_historia + FLOOR(RAND() * (40 - 20 + 1) + 20) WHERE genero LIKE '%Role-playing%' OR genero LIKE '%RPG%' OR genero LIKE '%Adventure%'");

// 2. Shooters y Acción (Juegos medios)
$conexion->query("UPDATE Videojuego SET tiempo_historia = FLOOR(RAND() * (15 - 8 + 1) + 8), tiempo_completo = tiempo_historia + FLOOR(RAND() * (15 - 10 + 1) + 10) WHERE (genero LIKE '%Shooter%' OR genero LIKE '%Action%') AND tiempo_historia = 0");

// 3. Puzzles y Plataformas (Juegos cortos)
$conexion->query("UPDATE Videojuego SET tiempo_historia = FLOOR(RAND() * (7 - 3 + 1) + 3), tiempo_completo = tiempo_historia + FLOOR(RAND() * (5 - 2 + 1) + 2) WHERE (genero LIKE '%Puzzle%' OR genero LIKE '%Platform%' OR genero LIKE '%Arcade%') AND tiempo_historia = 0");

// 4. Relleno para el resto
$conexion->query("UPDATE Videojuego SET tiempo_historia = FLOOR(RAND() * (20 - 10 + 1) + 10), tiempo_completo = tiempo_historia + FLOOR(RAND() * (15 - 5 + 1) + 5) WHERE tiempo_historia = 0");

echo "✅ Horas generadas correctamente en la base de datos.\n";
// =========================================================================

echo "\n✨ IMPORTACIÓN FINALIZADA. TOTAL JUEGOS: $juegos_totales ✨\n";