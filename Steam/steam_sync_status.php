<?php

declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/SteamSyncProgress.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'progress' => 0,
        'message' => 'Sesion no valida.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int) $_SESSION['id_usuario'];
session_write_close();

$token = (string) ($_GET['token'] ?? '');
$progress = SteamSyncProgress::read($token);

if ($progress === null) {
    echo json_encode([
        'status' => 'starting',
        'progress' => 0,
        'message' => 'Preparando sincronizacion...'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ((int) ($progress['user_id'] ?? 0) !== $userId) {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'progress' => 0,
        'message' => 'No autorizado.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'status' => (string) ($progress['status'] ?? 'running'),
    'progress' => (int) ($progress['progress'] ?? 0),
    'message' => (string) ($progress['message'] ?? 'Sincronizando...')
], JSON_UNESCAPED_UNICODE);
