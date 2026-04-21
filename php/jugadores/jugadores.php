<?php
session_start();
require 'procesarJugadores.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Comunidad de Jugadores</title>
    <link rel="stylesheet" href="../../estilos/estilos_juegos.css">
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_jugadores.css">
    <link rel="icon" href="../../media/logoPlatino.png">
    <style>
        .user-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="jugadores.php" class="activo">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../tienda/tienda.php">Tienda</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <li><a href="../ranking/ranking.php">Ranking</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <button class="menu-toggle" aria-label="Menú">☰</button>
        
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <div class="user-actions">
                <!-- CHATS -->
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
                <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
            </div>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Explora la comunidad</h1>
        <p>Encuentra a otros gamers, revisa sus perfiles y descubre nuevas amistades dentro de SalsaBox.</p><br>
        
        <div class="buscadorContainer">
            <input type="text" id="input-busqueda" placeholder="Buscar por GameTag..." aria-label="Buscar jugador">
        </div>
    </div>

    <main>
        <h2>Todos los jugadores</h2>
        <div class="user-grid" id="contenedor-resultados">
            </div>
        <p id="sinResultados" class="sinResultados" hidden>No se encontraron jugadores para esa búsqueda.</p>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputBusqueda = document.getElementById('input-busqueda');
        const contenedor = document.getElementById('contenedor-resultados');
        const sinResultados = document.getElementById('sinResultados');

        const fetchJugadores = () => {
            const texto = inputBusqueda.value;
            fetch(`procesarJugadores.php?buscar=${encodeURIComponent(texto)}&ajax=true`)
                .then(response => response.text())
                .then(html => {
                    contenedor.innerHTML = html;
                    if (html.includes('no-results') || html.trim() === "") {
                        sinResultados.hidden = false;
                    } else {
                        sinResultados.hidden = true;
                    }
                })
                .catch(error => console.error('Error:', error));
        };

        inputBusqueda.addEventListener('input', fetchJugadores);
        fetchJugadores(); 
    });
    </script>
    <script>
        // Menú hamburguesa
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const nav = document.querySelector('nav');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    nav.classList.toggle('open');
                });
            }
        });
    </script>
    <script src="../../js/notificaciones.js"></script>
</body>
</html>