<?php

declare(strict_types=1);

function salsabox_remember_cookie_name(): string
{
    return 'salsabox_remember';
}

function salsabox_remember_lifetime_seconds(): int
{
    return 60 * 60 * 24 * 30; // 30 días
}

function salsabox_is_https_request(): bool
{
    if (!isset($_SERVER['HTTPS'])) {
        return false;
    }

    $https = strtolower((string) $_SERVER['HTTPS']);
    return $https !== '' && $https !== 'off' && $https !== '0';
}

function salsabox_set_cookie(string $name, string $value, int $expiresAt): void
{
    $options = [
        'expires' => $expiresAt,
        'path' => '/',
        'secure' => salsabox_is_https_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    setcookie($name, $value, $options);
}

function salsabox_clear_cookie(string $name): void
{
    salsabox_set_cookie($name, '', time() - 3600);
    unset($_COOKIE[$name]);
}

function salsabox_parse_remember_cookie(string $cookieValue): ?array
{
    $cookieValue = trim($cookieValue);
    if ($cookieValue === '') {
        return null;
    }

    $parts = explode(':', $cookieValue, 2);
    if (count($parts) !== 2) {
        return null;
    }

    [$selector, $validator] = $parts;
    $selector = trim($selector);
    $validator = trim($validator);

    if ($selector === '' || $validator === '') {
        return null;
    }

    if (!ctype_xdigit($selector) || !ctype_xdigit($validator)) {
        return null;
    }

    if (strlen($selector) < 12 || strlen($selector) > 64) {
        return null;
    }

    if (strlen($validator) !== 64) {
        return null;
    }

    return ['selector' => $selector, 'validator' => $validator];
}

function salsabox_issue_remember_token(mysqli $conexion, int $userId): void
{
    $selector = bin2hex(random_bytes(9)); // 18 chars
    $validator = bin2hex(random_bytes(32)); // 64 chars
    $tokenHash = hash('sha256', $validator);

    $expiresAt = time() + salsabox_remember_lifetime_seconds();
    $expiresAtSql = date('Y-m-d H:i:s', $expiresAt);

    $stmt = $conexion->prepare("
        INSERT INTO Remember_Token (id_usuario, selector, token_hash, expires_at)
        VALUES (?, ?, ?, ?)
    ");
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('isss', $userId, $selector, $tokenHash, $expiresAtSql);
    if ($stmt->execute()) {
        $cookieValue = $selector . ':' . $validator;
        salsabox_set_cookie(salsabox_remember_cookie_name(), $cookieValue, $expiresAt);
        $_COOKIE[salsabox_remember_cookie_name()] = $cookieValue;
    }

    $stmt->close();
}

function salsabox_forget_current_remember_token(mysqli $conexion): void
{
    $cookieName = salsabox_remember_cookie_name();
    if (empty($_COOKIE[$cookieName])) {
        return;
    }

    $parsed = salsabox_parse_remember_cookie((string) $_COOKIE[$cookieName]);
    if (!$parsed) {
        return;
    }

    $selector = $parsed['selector'];
    $stmt = $conexion->prepare("DELETE FROM Remember_Token WHERE selector = ? LIMIT 1");
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('s', $selector);
    $stmt->execute();
    $stmt->close();
}

function salsabox_clear_remember_cookie(): void
{
    salsabox_clear_cookie(salsabox_remember_cookie_name());
}

function salsabox_try_remember_login(mysqli $conexion): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }

    if (!empty($_SESSION['id_usuario'])) {
        return true;
    }

    $cookieName = salsabox_remember_cookie_name();
    if (empty($_COOKIE[$cookieName])) {
        return false;
    }

    $parsed = salsabox_parse_remember_cookie((string) $_COOKIE[$cookieName]);
    if (!$parsed) {
        salsabox_clear_remember_cookie();
        return false;
    }

    $selector = $parsed['selector'];
    $validator = $parsed['validator'];
    $validatorHash = hash('sha256', $validator);

    $stmt = $conexion->prepare("
        SELECT id_remember_token, id_usuario, token_hash, expires_at
        FROM Remember_Token
        WHERE selector = ?
        LIMIT 1
    ");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('s', $selector);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row) {
        salsabox_clear_remember_cookie();
        return false;
    }

    $expiresAt = strtotime((string) $row['expires_at']);
    if ($expiresAt === false || $expiresAt < time()) {
        $stmt = $conexion->prepare("DELETE FROM Remember_Token WHERE id_remember_token = ? LIMIT 1");
        if ($stmt) {
            $id = (int) $row['id_remember_token'];
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
        salsabox_clear_remember_cookie();
        return false;
    }

    if (!hash_equals((string) $row['token_hash'], $validatorHash)) {
        $stmt = $conexion->prepare("DELETE FROM Remember_Token WHERE id_remember_token = ? LIMIT 1");
        if ($stmt) {
            $id = (int) $row['id_remember_token'];
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
        salsabox_clear_remember_cookie();
        return false;
    }

    $userId = (int) $row['id_usuario'];

    $stmt = $conexion->prepare("
        SELECT id_usuario, gameTag, admin, email_verificado
        FROM Usuario
        WHERE id_usuario = ?
        LIMIT 1
    ");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $resUser = $stmt->get_result();
    $user = $resUser ? $resUser->fetch_assoc() : null;
    $stmt->close();

    if (!$user || empty($user['email_verificado'])) {
        $stmt = $conexion->prepare("DELETE FROM Remember_Token WHERE id_remember_token = ? LIMIT 1");
        if ($stmt) {
            $id = (int) $row['id_remember_token'];
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
        salsabox_clear_remember_cookie();
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['tag'] = (string) $user['gameTag'];
    $_SESSION['id_usuario'] = (int) $user['id_usuario'];
    $_SESSION['admin'] = ((int) $user['admin']) === 1;

    // Rotación del token (evita reutilización si se filtra).
    $stmt = $conexion->prepare("DELETE FROM Remember_Token WHERE id_remember_token = ? LIMIT 1");
    if ($stmt) {
        $id = (int) $row['id_remember_token'];
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    salsabox_issue_remember_token($conexion, $userId);

    return true;
}

