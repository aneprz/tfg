<?php

declare(strict_types=1);

final class SteamSyncProgress
{
    public static function start(string $token, int $userId, string $message): void
    {
        self::write($token, [
            'user_id' => $userId,
            'status' => 'running',
            'progress' => 0,
            'message' => $message,
            'updated_at' => time()
        ]);
    }

    public static function advance(string $token, int $userId, int $progress, string $message): void
    {
        self::write($token, [
            'user_id' => $userId,
            'status' => 'running',
            'progress' => max(0, min(100, $progress)),
            'message' => $message,
            'updated_at' => time()
        ]);
    }

    public static function finish(string $token, int $userId, string $status, int $progress, string $message): void
    {
        self::write($token, [
            'user_id' => $userId,
            'status' => $status,
            'progress' => max(0, min(100, $progress)),
            'message' => $message,
            'updated_at' => time()
        ]);
    }

    public static function read(string $token): ?array
    {
        $path = self::path($token);
        if ($path === null || !is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private static function write(string $token, array $payload): void
    {
        $path = self::path($token);
        if ($path === null) {
            return;
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private static function path(string $token): ?string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '', $token);
        if ($sanitized === null || $sanitized === '') {
            return null;
        }

        return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'salsabox_steam_sync'
            . DIRECTORY_SEPARATOR
            . $sanitized
            . '.json';
    }
}
