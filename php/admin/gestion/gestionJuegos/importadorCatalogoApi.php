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
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    if ($json === false) {
        http_response_code(500);
        echo json_encode([
            'error' => 'No se pudo serializar la respuesta JSON.',
            'json_error' => json_last_error_msg()
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit();
    }

    echo $json;
    exit();
}

if ($action === 'start') {
    $config = [
        'games_limit' => $_POST['games_limit'] ?? null,
        'games_max_offset' => $_POST['games_max_offset'] ?? null,
        'steam_batch_size' => $_POST['steam_batch_size'] ?? null,
        'achievements_batch_size' => $_POST['achievements_batch_size'] ?? null,
        'achievements_start_progress' => $_POST['achievements_start_progress'] ?? null
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

if ($action === 'start_achievements') {
    $config = [
        'games_limit' => $_POST['games_limit'] ?? null,
        'games_max_offset' => $_POST['games_max_offset'] ?? null,
        'steam_batch_size' => $_POST['steam_batch_size'] ?? null,
        'achievements_batch_size' => $_POST['achievements_batch_size'] ?? null,
        'achievements_start_progress' => $_POST['achievements_start_progress'] ?? null
    ];

    $state = $service->buildAchievementsResumeState($config);
    $state['status'] = 'running';
    $_SESSION[$sessionKey] = $state;

    $processed = (int) ($state['phases']['import_logros']['processed_games'] ?? 0);
    $total = (int) ($state['phases']['import_logros']['total_games'] ?? 0);

    respond([
        'ok' => true,
        'logs' => [
            sprintf('Importacion de logros inicializada desde %d/%d juegos procesados.', $processed, $total),
            'Los contadores de logros insertados empiezan a contar desde esta reanudacion manual.'
        ],
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
