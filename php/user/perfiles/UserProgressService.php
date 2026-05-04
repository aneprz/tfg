<?php

declare(strict_types=1);

final class UserProgressService
{
    private const RANK_TIERS = [
        ['label' => 'Novato', 'min' => 0],
        ['label' => 'Explorador', 'min' => 500],
        ['label' => 'Guerrero', 'min' => 1500],
        ['label' => 'Cazalogros', 'min' => 3000],
        ['label' => 'Maestro', 'min' => 6000],
        ['label' => 'Leyenda', 'min' => 10000]
    ];

    public static function buildRankFromPoints(int $currentPoints): array
    {
        $pointsForTier = max(0, $currentPoints);
        $current = self::RANK_TIERS[0];
        $next = null;

        foreach (self::RANK_TIERS as $index => $tier) {
            if ($pointsForTier >= $tier['min']) {
                $current = $tier;
                $next = self::RANK_TIERS[$index + 1] ?? null;
            }
        }

        if ($next === null) {
            return [
                'label' => $current['label'],
                'current_points' => $currentPoints,
                'next_label' => null,
                'points_to_next' => 0,
                'progress_percent' => 100
            ];
        }

        $currentMin = (int) $current['min'];
        $nextMin = (int) $next['min'];
        $tierRange = max(1, $nextMin - $currentMin);
        $progress = (int) floor((($pointsForTier - $currentMin) / $tierRange) * 100);

        return [
            'label' => $current['label'],
            'current_points' => $currentPoints,
            'next_label' => $next['label'],
            'points_to_next' => max(0, $nextMin - $currentPoints),
            'progress_percent' => max(0, min(100, $progress))
        ];
    }

    public static function applyPointDelta(mysqli $db, int $userId, int $delta): void
    {
        $stmt = $db->prepare(
            "UPDATE Usuario SET puntos_actuales = puntos_actuales + ? WHERE id_usuario = ?"
        );

        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar la actualizacion de puntos.');
        }

        $stmt->bind_param('ii', $delta, $userId);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException($error !== '' ? $error : 'No se pudieron actualizar los puntos.');
        }

        $stmt->close();
    }

    public static function registerPointMovement(
        mysqli $db,
        int $userId,
        int $points,
        string $type,
        string $description
    ): void {
        $stmt = $db->prepare(
            "INSERT INTO Movimientos_Puntos (id_usuario, puntos, tipo, descripcion) VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) {
            throw new RuntimeException('No se pudo preparar el movimiento de puntos.');
        }

        $stmt->bind_param('iiss', $userId, $points, $type, $description);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();
            throw new RuntimeException($error !== '' ? $error : 'No se pudo registrar el movimiento de puntos.');
        }

        $stmt->close();
    }
}
