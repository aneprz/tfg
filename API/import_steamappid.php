<?php

require "../db/conexiones.php";

/* =========================
   DESCARGAR LISTA STEAM
   ========================= */

$url = "https://raw.githubusercontent.com/dgibbs64/SteamCMD-AppID-List/master/steamcmd_appid.json";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");

$json = curl_exec($ch);

if(curl_errno($ch)){
    die("Error CURL: " . curl_error($ch));
}

curl_close($ch);

$data = json_decode($json, true);

if(!$data){
    die("Error leyendo JSON de Steam\n");
}

/* =========================
   DETECTAR ESTRUCTURA
   ========================= */

if(isset($data["applist"]["apps"])){

    $steamGames = $data["applist"]["apps"];

}elseif(isset($data[0]["appid"])){

    $steamGames = $data;

}else{

    echo "JSON recibido:\n";
    print_r(array_slice($data,0,5));
    die("Formato desconocido\n");
}

echo "Steam cargados: ".count($steamGames)."\n";


/* =========================
   FUNCION LIMPIAR TEXTO
   ========================= */

function limpiar($texto){

    $texto = strtolower($texto);

    $texto = str_replace(
        ["®","™","’","'","-","_",":","(",")","[","]","!","?","."],
        "",
        $texto
    );

    $texto = preg_replace('/[^a-z0-9 ]/', '', $texto);

    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}


/* =========================
   INDEXAR JUEGOS STEAM
   ========================= */

$steamIndex = [];

foreach($steamGames as $game){

    if(empty($game["name"])) continue;

    $nombre = limpiar($game["name"]);

    if(!$nombre) continue;

    $steamIndex[$nombre] = $game["appid"];
}

echo "Steam indexados: ".count($steamIndex)."\n";


/* =========================
   CARGAR JUEGOS DE TU DB
   ========================= */

$result = mysqli_query($conexion,"
SELECT id_videojuego,titulo
FROM Videojuego
WHERE steam_appid IS NULL
");

$total = 0;
$encontrados = 0;


while($row = mysqli_fetch_assoc($result)){

    $total++;

    $id = $row["id_videojuego"];
    $titulo = $row["titulo"];

    $titulo_limpio = limpiar($titulo);

    $appid = null;


    /* =========================
       1️⃣ MATCH EXACTO
       ========================= */

    if(isset($steamIndex[$titulo_limpio])){

        $appid = $steamIndex[$titulo_limpio];

    }


    /* =========================
       2️⃣ MATCH SIMILAR
       ========================= */

    if(!$appid){

        $mejorDistancia = 999;
        $mejorAppid = null;

        foreach($steamIndex as $nombre => $steamid){

            $distancia = levenshtein($titulo_limpio,$nombre);

            if($distancia < $mejorDistancia){

                $mejorDistancia = $distancia;
                $mejorAppid = $steamid;
            }
        }

        /* aceptar solo si son MUY parecidos */

        if($mejorDistancia <= 3){

            $appid = $mejorAppid;

        }
    }


    /* =========================
       GUARDAR RESULTADO
       ========================= */

    if($appid){

        mysqli_query($conexion,"
        UPDATE Videojuego
        SET steam_appid='$appid'
        WHERE id_videojuego='$id'
        ");

        $encontrados++;

        echo "✔ $titulo → $appid\n";

    }else{

        echo "✖ $titulo\n";
    }

}


/* =========================
   RESULTADO FINAL
   ========================= */

echo "\n";
echo "Total juegos: $total\n";
echo "Steam encontrados: $encontrados\n";
echo "Finalizado\n";