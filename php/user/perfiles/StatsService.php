<?php

require_once __DIR__ . '/../../../db/conexiones.php';

class StatsService
{
    private static function db(): mysqli
    {
        global $conexion;
        return $conexion;
    }

    public static function getUserStats($userId): array
    {
        $library = self::getLibraryRows((int) $userId);
        $stateSummary = self::buildStateSummary($library);
        $topGames = self::buildTopGames($library);
        $topGenres = self::buildTaxonomyBreakdown($library, 'genero');
        $topPlatforms = self::buildTaxonomyBreakdown($library, 'plataforma');
        $achievements = self::getAchievementsSummary((int) $userId);
        $points = self::getPointsSummary((int) $userId, $achievements['unlocked_points']);
        $social = self::getSocialSummary((int) $userId);
        $inventory = self::getInventorySummary((int) $userId);
        $communities = self::getCommunities((int) $userId);

        $overview = self::buildOverview(
            $stateSummary,
            $achievements,
            $points,
            $social,
            $inventory
        );

        return [
            'overview' => $overview,
            'rank' => self::buildRank((int) $points['lifetime_points']),
            'charts' => [
                'rating_evolution' => self::getRatingEvolution((int) $userId),
                'points_evolution' => self::getPointsEvolution((int) $userId),
                'state_distribution' => self::formatStateDistribution($stateSummary),
                'top_genres' => array_slice($topGenres, 0, 5),
                'top_platforms' => array_slice($topPlatforms, 0, 5)
            ],
            'lists' => [
                'top_games' => array_slice($topGames, 0, 5),
                'recent_achievements' => self::getRecentAchievements((int) $userId),
                'recent_reviews' => self::getRecentReviews((int) $userId),
                'communities' => $communities,
                'point_movements' => self::getRecentPointMovements((int) $userId)
            ],
            'insights' => self::buildInsights(
                $stateSummary,
                $topGames,
                $topGenres,
                $topPlatforms,
                $achievements,
                $points,
                $communities
            ),
            'activity' => self::getRecentActivity((int) $userId),
            'social' => $social,
            'inventory' => $inventory
        ];
    }

    private static function getLibraryRows(int $userId): array
    {
        return self::fetchRows(
            "
            SELECT
                v.id_videojuego,
                v.titulo,
                v.portada,
                v.genero,
                v.plataforma,
                v.tiempo_historia,
                v.tiempo_completo,
                b.estado,
                b.horas_totales,
                r.puntuacion
            FROM Biblioteca b
            JOIN Videojuego v ON v.id_videojuego = b.id_videojuego
            LEFT JOIN Resena r
                ON r.id_usuario = b.id_usuario
                AND r.id_videojuego = b.id_videojuego
            WHERE b.id_usuario = ?
            ",
            'i',
            [$userId]
        );
    }

    private static function buildOverview(
        array $stateSummary,
        array $achievements,
        array $points,
        array $social,
        array $inventory
    ): array {
        return [
            'horas_totales' => round((float) $stateSummary['total_hours'], 1),
            'juegos_totales' => (int) $stateSummary['total_games'],
            'completados' => (int) $stateSummary['counts']['completado'],
            'jugando' => (int) $stateSummary['counts']['jugando'],
            'pendientes' => (int) $stateSummary['counts']['pendiente'],
            'abandonados' => (int) $stateSummary['counts']['abandonado'],
            'ratio_abandono' => (float) $stateSummary['abandonment_ratio'],
            'ratio_completado' => (float) $stateSummary['completion_ratio'],
            'media_puntuacion' => (float) $stateSummary['rating_average'],
            'puntos_actuales' => (int) $points['current_points'],
            'puntos_ganados_totales' => (int) $points['lifetime_points'],
            'puntos_gastados' => (int) $points['spent_points'],
            'logros_desbloqueados' => (int) $achievements['unlocked_count'],
            'logros_disponibles' => (int) $achievements['available_count'],
            'porcentaje_logros' => (float) $achievements['unlock_rate'],
            'amigos' => (int) $social['friends'],
            'comunidades' => (int) $social['communities'],
            'items' => (int) $inventory['total_items']
        ];
    }

