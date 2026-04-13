<?php

session_start();
require "../db/conexiones.php";

/* comprobar que Steam devolvió datos */

if(!isset($_GET["openid_identity"]) && !isset($_GET["openid.identity"])){
    die("Error login Steam");
}

/* Steam a veces usa uno u otro */

$url = $_GET["openid_identity"] ?? $_GET["openid.identity"];

/* EXTRAER STEAMID */

preg_match("~/(?:openid/)?id/([0-9]+)~", $url, $matches);

if(!isset($matches[1])){
    die("SteamID no encontrado");
}

$steamid = $matches[1];

/* comprobar que Usuario esté logueado */

if(!isset($_SESSION["id_usuario"])){
    die("Usuario no logueado");
}

$id_usuario = $_SESSION["id_usuario"];

/* guardar steamid */

mysqli_query($conexion,"
UPDATE Usuario
SET steamid='$steamid'
WHERE id_usuario='$id_usuario'
");

/* mensaje de éxito */

$_SESSION["steam_vinculado"] = true;

/* redirigir */

header("Location: ../php/user/perfiles/perfilSesion.php");
exit;
