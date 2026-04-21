<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}
$admin = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Administracion</title>
    <link rel="stylesheet" href="../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="icon" href="../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../tienda/tienda.php">Tienda</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <li><a href="../ranking/ranking.php">Ranking</a></li>
                <?php if ($admin): ?>
                    <li><a href="indexAdmin.php" class="activo">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>  

        <button class="menu-toggle" aria-label="Menú">☰</button>
        
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <div class="user-actions">
                <div class="chat-wrapper" style="margin-right: 10px; display: inline-block; vertical-align: middle;">
                    <a href="../chat/bandeja.php" id="chat-icon" style="color: inherit; text-decoration: none; position: relative; display: flex; align-items: center;">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="26" height="26">
                            <path d="M12 2C6.477 2 2 6.14 2 11.25c0 2.457 1.047 4.675 2.75 6.275L4 21l3.75-1.5c1.33.4 2.76.625 4.25.625 5.523 0 10-4.14 10-9.25S17.523 2 12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span id="chat-badge" style="
                            position: absolute;
                            top: -5px;
                            right: -5px;
                            background-color: #ff4444;
                            color: white;
                            font-size: 10px;
                            font-weight: bold;
                            padding: 2px 5px;
                            border-radius: 10px;
                            display: none;
                        ">0</span>
                    </a>
                </div>
                <div class="notif-wrapper">
                    <div id="bell-icon">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 22C13.1 22 14 21.1 14 20H10C10 21.1 10.9 22 12 22ZM18 16V11C18 7.93 16.37 5.36 13.5 4.68V4C13.5 3.17 12.83 2.5 12 2.5C11.17 2.5 10.5 3.17 10.5 4V4.68C7.64 5.36 6 7.92 6 11V16L4 18V19H20V18L18 16Z" fill="currentColor"/>
                        </svg>
                        <span id="notif-badge">0</span>
                    </div>

                    <div id="notif-dropdown">
                        <div class="notif-header">
                            <span>Notificaciones</span>
                            <button onclick="marcarLeidas()">Limpiar</button>
                        </div>
                        <ul id="notif-list"></ul>
                    </div>
                </div>
                <a class="tag" href="../../php/user/perfiles/perfilSesion.php">
                    <?php echo htmlspecialchars($_SESSION['tag']); ?>
                </a>
            </div>
        <?php endif; ?>

        <script src="../../js/notificaciones.js"></script>
    </header>

    <div class="central">
        <h1>Admin</h1>
        <p>Este es el apartado de administración de la página web.</p>
    </div>

    <div class="admin-container">
        <h2>Gestión de contenido</h2>
        <div class="admin-grid">
            <a href="gestion/gestionJuegos/gestionJuegos.php" class="admin-card">
                <div class="card-icon">🎮</div>
                <span>Gestionar Videojuegos</span>
            </a>
            <a href="gestion/gestionJugadores/gestionJugadores.php" class="admin-card">
                <div class="card-icon">👤</div>
                <span>Gestionar Jugadores</span>
            </a>
            <a href="gestion/gestionComunidades/gestionComunidades.php" class="admin-card">
                <div class="card-icon">🏘️</div>
                <span>Gestionar Comunidades</span>
            </a>
            <a href="gestion/gestionLogros/gestionLogros.php" class="admin-card">
                <div class="card-icon">🏆</div>
                <span>Gestionar Logros</span>
            </a>
            <a href="gestion/gestionTiendas/gestionTienda.php" class="admin-card">
                <div class="card-icon">🛒</div>
                <span>Gestionar Tienda</span>
            </a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('nav');
        
        if (menuToggle) {
            // Abrir/cerrar al hacer clic en el botón
            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                nav.classList.toggle('open');
            });
        }
        
        // Cerrar el menú al hacer clic en un enlace del nav
        const navLinks = document.querySelectorAll('nav ul li a');
        navLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                nav.classList.remove('open');
            });
        });
        
        // Cerrar el menú al hacer clic fuera de él
        document.addEventListener('click', function(event) {
            if (nav && menuToggle && !nav.contains(event.target) && !menuToggle.contains(event.target)) {
                nav.classList.remove('open');
            }
        });
    });
</script>
</body>
</html>