    private static function buildStateSummary(array $library): array
    {
        $counts = [
            'pendiente' => 0,
            'jugando' => 0,
            'completado' => 0,
            'abandonado' => 0,
            'otro' => 0
        ];

        $totalHours = 0.0;
        $ratingSum = 0.0;
        $ratingCount = 0;
        $completedHours = 0.0;
        $completedCount = 0;
        $storyHours = 0.0;
        $storyCount = 0;
        $fullHours = 0.0;
        $fullCount = 0;

        foreach ($library as $row) {
            $state = self::normalizeState($row['estado'] ?? '');
            $hours = (float) ($row['horas_totales'] ?? 0);
            $counts[$state]++;
            $totalHours += $hours;

            if (isset($row['puntuacion']) && $row['puntuacion'] !== null && $row['puntuacion'] !== '') {
                $ratingSum += (float) $row['puntuacion'];
                $ratingCount++;
            }

            if ($state === 'completado') {
                $completedHours += $hours;
                $completedCount++;

                $story = (int) ($row['tiempo_historia'] ?? 0);
                $full = (int) ($row['tiempo_completo'] ?? 0);

                if ($story > 0) {
                    $storyHours += $story;
                    $storyCount++;
                }

                if ($full > 0) {
                    $fullHours += $full;
                    $fullCount++;
                }
            }
        }

        $totalGames = count($library);
        $finishedGames = $counts['completado'] + $counts['abandonado'];

        return [
            'counts' => $counts,
            'total_games' => $totalGames,
            'total_hours' => $totalHours,
            'rating_average' => $ratingCount > 0 ? round($ratingSum / $ratingCount, 2) : 0.0,
            'abandonment_ratio' => $finishedGames > 0 ? $counts['abandonado'] / $finishedGames : 0.0,
            'completion_ratio' => $totalGames > 0 ? $counts['completado'] / $totalGames : 0.0,
            'avg_hours_completed' => $completedCount > 0 ? round($completedHours / $completedCount, 1) : 0.0,
            'avg_story_hours' => $storyCount > 0 ? round($storyHours / $storyCount, 1) : 0.0,
            'avg_full_hours' => $fullCount > 0 ? round($fullHours / $fullCount, 1) : 0.0
        ];
    }

    private static function buildTopGames(array $library): array
    {
        $games = [];

        foreach ($library as $row) {
            $games[] = [
                'id_videojuego' => (int) ($row['id_videojuego'] ?? 0),
                'titulo' => (string) ($row['titulo'] ?? 'Sin titulo'),
                'portada' => (string) ($row['portada'] ?? ''),
                'horas_totales' => round((float) ($row['horas_totales'] ?? 0), 1),
                'estado' => self::stateLabel(self::normalizeState($row['estado'] ?? '')),
                'puntuacion' => isset($row['puntuacion']) && $row['puntuacion'] !== null
                    ? round((float) $row['puntuacion'], 1)
                    : null
            ];
        }

        usort($games, static function (array $a, array $b): int {
            if ($a['horas_totales'] === $b['horas_totales']) {
                return strcmp($a['titulo'], $b['titulo']);
            }

            return $b['horas_totales'] <=> $a['horas_totales'];
        });

        return $games;
    }

