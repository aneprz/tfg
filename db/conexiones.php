<?php
$conexion = mysqli_connect("localhost","root","","salsabox_db");

// Auto-login con "Recordarme" (si existe cookie y no hay sesión iniciada).
if (
    PHP_SAPI !== 'cli'
    && $conexion instanceof mysqli
    && isset($_COOKIE['salsabox_remember'])
    && (!isset($_SESSION) || empty($_SESSION['id_usuario']))
) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        if (!headers_sent()) {
            session_start();
        }
    }

    if (session_status() === PHP_SESSION_ACTIVE && empty($_SESSION['id_usuario'])) {
        require_once __DIR__ . '/../php/sesiones/remember_me.php';
        salsabox_try_remember_login($conexion);
    }
}
?>
