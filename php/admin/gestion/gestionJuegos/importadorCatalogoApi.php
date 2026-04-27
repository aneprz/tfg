<?php
session_start();
require_once __DIR__ . '/../../../../db/conexiones.php';
require_once __DIR__ . '/ImportPipelineService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$service = new ImportPipelineService($conexion);
$action = $_POST['action'] ?? $_GET['action'] ?? 'status';
$sessionKey = 'catalog_import_pipeline';

function respond(array $payload): void
{
    echo json_encode($payload);
    exit();
}

if ($action === 'start') {
    $config = [
        'games_limit' => $_POST['games_limit'] ?? null,
        'games_max_offset' => $_POST['games_max_offset'] ?? null,
        'steam_batch_size' => $_POST['steam_batch_size'] ?? null,
        'achievements_batch_size' => $_POST['achievements_batch_size'] ?? null
    ];

    $state = $service->buildInitialState($config);
    $state['status'] = 'running';
    $_SESSION[$sessionKey] = $state;

    respond([
        'ok' => true,
        'logs' => ['Importacion inicializada.'],
        'summary' => $service->summarize($state)
    ]);
}

if ($action === 'reset') {
    unset($_SESSION[$sessionKey]);

    respond([
        'ok' => true,
        'logs' => ['Estado de importacion reiniciado.'],
        'summary' => $service->summarize($service->buildInitialState([]))
    ]);
}

$state = $_SESSION[$sessionKey] ?? $service->buildInitialState([]);

if ($action === 'status') {
    respond([
        'ok' => true,
        'logs' => [],
        'summary' => $service->summarize($state)
    ]);
}

if ($action === 'step') {
    [$state, $logs] = $service->processStep($state);
    $_SESSION[$sessionKey] = $state;

    respond([
        'ok' => ($state['status'] ?? '') !== 'error',
        'logs' => $logs,
        'summary' => $service->summarize($state)
    ]);
}

http_response_code(400);
respond(['error' => 'Accion no valida']);
