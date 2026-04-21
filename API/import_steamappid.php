<?php
ini_set('memory_limit', '512M');
require "../db/conexiones.php";


/* =========================
CACHE LISTA STEAM
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
NORMALIZAR TEXTO
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

return count($inter)/max(count($ta),count($tb));

}


/* =========================
INDEXAR STEAM
========================= */

$exact=[];
$tokenIndex=[];
$steamData=[];

foreach($steamGames as $g){

if(empty($g["name"])) continue;

$name=norm($g["name"]);
if(!$name) continue;

$appid=$g["appid"];

$tokens=explode(" ",$name);

$steamData[$name]=[
"appid"=>$appid,
"tokens"=>$tokens
];

$exact[$name]=$appid;

foreach($tokens as $tok){
$tokenIndex[$tok][]=$name;
}

}

echo "Indice creado\n";


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


/* =========================
1️⃣ MATCH EXACTO
========================= */

if(isset($exact[$t])){

$stmt->bind_param("ii",$exact[$t],$id);
$stmt->execute();

$encontrados++;

echo "✔ $titulo → ".$exact[$t]."\n";

continue;

}


/* =========================
2️⃣ BUSCAR CANDIDATOS
========================= */

$tokens=explode(" ",$t);

$candidatos=[];

foreach($tokens as $tok){

if(isset($tokenIndex[$tok])){

$candidatos=array_merge($candidatos,$tokenIndex[$tok]);

}

}

$candidatos=array_unique($candidatos);


/* =========================
3️⃣ SIMILARIDAD
========================= */

$mejorScore=0;
$mejorApp=null;

foreach($candidatos as $name){

$c=$steamData[$name];

$tokOverlap=count(array_intersect($tokens,$c["tokens"]));

if($tokOverlap==0) continue;

$tokScore=$tokOverlap/max(count($tokens),count($c["tokens"]));

if($tokScore<0.3) continue;

$trigram=trigram_similarity($t,$name);

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