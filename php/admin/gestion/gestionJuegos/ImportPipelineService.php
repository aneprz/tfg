<?php

final class ImportPipelineService
{
    private mysqli $db;
    private string $projectRoot;
    private string $apiDir;
    private ?array $steamAppList = null;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->projectRoot = dirname(__DIR__, 4);
        $this->apiDir = $this->projectRoot . '/API';
    }

    public function buildInitialState(array $config): array
    {
        $normalized = $this->normalizeConfig($config);
        $now = time();

        return [
            'status' => 'idle',
            'current_phase' => 'import_games',
            'started_at' => $now,
            'updated_at' => $now,
            'last_error' => null,
            'config' => $normalized,
            'phases' => [
                'import_games' => [
                    'offset' => 0,
                    'limit' => $normalized['games_limit'],
                    'max_offset' => $normalized['games_max_offset'],
                    'processed_batches' => 0,
                    'processed_records' => 0,
                    'inserted_records' => 0,
                    'skipped_records' => 0,
                    'total_batches' => (int) ceil($normalized['games_max_offset'] / $normalized['games_limit'])
                ],
                'import_steamappid' => [
                    'last_id' => 0,
                    'batch_size' => $normalized['steam_batch_size'],
                    'processed_games' => 0,
                    'matched_games' => 0,
                    'unmatched_games' => 0,
                    'total_games' => 0
                ],
                'import_logros' => [
                    'last_id' => 0,
                    'batch_size' => $normalized['achievements_batch_size'],
                    'processed_games' => 0,
                    'games_with_achievements' => 0,
                    'games_without_achievements' => 0,
                    'inserted_achievements' => 0,
                    'total_games' => 0
                ]
            ]
        ];
    }

    public function processStep(array $state): array
    {
        $logs = [];

        try {
            $phase = $state['current_phase'] ?? 'import_games';

            if ($phase === 'completed') {
                $state['status'] = 'completed';
                $logs[] = 'La importacion ya estaba completada.';
                return [$state, $logs];
            }

            if ($phase === 'import_games') {
                $state = $this->processGamesPhase($state, $logs);
            } elseif ($phase === 'import_steamappid') {
                $state = $this->processSteamAppIdPhase($state, $logs);
            } elseif ($phase === 'import_logros') {
                $state = $this->processAchievementsPhase($state, $logs);
            } else {
                throw new RuntimeException('Fase de importacion desconocida.');
            }

            $state['status'] = ($state['current_phase'] === 'completed') ? 'completed' : 'running';
            $state['updated_at'] = time();
        } catch (Throwable $exception) {
            $state['status'] = 'error';
            $state['last_error'] = $exception->getMessage();
            $state['updated_at'] = time();
            $logs[] = 'ERROR: ' . $exception->getMessage();
        }

        return [$state, $logs];
    }

    public function summarize(array $state): array
    {
        $phase = $state['current_phase'] ?? 'import_games';
        $current = $state['phases'][$phase] ?? null;
        $progress = 0;
        $processed = 0;
        $total = 0;

        if ($phase === 'import_games') {
            $processed = (int) ($current['processed_batches'] ?? 0);
            $total = max(1, (int) ($current['total_batches'] ?? 1));
        } elseif ($phase === 'import_steamappid' || $phase === 'import_logros') {
            $processed = (int) ($current['processed_games'] ?? 0);
            $total = max(1, (int) ($current['total_games'] ?? 1));
        } elseif ($phase === 'completed') {
            $processed = 1;
            $total = 1;
        }

        $progress = min(100, (int) floor(($processed / $total) * 100));

        return [
            'status' => $state['status'] ?? 'idle',
            'current_phase' => $phase,
            'current_phase_label' => $this->phaseLabel($phase),
            'progress_percent' => $progress,
            'progress_processed' => $processed,
            'progress_total' => $total,
            'state' => $state
        ];
    }

    private function processGamesPhase(array $state, array &$logs): array
    {
        $phase =& $state['phases']['import_games'];
        $offset = (int) $phase['offset'];
        $limit = (int) $phase['limit'];
        $maxOffset = (int) $phase['max_offset'];

        if ($offset >= $maxOffset) {
            $logs[] = 'Importacion de juegos terminada. Pasando a import_steamappid.';
            return $this->moveToSteamPhase($state);
        }

        $token = $this->fetchTwitchToken();
        $games = $this->fetchIgdbGames($token, $offset, $limit);

        $inserted = 0;
        $processed = 0;
        $skipped = 0;

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO Videojuego
            (titulo, descripcion, fecha_lanzamiento, developer, rating_medio, portada, genero, plataforma, trailer_youtube_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la insercion de videojuegos.');
        }

        $this->db->begin_transaction();

        try {
            foreach ($games as $game) {
                $processed++;
                $mapped = $this->mapIgdbGame($game);

                if ($mapped === null) {
                    $skipped++;
                    continue;
                }

                [
                    $titulo,
                    $descripcion,
                    $fecha,
                    $developer,
                    $rating,
                    $portada,
                    $genero,
                    $plataforma,
                    $trailer
                ] = $mapped;

                $stmt->bind_param(
                    'sssssssss',
                    $titulo,
                    $descripcion,
                    $fecha,
                    $developer,
                    $rating,
                    $portada,
                    $genero,
                    $plataforma,
                    $trailer
                );

                $stmt->execute();
                $inserted += ($stmt->affected_rows > 0) ? 1 : 0;
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }

        $phase['offset'] += $limit;
        $phase['processed_batches']++;
        $phase['processed_records'] += $processed;
        $phase['inserted_records'] += $inserted;
        $phase['skipped_records'] += $skipped;

        $logs[] = sprintf(
            'import_games offset %d: %d recibidos, %d insertados, %d omitidos.',
            $offset,
            $processed,
            $inserted,
            $skipped
        );

        if ($processed === 0 || $phase['offset'] >= $maxOffset) {
            $logs[] = 'Importacion de juegos terminada. Pasando a import_steamappid.';
            return $this->moveToSteamPhase($state);
        }

        return $state;
    }

    private function processSteamAppIdPhase(array $state, array &$logs): array
    {
        $phase =& $state['phases']['import_steamappid'];

        if (empty($phase['total_games'])) {
            $phase['total_games'] = $this->countGamesWithoutSteamAppId();
        }

        $games = $this->fetchGamesWithoutSteamAppId((int) $phase['last_id'], (int) $phase['batch_size']);

        if (empty($games)) {
            $logs[] = 'Asignacion de steam_appid terminada. Pasando a import_logros.';
            return $this->moveToAchievementsPhase($state);
        }

        $matches = $this->matchSteamAppIds($games);
        $matched = 0;
        $unmatched = 0;

        $stmt = $this->db->prepare('UPDATE Videojuego SET steam_appid = ? WHERE id_videojuego = ?');

        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la actualizacion de steam_appid.');
        }

        $this->db->begin_transaction();

        try {
            foreach ($games as $game) {
                $id = (int) $game['id_videojuego'];
                $title = $game['titulo'];

                if (isset($matches[$id])) {
                    $appid = (int) $matches[$id]['appid'];
                    $stmt->bind_param('ii', $appid, $id);
                    $stmt->execute();
                    $matched++;
                    $logs[] = sprintf('steam_appid: %s -> %d', $title, $appid);
                } else {
                    $unmatched++;
                    $logs[] = sprintf('steam_appid: sin match para %s', $title);
                }
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        }

        $phase['processed_games'] += count($games);
        $phase['matched_games'] += $matched;
        $phase['unmatched_games'] += $unmatched;
        $phase['last_id'] = (int) end($games)['id_videojuego'];

        return $state;
    }

    private function processAchievementsPhase(array $state, array &$logs): array
    {
        $phase =& $state['phases']['import_logros'];

        if (empty($phase['total_games'])) {
            $phase['total_games'] = $this->countGamesWithSteamAppId();
        }

        $games = $this->fetchGamesWithSteamAppId((int) $phase['last_id'], (int) $phase['batch_size']);

        if (empty($games)) {
            $state['current_phase'] = 'completed';
            $logs[] = 'Importacion de logros terminada.';
            return $state;
        }

        $credentials = $this->loadCredentials();
        $steamApiKey = $credentials['steam_api_key'] ?? null;

        if (!$steamApiKey) {
            throw new RuntimeException('Falta steam_api_key en API/credenciales.php.');
        }

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO Logros
            (id_videojuego, nombre_logro, descripcion, puntos_logro, icono, icono_gris, porcentaje_global, steam_api_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la insercion de logros.');
        }

        $mh = curl_multi_init();
        $handles = [];

        foreach ($games as $game) {
            $appid = (int) $game['steam_appid'];
            $schemaUrl = sprintf(
                'https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key=%s&appid=%d',
                rawurlencode($steamApiKey),
                $appid
            );
            $statsUrl = sprintf(
                'https://api.steampowered.com/ISteamUserStats/GetGlobalAchievementPercentagesForApp/v2/?gameid=%d',
                $appid
            );

            $schemaHandle = $this->createCurlHandle($schemaUrl);
            $statsHandle = $this->createCurlHandle($statsUrl);

            curl_multi_add_handle($mh, $schemaHandle);
            curl_multi_add_handle($mh, $statsHandle);

            $handles[] = [
                'game' => $game,
                'schema' => $schemaHandle,
                'stats' => $statsHandle
            ];
        }

        $this->runMultiCurl($mh);

        $insertedAchievements = 0;
        $gamesWithAchievements = 0;
        $gamesWithoutAchievements = 0;

        $this->db->begin_transaction();

        try {
            foreach ($handles as $handleGroup) {
                $game = $handleGroup['game'];
                $schema = json_decode(curl_multi_getcontent($handleGroup['schema']), true);
                $statsJson = json_decode(curl_multi_getcontent($handleGroup['stats']), true);

                curl_multi_remove_handle($mh, $handleGroup['schema']);
                curl_multi_remove_handle($mh, $handleGroup['stats']);
                curl_close($handleGroup['schema']);
                curl_close($handleGroup['stats']);

                $achievements = $schema['game']['availableGameStats']['achievements'] ?? null;

                if (!$achievements || !is_array($achievements)) {
                    $gamesWithoutAchievements++;
                    $logs[] = sprintf('logros: %s sin logros disponibles.', $game['titulo']);
                    continue;
                }

                $gamesWithAchievements++;
                $statsMap = [];

                if (isset($statsJson['achievementpercentages']['achievements']) && is_array($statsJson['achievementpercentages']['achievements'])) {
                    foreach ($statsJson['achievementpercentages']['achievements'] as $stat) {
                        if (isset($stat['name'], $stat['percent'])) {
                            $statsMap[$stat['name']] = (float) $stat['percent'];
                        }
                    }
                }

                $insertedForGame = 0;

                foreach ($achievements as $achievement) {
                    $nombre = $achievement['displayName'] ?? '';

                    if ($nombre === '') {
                        continue;
                    }

                    $descripcion = $achievement['description'] ?? '';
                    $icono = $achievement['icon'] ?? '';
                    $iconoGris = $achievement['icongray'] ?? '';
                    $steamApiName = $achievement['name'] ?? '';
                    $porcentaje = $statsMap[$steamApiName] ?? null;
                    $puntos = $this->calculateAchievementPoints($porcentaje);

                    $stmt->bind_param(
                        'ississds',
                        $game['id_videojuego'],
                        $nombre,
                        $descripcion,
                        $puntos,
                        $icono,
                        $iconoGris,
                        $porcentaje,
                        $steamApiName
                    );

                    $stmt->execute();
                    $insertedForGame += ($stmt->affected_rows > 0) ? 1 : 0;
                }

                $insertedAchievements += $insertedForGame;
                $logs[] = sprintf('logros: %s -> %d insertados.', $game['titulo'], $insertedForGame);
            }

            $this->db->commit();
        } catch (Throwable $exception) {
            $this->db->rollback();
            throw $exception;
        } finally {
            curl_multi_close($mh);
        }

        $phase['processed_games'] += count($games);
        $phase['games_with_achievements'] += $gamesWithAchievements;
        $phase['games_without_achievements'] += $gamesWithoutAchievements;
        $phase['inserted_achievements'] += $insertedAchievements;
        $phase['last_id'] = (int) end($games)['id_videojuego'];

        return $state;
    }

    private function moveToSteamPhase(array $state): array
    {
        $state['current_phase'] = 'import_steamappid';
        $state['phases']['import_steamappid']['total_games'] = $this->countGamesWithoutSteamAppId();
        return $state;
    }

    private function moveToAchievementsPhase(array $state): array
    {
        $state['current_phase'] = 'import_logros';
        $state['phases']['import_logros']['total_games'] = $this->countGamesWithSteamAppId();
        return $state;
    }

    private function fetchTwitchToken(): string
    {
        $credentials = $this->loadCredentials();
        $clientId = $credentials['client_id'] ?? null;
        $clientSecret = $credentials['client_secret'] ?? null;

        if (!$clientId || !$clientSecret) {
            throw new RuntimeException('Faltan client_id o client_secret en API/credenciales.php.');
        }

        $handle = curl_init('https://id.twitch.tv/oauth2/token');

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'client_credentials'
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);

        $response = curl_exec($handle);

        if ($response === false) {
            throw new RuntimeException('No se pudo obtener el token de Twitch: ' . curl_error($handle));
        }

        $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        $data = json_decode($response, true);

        if ($httpCode >= 400 || empty($data['access_token'])) {
            throw new RuntimeException('Respuesta invalida al pedir token de Twitch.');
        }

        return $data['access_token'];
    }

    private function fetchIgdbGames(string $token, int $offset, int $limit): array
    {
        $credentials = $this->loadCredentials();
        $clientId = $credentials['client_id'] ?? null;

        if (!$clientId) {
            throw new RuntimeException('Falta client_id en API/credenciales.php.');
        }

        $query = sprintf(
            "fields\nname,\nsummary,\nfirst_release_date,\nrating,\naggregated_rating,\ncover.url,\ngenres.name,\nplatforms.name,\ninvolved_companies.company.name,\nvideos.video_id;\nwhere rating != null\n& cover != null\n& summary != null;\nlimit %d;\noffset %d;\n",
            $limit,
            $offset
        );

        $handle = curl_init('https://api.igdb.com/v4/games');

        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_POSTFIELDS => $query,
            CURLOPT_HTTPHEADER => [
                'Client-ID: ' . $clientId,
                'Authorization: Bearer ' . $token
            ]
        ]);

        $response = curl_exec($handle);

        if ($response === false) {
            throw new RuntimeException('Fallo consultando IGDB: ' . curl_error($handle));
        }

        $httpCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if ($httpCode >= 400) {
            throw new RuntimeException('IGDB devolvio HTTP ' . $httpCode . '.');
        }

        $games = json_decode($response, true);

        if (!is_array($games)) {
            return [];
        }

        return $games;
    }

    private function mapIgdbGame(array $game): ?array
    {
        $titulo = $game['name'] ?? '';
        $descripcion = $game['summary'] ?? '';
        $fecha = isset($game['first_release_date']) ? date('Y-m-d', (int) $game['first_release_date']) : null;
        $developer = $game['involved_companies'][0]['company']['name'] ?? null;

        $rating = null;
        if (isset($game['rating'])) {
            $rating = round(((float) $game['rating']) / 10, 1);
        } elseif (isset($game['aggregated_rating'])) {
            $rating = round(((float) $game['aggregated_rating']) / 10, 1);
        }

        $portada = null;
        if (!empty($game['cover']['url'])) {
            $portada = 'https:' . $game['cover']['url'];
            $portada = str_replace('t_thumb', 't_1080p', $portada);
        }

        $trailer = $game['videos'][0]['video_id'] ?? null;

        $generos = [];
        if (!empty($game['genres']) && is_array($game['genres'])) {
            foreach ($game['genres'] as $genre) {
                if (!empty($genre['name'])) {
                    $generos[] = $genre['name'];
                }
            }
        }

        $plataformas = [];
        if (!empty($game['platforms']) && is_array($game['platforms'])) {
            foreach ($game['platforms'] as $platform) {
                if (!empty($platform['name'])) {
                    $plataformas[] = $platform['name'];
                }
            }
        }

        $genero = implode(', ', $generos);
        $plataforma = implode(', ', $plataformas);

        if (
            $titulo === '' ||
            $descripcion === '' ||
            !$fecha ||
            !$developer ||
            $rating === null ||
            !$portada ||
            $genero === '' ||
            $plataforma === ''
        ) {
            return null;
        }

        return [
            $titulo,
            $descripcion,
            $fecha,
            $developer,
            (string) $rating,
            $portada,
            $genero,
            $plataforma,
            $trailer
        ];
    }

    private function fetchGamesWithoutSteamAppId(int $lastId, int $batchSize): array
    {
        $stmt = $this->db->prepare(
            'SELECT id_videojuego, titulo
            FROM Videojuego
            WHERE steam_appid IS NULL AND id_videojuego > ?
            ORDER BY id_videojuego ASC
            LIMIT ?'
        );

        if (!$stmt) {
            throw new RuntimeException('No se pudo cargar el lote para import_steamappid.');
        }

        $stmt->bind_param('ii', $lastId, $batchSize);
        $stmt->execute();
        $result = $stmt->get_result();
        $games = [];

        while ($row = $result->fetch_assoc()) {
            $games[] = $row;
        }

        return $games;
    }

    private function fetchGamesWithSteamAppId(int $lastId, int $batchSize): array
    {
        $stmt = $this->db->prepare(
            'SELECT id_videojuego, steam_appid, titulo
            FROM Videojuego
            WHERE steam_appid IS NOT NULL AND id_videojuego > ?
            ORDER BY id_videojuego ASC
            LIMIT ?'
        );

        if (!$stmt) {
            throw new RuntimeException('No se pudo cargar el lote para import_logros.');
        }

        $stmt->bind_param('ii', $lastId, $batchSize);
        $stmt->execute();
        $result = $stmt->get_result();
        $games = [];

        while ($row = $result->fetch_assoc()) {
            $row['id_videojuego'] = (int) $row['id_videojuego'];
            $row['steam_appid'] = (int) $row['steam_appid'];
            $games[] = $row;
        }

        return $games;
    }

    private function countGamesWithoutSteamAppId(): int
    {
        $result = $this->db->query('SELECT COUNT(*) AS total FROM Videojuego WHERE steam_appid IS NULL');
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int) ($row['total'] ?? 0);
    }

    private function countGamesWithSteamAppId(): int
    {
        $result = $this->db->query('SELECT COUNT(*) AS total FROM Videojuego WHERE steam_appid IS NOT NULL');
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        return (int) ($row['total'] ?? 0);
    }

    private function matchSteamAppIds(array $games): array
    {
        $steamGames = $this->loadSteamAppList();
        $targetsById = [];
        $targetsByName = [];
        $targetsByToken = [];
        $candidateMap = [];
        $exactMatches = [];

        foreach ($games as $game) {
            $id = (int) $game['id_videojuego'];
            $normalized = $this->normalizeText($game['titulo']);
            $tokens = ($normalized === '') ? [] : explode(' ', $normalized);

            $targetsById[$id] = [
                'title' => $game['titulo'],
                'normalized' => $normalized,
                'tokens' => $tokens
            ];

            if ($normalized !== '') {
                $targetsByName[$normalized][] = $id;
            }

            foreach ($tokens as $token) {
                $targetsByToken[$token][$id] = true;
            }

            $candidateMap[$id] = [];
        }

        foreach ($steamGames as $steamGame) {
            $name = $steamGame['name'] ?? '';

            if ($name === '') {
                continue;
            }

            $normalizedSteamName = $this->normalizeText($name);
            if ($normalizedSteamName === '') {
                continue;
            }

            $appid = (int) ($steamGame['appid'] ?? 0);
            if ($appid <= 0) {
                continue;
            }

            if (isset($targetsByName[$normalizedSteamName])) {
                foreach ($targetsByName[$normalizedSteamName] as $targetId) {
                    $exactMatches[$targetId] = [
                        'appid' => $appid,
                        'name' => $normalizedSteamName
                    ];
                }
            }

            $steamTokens = explode(' ', $normalizedSteamName);
            $matchedTargetIds = [];

            foreach ($steamTokens as $token) {
                if (!isset($targetsByToken[$token])) {
                    continue;
                }

                foreach ($targetsByToken[$token] as $targetId => $_) {
                    $matchedTargetIds[$targetId] = true;
                }
            }

            if (!$matchedTargetIds) {
                continue;
            }

            foreach (array_keys($matchedTargetIds) as $targetId) {
                $candidateMap[$targetId][$normalizedSteamName] = [
                    'appid' => $appid,
                    'name' => $normalizedSteamName,
                    'tokens' => $steamTokens
                ];
            }
        }

        $matches = [];

        foreach ($targetsById as $id => $target) {
            if ($target['normalized'] === '') {
                continue;
            }

            if (isset($exactMatches[$id])) {
                $matches[$id] = $exactMatches[$id];
                continue;
            }

            $bestScore = 0.0;
            $bestMatch = null;

            foreach ($candidateMap[$id] as $candidate) {
                $tokenOverlap = count(array_intersect($target['tokens'], $candidate['tokens']));

                if ($tokenOverlap === 0) {
                    continue;
                }

                $tokenScore = $tokenOverlap / max(count($target['tokens']), count($candidate['tokens']));

                if ($tokenScore < 0.3) {
                    continue;
                }

                $trigramScore = $this->trigramSimilarity($target['normalized'], $candidate['name']);
                $score = ($trigramScore * 0.7) + ($tokenScore * 0.3);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = [
                        'appid' => $candidate['appid'],
                        'name' => $candidate['name']
                    ];
                }
            }

            if ($bestMatch !== null && $bestScore > 0.55) {
                $matches[$id] = $bestMatch;
            }
        }

        return $matches;
    }

    private function loadSteamAppList(): array
    {
        if ($this->steamAppList !== null) {
            return $this->steamAppList;
        }

        $cachePath = $this->apiDir . '/steam_cache.json';

        if (is_file($cachePath)) {
            $json = file_get_contents($cachePath);
        } else {
            $handle = $this->createCurlHandle('https://raw.githubusercontent.com/dgibbs64/SteamCMD-AppID-List/master/steamcmd_appid.json');
            $json = curl_exec($handle);

            if ($json === false) {
                throw new RuntimeException('No se pudo descargar la cache de Steam: ' . curl_error($handle));
            }

            curl_close($handle);
            file_put_contents($cachePath, $json);
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new RuntimeException('La cache de Steam no tiene un formato valido.');
        }

        $this->steamAppList = $data['applist']['apps'] ?? $data;

        if (!is_array($this->steamAppList)) {
            throw new RuntimeException('No se pudo leer la lista de apps de Steam.');
        }

        return $this->steamAppList;
    }

    private function createCurlHandle(string $url)
    {
        $handle = curl_init($url);

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERAGENT => 'SalsaBox Importer/1.0'
        ]);

        return $handle;
    }

    private function runMultiCurl($multiHandle): void
    {
        $running = null;

        do {
            $status = curl_multi_exec($multiHandle, $running);

            if ($status > CURLM_OK) {
                throw new RuntimeException('Fallo ejecutando las peticiones concurrentes.');
            }

            if ($running) {
                $select = curl_multi_select($multiHandle, 1.0);
                if ($select === -1) {
                    usleep(100000);
                }
            }
        } while ($running);
    }

    private function calculateAchievementPoints(?float $percent): int
    {
        if ($percent === null || $percent >= 75) {
            return 1;
        }
        if ($percent >= 50) {
            return 2;
        }
        if ($percent >= 25) {
            return 3;
        }
        if ($percent >= 10) {
            return 4;
        }
        if ($percent >= 5) {
            return 6;
        }

        return 8;
    }

    private function loadCredentials(): array
    {
        $path = $this->apiDir . '/credenciales.php';

        if (!is_file($path)) {
            throw new RuntimeException('No existe API/credenciales.php.');
        }

        $credentials = require $path;

        if (is_array($credentials)) {
            return $credentials;
        }

        $available = [];
        $keys = ['client_id', 'client_secret', 'steam_api_key'];

        foreach ($keys as $key) {
            if (isset($$key)) {
                $available[$key] = $$key;
            }
        }

        return $available;
    }

    private function normalizeConfig(array $config): array
    {
        return [
            'games_limit' => $this->clamp((int) ($config['games_limit'] ?? 150), 25, 500),
            'games_max_offset' => $this->clamp((int) ($config['games_max_offset'] ?? 10000), 100, 50000),
            'steam_batch_size' => $this->clamp((int) ($config['steam_batch_size'] ?? 80), 10, 500),
            'achievements_batch_size' => $this->clamp((int) ($config['achievements_batch_size'] ?? 8), 1, 30)
        ];
    }

    private function clamp(int $value, int $min, int $max): int
    {
        return max($min, min($max, $value));
    }

    private function phaseLabel(string $phase): string
    {
        if ($phase === 'import_games') {
            return 'import_games';
        }
        if ($phase === 'import_steamappid') {
            return 'import_steamappid';
        }
        if ($phase === 'import_logros') {
            return 'import_logros';
        }
        if ($phase === 'completed') {
            return 'Completado';
        }

        return $phase;
    }

    private function normalizeText(string $text): string
    {
        $text = strtolower($text);
        $text = str_replace(
            ['®', '™', '’', "'", '-', '_', ':', '(', ')', '[', ']', '!', '?', '.'],
            '',
            $text
        );
        $text = preg_replace('/[^a-z0-9 ]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim((string) $text);
    }

    private function trigramSimilarity(string $first, string $second): float
    {
        $firstTrigrams = $this->trigrams($first);
        $secondTrigrams = $this->trigrams($second);

        if (!$firstTrigrams || !$secondTrigrams) {
            return 0.0;
        }

        $intersection = array_intersect($firstTrigrams, $secondTrigrams);

        return count($intersection) / max(count($firstTrigrams), count($secondTrigrams));
    }

    private function trigrams(string $value): array
    {
        $value = '  ' . $value . ' ';
        $result = [];
        $length = strlen($value);

        for ($index = 0; $index < $length - 2; $index++) {
            $result[] = substr($value, $index, 3);
        }

        return $result;
    }
}
