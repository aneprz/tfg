<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/../php/user/perfiles/StatsService.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

echo json_encode(StatsService::getUserStats($_SESSION['id_usuario']));