    private static function buildTaxonomyBreakdown(array $library, string $field): array
    {
        $totals = [];

        foreach ($library as $row) {
            $hours = (float) ($row['horas_totales'] ?? 0);
            $raw = trim((string) ($row[$field] ?? ''));

            if ($raw === '') {
                continue;
            }

            $parts = preg_split('/\s*,\s*/', $raw);
            if (!is_array($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                $label = trim($part);
                if ($label === '') {
                    continue;
                }

                $key = strtolower($label);

                if (!isset($totals[$key])) {
                    $totals[$key] = [
                        'label' => $label,
                        'hours' => 0.0,
                        'games' => 0
                    ];
                }

                $totals[$key]['hours'] += $hours;
                $totals[$key]['games']++;
            }
        }

        $result = array_values($totals);

        usort($result, static function (array $a, array $b): int {
            if ($a['hours'] === $b['hours']) {
                if ($a['games'] === $b['games']) {
                    return strcmp($a['label'], $b['label']);
                }

                return $b['games'] <=> $a['games'];
            }

            return $b['hours'] <=> $a['hours'];
        });

        return array_map(static function (array $item): array {
            return [
                'label' => $item['label'],
                'hours' => round((float) $item['hours'], 1),
                'games' => (int) $item['games']
            ];
        }, $result);
    }

    private static function getAchievementsSummary(int $userId): array
    {
        $summary = self::fetchRow(
            "
            SELECT
                COUNT(*) AS unlocked_count,
                COALESCE(SUM(l.puntos_logro), 0) AS unlocked_points
            FROM Logros_Usuario lu
            JOIN Logros l ON l.id_logro = lu.id_logro
            WHERE lu.id_usuario = ?
            ",
            'i',
            [$userId]
        );

        $available = self::fetchRow(
            "
            SELECT COUNT(l.id_logro) AS available_count
            FROM Biblioteca b
            JOIN Logros l ON l.id_videojuego = b.id_videojuego
            WHERE b.id_usuario = ?
            ",
            'i',
            [$userId]
        );

        $topGame = self::fetchRow(
            "
            SELECT
                v.titulo,
                COUNT(*) AS total
            FROM Logros_Usuario lu
            JOIN Logros l ON l.id_logro = lu.id_logro
            JOIN Videojuego v ON v.id_videojuego = l.id_videojuego
            WHERE lu.id_usuario = ?
            GROUP BY l.id_videojuego, v.titulo
            ORDER BY total DESC, v.titulo ASC
            LIMIT 1
            ",
            'i',
            [$userId]
        );

        $rarest = self::fetchRow(
            "
            SELECT
                l.nombre_logro,
                l.porcentaje_global,
                v.titulo
            FROM Logros_Usuario lu
            JOIN Logros l ON l.id_logro = lu.id_logro
            LEFT JOIN Videojuego v ON v.id_videojuego = l.id_videojuego
            WHERE lu.id_usuario = ?
            ORDER BY
                CASE WHEN l.porcentaje_global IS NULL THEN 1 ELSE 0 END ASC,
                l.porcentaje_global ASC,
                lu.fecha_obtencion DESC
            LIMIT 1
            ",
            'i',
            [$userId]
        );

        $unlockedCount = (int) ($summary['unlocked_count'] ?? 0);
        $availableCount = (int) ($available['available_count'] ?? 0);

        return [
            'unlocked_count' => $unlockedCount,
            'available_count' => $availableCount,
            'unlocked_points' => (int) ($summary['unlocked_points'] ?? 0),
            'unlock_rate' => $availableCount > 0 ? $unlockedCount / $availableCount : 0.0,
            'top_game' => $topGame ? [
                'titulo' => (string) ($topGame['titulo'] ?? 'Sin datos'),
                'total' => (int) ($topGame['total'] ?? 0)
            ] : null,
            'rarest' => $rarest ? [
                'nombre_logro' => (string) ($rarest['nombre_logro'] ?? 'Sin datos'),
                'titulo' => (string) ($rarest['titulo'] ?? 'Sin juego'),
                'porcentaje_global' => $rarest['porcentaje_global'] !== null
                    ? round((float) $rarest['porcentaje_global'], 2)
                    : null
            ] : null
        ];
    }

    private static function getPointsSummary(int $userId, int $fallbackLifetimePoints): array
    {
        $user = self::fetchRow(
            "SELECT puntos_actuales FROM Usuario WHERE id_usuario = ? LIMIT 1",
            'i',
            [$userId]
        );

        $summary = self::fetchRow(
            "
            SELECT
                COALESCE(SUM(CASE WHEN puntos > 0 THEN puntos ELSE 0 END), 0) AS gained_points,
                COALESCE(SUM(CASE WHEN puntos < 0 THEN ABS(puntos) ELSE 0 END), 0) AS spent_points
            FROM Movimientos_Puntos
            WHERE id_usuario = ?
            ",
            'i',
            [$userId]
        );

        $gainedPoints = (int) ($summary['gained_points'] ?? 0);

        return [
            'current_points' => (int) ($user['puntos_actuales'] ?? 0),
            'lifetime_points' => $gainedPoints > 0 ? $gainedPoints : $fallbackLifetimePoints,
            'spent_points' => (int) ($summary['spent_points'] ?? 0)
        ];
    }

    private static function getSocialSummary(int $userId): array
    {
        $friends = self::fetchRow(
            "
            SELECT COUNT(DISTINCT amigo_id) AS total
            FROM (
                SELECT id_amigo AS amigo_id
                FROM Amigos
                WHERE id_usuario = ? AND estado = 'aceptada'
                UNION ALL
                SELECT id_usuario AS amigo_id
                FROM Amigos
                WHERE id_amigo = ? AND estado = 'aceptada'
            ) amigos_aceptados
            ",
            'ii',
            [$userId, $userId]
        );

        $communities = self::fetchRow(
            "SELECT COUNT(*) AS total FROM Miembro_Comunidad WHERE id_usuario = ?",
            'i',
            [$userId]
        );

        $posts = self::fetchRow(
            "SELECT COUNT(*) AS total FROM Post WHERE id_usuario = ?",
            'i',
            [$userId]
        );

        $likesReceived = self::fetchRow(
            "
            SELECT COUNT(*) AS total
            FROM Post_Likes pl
            JOIN Post p ON p.id_post = pl.id_post
            WHERE p.id_usuario = ?
            ",
            'i',
            [$userId]
        );

        $pendingRequests = self::fetchRow(
            "SELECT COUNT(*) AS total FROM Amigos WHERE id_amigo = ? AND estado = 'pendiente'",
            'i',
            [$userId]
        );

        $unreadNotifications = self::fetchRow(
            "SELECT COUNT(*) AS total FROM Notificacion WHERE id_usuario_destino = ? AND leida = 0",
            'i',
            [$userId]
        );

        $unreadChat = self::fetchRow(
            "
            SELECT COALESCE(SUM(mensajes_no_leidos), 0) AS total
            FROM chat_participante
            WHERE id_usuario = ?
            ",
            'i',
            [$userId]
        );

        return [
            'friends' => (int) ($friends['total'] ?? 0),
            'communities' => (int) ($communities['total'] ?? 0),
            'posts' => (int) ($posts['total'] ?? 0),
            'likes_received' => (int) ($likesReceived['total'] ?? 0),
            'pending_requests' => (int) ($pendingRequests['total'] ?? 0),
            'unread_notifications' => (int) ($unreadNotifications['total'] ?? 0),
            'unread_chat_messages' => (int) ($unreadChat['total'] ?? 0)
        ];
    }

    private static function getInventorySummary(int $userId): array
    {
        $countsByType = [];
        $countsByRarity = [];
        $equipped = [
            'avatar' => null,
            'marco' => null,
            'fondo' => null
        ];

        $rows = self::fetchRows(
            "
            SELECT
                ti.nombre,
                ti.tipo,
                ti.rareza,
                ti.imagen,
                ui.equipado
            FROM Usuario_Items ui
            JOIN Tienda_Items ti ON ti.id_item = ui.id_item
            WHERE ui.id_usuario = ?
            ",
            'i',
            [$userId]
        );

        foreach ($rows as $row) {
            $type = (string) ($row['tipo'] ?? 'desconocido');
            $rarity = (string) ($row['rareza'] ?? 'comun');

            $countsByType[$type] = ($countsByType[$type] ?? 0) + 1;
            $countsByRarity[$rarity] = ($countsByRarity[$rarity] ?? 0) + 1;

            if ((int) ($row['equipado'] ?? 0) === 1 && array_key_exists($type, $equipped)) {
                $equipped[$type] = [
                    'nombre' => (string) ($row['nombre'] ?? 'Sin nombre'),
                    'imagen' => (string) ($row['imagen'] ?? '')
                ];
            }
        }

        return [
            'total_items' => count($rows),
            'equipped_count' => count(array_filter($equipped)),
            'counts_by_type' => self::normalizeCountMap($countsByType),
            'counts_by_rarity' => self::normalizeCountMap($countsByRarity),
            'highest_rarity' => self::getHighestRarity($countsByRarity),
            'equipped' => $equipped
        ];
    }

    private static function getCommunities(int $userId): array
    {
        $rows = self::fetchRows(
            "
            SELECT
                c.id_comunidad,
                c.nombre,
                COALESCE(v.titulo, 'Sin juego') AS juego,
                (
                    SELECT COUNT(*)
                    FROM Miembro_Comunidad mc2
                    WHERE mc2.id_comunidad = c.id_comunidad
                ) AS miembros,
                (
                    SELECT COUNT(*)
                    FROM Post p
                    WHERE p.id_comunidad = c.id_comunidad
                ) AS posts
            FROM Miembro_Comunidad mc
            JOIN Comunidad c ON c.id_comunidad = mc.id_comunidad
            LEFT JOIN Videojuego v ON v.id_videojuego = c.id_videojuego_principal
            WHERE mc.id_usuario = ?
            ORDER BY miembros DESC, posts DESC, c.nombre ASC
            LIMIT 5
            ",
            'i',
            [$userId]
        );

        return array_map(static function (array $row): array {
            return [
                'id_comunidad' => (int) ($row['id_comunidad'] ?? 0),
                'nombre' => (string) ($row['nombre'] ?? 'Sin nombre'),
                'juego' => (string) ($row['juego'] ?? 'Sin juego'),
                'miembros' => (int) ($row['miembros'] ?? 0),
                'posts' => (int) ($row['posts'] ?? 0)
            ];
        }, $rows);
    }

    private static function getRatingEvolution(int $userId): array
    {
        $rows = self::fetchRows(
            "
            SELECT
                DATE_FORMAT(fecha_publicacion, '%Y-%m') AS mes,
                ROUND(AVG(puntuacion), 2) AS media,
                COUNT(*) AS total
            FROM Resena
            WHERE id_usuario = ?
            GROUP BY mes
            ORDER BY mes ASC
            ",
            'i',
            [$userId]
        );

        return array_map(static function (array $row): array {
            return [
                'mes' => (string) ($row['mes'] ?? ''),
                'media' => round((float) ($row['media'] ?? 0), 2),
                'total' => (int) ($row['total'] ?? 0)
            ];
        }, $rows);
    }

    private static function getPointsEvolution(int $userId): array
    {
        $rows = self::fetchRows(
            "
            SELECT
                DATE_FORMAT(fecha, '%Y-%m') AS mes,
                COALESCE(SUM(puntos), 0) AS neto,
                COALESCE(SUM(CASE WHEN puntos > 0 THEN puntos ELSE 0 END), 0) AS ganados,
                COALESCE(SUM(CASE WHEN puntos < 0 THEN ABS(puntos) ELSE 0 END), 0) AS gastados
            FROM Movimientos_Puntos
            WHERE id_usuario = ?
            GROUP BY mes
            ORDER BY mes ASC
            ",
            'i',
            [$userId]
        );

        return array_map(static function (array $row): array {
            return [
                'mes' => (string) ($row['mes'] ?? ''),
                'neto' => (int) ($row['neto'] ?? 0),
                'ganados' => (int) ($row['ganados'] ?? 0),
                'gastados' => (int) ($row['gastados'] ?? 0)
            ];
        }, $rows);
    }

    private static function formatStateDistribution(array $stateSummary): array
    {
        $result = [];

        foreach ($stateSummary['counts'] as $key => $count) {
            if ($count <= 0 || $key === 'otro') {
                continue;
            }

            $result[] = [
                'label' => self::stateLabel($key),
                'value' => (int) $count
            ];
        }

        return $result;
    }

    private static function getRecentAchievements(int $userId): array
    {
        $rows = self::fetchRows(
            "
            SELECT
                l.nombre_logro,
                l.porcentaje_global,
                v.titulo,
                lu.fecha_obtencion
            FROM Logros_Usuario lu
            JOIN Logros l ON l.id_logro = lu.id_logro
            LEFT JOIN Videojuego v ON v.id_videojuego = l.id_videojuego
            WHERE lu.id_usuario = ?
            ORDER BY lu.fecha_obtencion DESC
            LIMIT 5
            ",
            'i',
            [$userId]
        );

        return array_map(static function (array $row): array {
            return [
                'nombre_logro' => (string) ($row['nombre_logro'] ?? 'Sin logro'),
                'titulo' => (string) ($row['titulo'] ?? 'Sin juego'),
                'porcentaje_global' => $row['porcentaje_global'] !== null
                    ? round((float) $row['porcentaje_global'], 2)
                    : null,
                'fecha_obtencion' => (string) ($row['fecha_obtencion'] ?? '')
            ];
        }, $rows);
    }

    private static function getRecentReviews(int $userId): array
    {
        $rows = self::fetchRows(
            "
            SELECT
                v.titulo,
                r.puntuacion,
                r.texto_resena,
                r.fecha_publicacion
            FROM Resena r
            JOIN Videojuego v ON v.id_videojuego = r.id_videojuego
            WHERE r.id_usuario = ?
            ORDER BY r.fecha_publicacion DESC
            LIMIT 5
            ",
            'i',
            [$userId]
        );

        return array_map(static function (array $row): array {
            return [
                'titulo' => (string) ($row['titulo'] ?? 'Sin juego'),
                'puntuacion' => isset($row['puntuacion']) && $row['puntuacion'] !== null
                    ? round((float) $row['puntuacion'], 1)
                    : null,
                'texto_resena' => self::truncate((string) ($row['texto_resena'] ?? ''), 140),
                'fecha_publicacion' => (string) ($row['fecha_publicacion'] ?? '')
            ];
        }, $rows);
    }

    private static function getRecentPointMovements(int $userId): array
    {
        $rows = self::fetchRows(
            "
            SELECT puntos, tipo, descripcion, fecha
            FROM Movimientos_Puntos
            WHERE id_usuario = ?
            ORDER BY fecha DESC
            LIMIT 5
            ",
            'i',
            [$userId]
        );

        return array_map(static function (array $row): array {
            return [
                'puntos' => (int) ($row['puntos'] ?? 0),
                'tipo' => (string) ($row['tipo'] ?? 'sistema'),
                'descripcion' => (string) ($row['descripcion'] ?? 'Movimiento'),
                'fecha' => (string) ($row['fecha'] ?? '')
            ];
        }, $rows);
    }

    private static function getRecentActivity(int $userId): array
    {
        $latestReview = self::fetchRow(
            "
            SELECT v.titulo, r.puntuacion, r.fecha_publicacion
            FROM Resena r
            JOIN Videojuego v ON v.id_videojuego = r.id_videojuego
            WHERE r.id_usuario = ?
            ORDER BY r.fecha_publicacion DESC
            LIMIT 1
            ",
            'i',
            [$userId]
        );

        $latestAchievement = self::fetchRow(
            "
            SELECT l.nombre_logro, v.titulo, lu.fecha_obtencion
            FROM Logros_Usuario lu
            JOIN Logros l ON l.id_logro = lu.id_logro
            LEFT JOIN Videojuego v ON v.id_videojuego = l.id_videojuego
            WHERE lu.id_usuario = ?
            ORDER BY lu.fecha_obtencion DESC
            LIMIT 1
            ",
            'i',
            [$userId]
        );

        $latestPurchase = self::fetchRow(
            "
            SELECT ti.nombre, ti.rareza, ui.fecha_compra
            FROM Usuario_Items ui
            JOIN Tienda_Items ti ON ti.id_item = ui.id_item
            WHERE ui.id_usuario = ?
            ORDER BY ui.fecha_compra DESC
            LIMIT 1
            ",
            'i',
            [$userId]
        );

        $latestPost = self::fetchRow(
            "
            SELECT c.nombre, p.contenido, p.fecha_publicacion
            FROM Post p
            JOIN Comunidad c ON c.id_comunidad = p.id_comunidad
            WHERE p.id_usuario = ?
            ORDER BY p.fecha_publicacion DESC
            LIMIT 1
            ",
            'i',
            [$userId]
        );

        $latestFriend = self::fetchRow(
            "
            SELECT u.gameTag, a.fecha_amistad
            FROM Amigos a
            JOIN Usuario u
                ON u.id_usuario = CASE
                    WHEN a.id_usuario = ? THEN a.id_amigo
                    ELSE a.id_usuario
                END
            WHERE (a.id_usuario = ? OR a.id_amigo = ?)
                AND a.estado = 'aceptada'
            ORDER BY a.fecha_amistad DESC
            LIMIT 1
            ",
            'iii',
            [$userId, $userId, $userId]
        );

        return [
            'latest_review' => $latestReview ? [
                'titulo' => (string) ($latestReview['titulo'] ?? 'Sin juego'),
                'puntuacion' => isset($latestReview['puntuacion']) && $latestReview['puntuacion'] !== null
                    ? round((float) $latestReview['puntuacion'], 1)
                    : null,
                'fecha' => (string) ($latestReview['fecha_publicacion'] ?? '')
            ] : null,
            'latest_achievement' => $latestAchievement ? [
                'nombre_logro' => (string) ($latestAchievement['nombre_logro'] ?? 'Sin logro'),
                'titulo' => (string) ($latestAchievement['titulo'] ?? 'Sin juego'),
                'fecha' => (string) ($latestAchievement['fecha_obtencion'] ?? '')
            ] : null,
            'latest_purchase' => $latestPurchase ? [
                'nombre' => (string) ($latestPurchase['nombre'] ?? 'Sin item'),
                'rareza' => (string) ($latestPurchase['rareza'] ?? 'comun'),
                'fecha' => (string) ($latestPurchase['fecha_compra'] ?? '')
            ] : null,
            'latest_post' => $latestPost ? [
                'nombre' => (string) ($latestPost['nombre'] ?? 'Sin comunidad'),
                'contenido' => self::truncate((string) ($latestPost['contenido'] ?? ''), 120),
                'fecha' => (string) ($latestPost['fecha_publicacion'] ?? '')
            ] : null,
            'latest_friend' => $latestFriend ? [
                'gameTag' => (string) ($latestFriend['gameTag'] ?? 'Sin tag'),
                'fecha' => (string) ($latestFriend['fecha_amistad'] ?? '')
            ] : null
        ];
    }

    private static function buildInsights(
        array $stateSummary,
        array $topGames,
        array $topGenres,
        array $topPlatforms,
        array $achievements,
        array $points,
        array $communities
    ): array {
        $backlog = (int) $stateSummary['counts']['pendiente'];
        $activeGames = (int) $stateSummary['counts']['jugando'];
        $totalGames = max(1, (int) $stateSummary['total_games']);
        $avgHoursPerGame = $stateSummary['total_games'] > 0
            ? round($stateSummary['total_hours'] / $stateSummary['total_games'], 1)
            : 0.0;

        $storyAverage = (float) $stateSummary['avg_story_hours'];
        $fullAverage = (float) $stateSummary['avg_full_hours'];
        $completedAverage = (float) $stateSummary['avg_hours_completed'];

        $playStyle = self::buildPlayStyle($completedAverage, $storyAverage, $fullAverage);

        return [
            'backlog_count' => $backlog,
            'backlog_percent' => round(($backlog / $totalGames) * 100, 1),
            'active_games' => $activeGames,
            'avg_hours_per_game' => $avgHoursPerGame,
            'avg_hours_completed' => $completedAverage,
            'avg_story_hours' => $storyAverage,
            'avg_full_hours' => $fullAverage,
            'play_style' => $playStyle,
            'favorite_genre' => $topGenres[0] ?? null,
            'favorite_platform' => $topPlatforms[0] ?? null,
            'most_played_game' => $topGames[0] ?? null,
            'top_community' => $communities[0] ?? null,
            'achievement_focus' => $achievements['top_game'] ?? null,
            'rarest_achievement' => $achievements['rarest'] ?? null,
            'collection_value' => (int) $points['lifetime_points']
        ];
    }

    private static function buildPlayStyle(float $completedAverage, float $storyAverage, float $fullAverage): array
    {
        if ($completedAverage <= 0 || ($storyAverage <= 0 && $fullAverage <= 0)) {
            return [
                'label' => 'Sin suficiente historial',
                'copy' => 'Completa algunos juegos mas para detectar tu estilo de juego.'
            ];
        }

        if ($fullAverage > 0 && $completedAverage >= ($fullAverage * 0.9)) {
            return [
                'label' => 'Completista',
                'copy' => 'Tus horas se acercan mucho al 100%. Sueles exprimir bastante cada juego.'
            ];
        }

        if ($storyAverage > 0 && $completedAverage >= ($storyAverage * 1.2)) {
            return [
                'label' => 'Explorador',
                'copy' => 'Sueles dedicar mas horas que la historia principal. Te gusta desviarte y curiosear.'
            ];
        }

        return [
            'label' => 'Directo al grano',
            'copy' => 'Tiendes a completar juegos cerca del camino principal sin entretenerte demasiado.'
        ];
    }

    private static function buildRank(int $lifetimePoints): array
    {
        $tiers = [
            ['label' => 'Novato', 'min' => 0],
            ['label' => 'Explorador', 'min' => 500],
            ['label' => 'Guerrero', 'min' => 1500],
            ['label' => 'Cazalogros', 'min' => 3000],
            ['label' => 'Maestro', 'min' => 6000],
            ['label' => 'Leyenda', 'min' => 10000]
        ];

        $current = $tiers[0];
        $next = null;

        foreach ($tiers as $index => $tier) {
            if ($lifetimePoints >= $tier['min']) {
                $current = $tier;
                $next = $tiers[$index + 1] ?? null;
            }
        }

        if ($next === null) {
            return [
                'label' => $current['label'],
                'current_points' => $lifetimePoints,
                'next_label' => null,
                'points_to_next' => 0,
                'progress_percent' => 100
            ];
        }

        $currentMin = (int) $current['min'];
        $nextMin = (int) $next['min'];
        $range = max(1, $nextMin - $currentMin);
        $progress = (int) floor((($lifetimePoints - $currentMin) / $range) * 100);

        return [
            'label' => $current['label'],
            'current_points' => $lifetimePoints,
            'next_label' => $next['label'],
            'points_to_next' => max(0, $nextMin - $lifetimePoints),
            'progress_percent' => max(0, min(100, $progress))
        ];
    }

    private static function normalizeState(string $state): string
    {
        $normalized = strtolower(trim($state));

        if ($normalized === 'pendiente') {
            return 'pendiente';
        }

        if ($normalized === 'jugando') {
            return 'jugando';
        }

        if ($normalized === 'completado') {
            return 'completado';
        }

        if ($normalized === 'abandonado') {
            return 'abandonado';
        }

        return 'otro';
    }

    private static function stateLabel(string $state): string
    {
        if ($state === 'pendiente') {
            return 'Pendiente';
        }

        if ($state === 'jugando') {
            return 'Jugando';
        }

        if ($state === 'completado') {
            return 'Completado';
        }

        if ($state === 'abandonado') {
            return 'Abandonado';
        }

        return 'Otro';
    }

    private static function getHighestRarity(array $countsByRarity): ?string
    {
        $order = ['comun', 'raro', 'epico', 'legendario'];

        for ($index = count($order) - 1; $index >= 0; $index--) {
            $rarity = $order[$index];
            if (!empty($countsByRarity[$rarity])) {
                return $rarity;
            }
        }

        return null;
    }

    private static function normalizeCountMap(array $counts): array
    {
        $result = [];

        foreach ($counts as $label => $value) {
            $result[] = [
                'label' => ucfirst((string) $label),
                'value' => (int) $value
            ];
        }

        usort($result, static function (array $a, array $b): int {
            if ($a['value'] === $b['value']) {
                return strcmp($a['label'], $b['label']);
            }

            return $b['value'] <=> $a['value'];
        });

        return $result;
    }

    private static function truncate(string $text, int $limit): string
    {
        $text = trim($text);

        if ($text === '' || strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(substr($text, 0, $limit - 3)) . '...';
    }

    private static function fetchRows(string $sql, string $types = '', array $params = []): array
    {
        $db = self::db();
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            return [];
        }

        if (!self::executeStatement($stmt, $types, $params)) {
            $stmt->close();
            return [];
        }

        $result = $stmt->get_result();
        $rows = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        $stmt->close();

        return $rows;
    }

    private static function fetchRow(string $sql, string $types = '', array $params = []): ?array
    {
        $rows = self::fetchRows($sql, $types, $params);
        return $rows[0] ?? null;
    }

    private static function executeStatement(mysqli_stmt $stmt, string $types, array $params): bool
    {
        if ($types !== '') {
            self::bindParams($stmt, $types, $params);
        }

        return $stmt->execute();
    }

    private static function bindParams(mysqli_stmt $stmt, string $types, array $params): void
    {
        $references = [$types];

        foreach ($params as $index => $value) {
            $references[] = &$params[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $references);
    }
}
