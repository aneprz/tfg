<?php

$steam_login_url = "https://steamcommunity.com/openid/login";
$dominio = "http://" . $_SERVER['HTTP_HOST'];

$params = [
"openid.ns" => "http://specs.openid.net/auth/2.0",
"openid.mode" => "checkid_setup",
"openid.return_to" => $dominio."/Steam/steam_callback.php",
"openid.realm" => $dominio."/",
"openid.identity" => "http://specs.openid.net/auth/2.0/identifier_select",
"openid.claimed_id" => "http://specs.openid.net/auth/2.0/identifier_select"
];

header("Location: ".$steam_login_url."?".http_build_query($params));
exit;