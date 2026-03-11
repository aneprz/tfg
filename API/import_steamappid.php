<?php

    require "../db/conexiones.php";

    /* =========================
    CACHE STEAM LIST
    ========================= */

    $cache="steam_cache.json";

    if(file_exists($cache)){

    $json=file_get_contents($cache);

    }else{

    $url="https://raw.githubusercontent.com/dgibbs64/SteamCMD-AppID-List/master/steamcmd_appid.json";

    $json=file_get_contents($url);

    file_put_contents($cache,$json);

    }

    $data=json_decode($json,true);

    $steamGames=$data["applist"]["apps"] ?? $data;

    echo "Steam cargados: ".count($steamGames)."\n";


    /* =========================
    NORMALIZAR
    ========================= */

    function norm($t){

    $t=strtolower($t);

    $t=str_replace(
    ["®","™","’","'","-","_",":","(",")","[","]","!","?","."],
    "",
    $t
    );

    $t=preg_replace('/[^a-z0-9 ]/','',$t);

    $t=preg_replace('/\s+/',' ',$t);

    return trim($t);
    }


    /* =========================
    TRIGRAM
    ========================= */

    function trigrams($s){

    $s="  ".$s." ";

    $tr=[];

    for($i=0;$i<strlen($s)-2;$i++){

    $tr[]=substr($s,$i,3);

    }

    return $tr;

    }

    function trigram_similarity($a,$b){

    $ta=trigrams($a);
    $tb=trigrams($b);

    $inter=array_intersect($ta,$tb);

    return count($inter) / max(count($ta),count($tb));

    }


    /* =========================
    INDEXAR STEAM
    ========================= */

    $index=[];

    foreach($steamGames as $g){

    if(empty($g["name"])) continue;

    $name=norm($g["name"]);

    if(!$name) continue;

    $appid=$g["appid"];

    $tokens=explode(" ",$name);

    foreach($tokens as $tok){

    $index[$tok][]=[
    "name"=>$name,
    "appid"=>$appid
    ];

    }

    }

    echo "Indice tokens creado\n";


    /* =========================
    PREPARED UPDATE
    ========================= */

    $stmt=$conexion->prepare("
    UPDATE Videojuego
    SET steam_appid=?
    WHERE id_videojuego=?
    ");


    /* =========================
    BUSCAR MATCHES
    ========================= */

    $result=mysqli_query($conexion,"
    SELECT id_videojuego,titulo
    FROM Videojuego
    WHERE steam_appid IS NULL
    ");

    $total=0;
    $encontrados=0;

    $conexion->begin_transaction();

    while($row=mysqli_fetch_assoc($result)){

    $total++;

    $id=$row["id_videojuego"];
    $titulo=$row["titulo"];

    $t=norm($titulo);

    $tokens=explode(" ",$t);

    $candidatos=[];


    /* =========================
    BUSCAR POR TOKENS
    ========================= */

    foreach($tokens as $tok){

    if(isset($index[$tok])){

    $candidatos=array_merge($candidatos,$index[$tok]);

    }

    }


    /* =========================
    QUITAR DUPLICADOS
    ========================= */

    $tmp=[];

    foreach($candidatos as $c){

    $tmp[$c["name"]]=$c;

    }

    $candidatos=array_values($tmp);


    /* =========================
    CALCULAR SCORE
    ========================= */

    $mejorScore=0;
    $mejorApp=null;

    foreach($candidatos as $c){

    $nombre=$c["name"];

    $trigram=trigram_similarity($t,$nombre);

    $tokOverlap=count(array_intersect(
    explode(" ",$t),
    explode(" ",$nombre)
    ));

    $tokScore=$tokOverlap/max(
    count(explode(" ",$t)),
    count(explode(" ",$nombre))
    );

    $score=($trigram*0.7)+($tokScore*0.3);

    if($score>$mejorScore){

    $mejorScore=$score;
    $mejorApp=$c["appid"];

    }

    }


    /* =========================
    UMBRAL
    ========================= */

    if($mejorScore>0.55){

    $stmt->bind_param("ii",$mejorApp,$id);
    $stmt->execute();

    $encontrados++;

    echo "✔ $titulo → $mejorApp (score $mejorScore)\n";

    }else{

    echo "✖ $titulo\n";

    }

    }

    $conexion->commit();


    /* =========================
    RESULTADO
    ========================= */

    echo "\n";
    echo "Total juegos: $total\n";
    echo "Steam encontrados: $encontrados\n";
    echo "Finalizado\n";