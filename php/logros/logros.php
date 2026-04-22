<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

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
    <title>SalsaBox - Logros</title>
    <link rel="stylesheet" href="../../estilos/estilos_logros.css">
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
                <li><a href="logros.php" class="activo">Logros</a></li>
                <li><a href="../ranking/ranking.php">Ranking</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <button class="menu-toggle" aria-label="Menú">☰</button>
        <?php if (!isset($_SESSION['tag'])): ?>

        <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">
            Iniciar sesión
        </a>

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
            <a class="tag" href="../../php/user/perfiles/perfilSesion.php">
                <?php echo htmlspecialchars($_SESSION['tag']); ?>
            </a>
        </div>
    <?php endif; ?>

        <script src="../../js/notificaciones.js"></script>
    </header>

    <div class="central">
        <h1>Logros</h1>
        <p>Aquí podrás ver los logros que podrás ir ganando a lo largo de tu experiencia como gamer.</p><br>
        
        <div class="buscadorContainer">
            <input type="text" id="input-busqueda" placeholder="Buscar por nombre de logro o videojuego..." aria-label="Buscar logro">
        </div>
        <div class="filtros-logros">
            <select id="filtroLogros">

                <option value="mas">Más logros</option>
                <option value="menos">Menos logros</option>

                <option value="nombre_asc">Nombre A → Z</option>
                <option value="nombre_desc">Nombre Z → A</option>

                <option value="nota_desc">Mejor puntuados</option>
                <option value="nota_asc">Peor puntuados</option>

                <option value="fecha_desc">Más recientes</option>
                <option value="fecha_asc">Más antiguos</option>

            </select>
        </div>
    </div>

    <main>
        <h2>Galería de Retos</h2>
        <div class="logros-grid" id="contenedor-logros">
            </div>
        <p id="sinResultados" class="sinResultados" hidden style="color: white; text-align: center; margin-top: 20px;">
            No se encontraron logros que coincidan con tu búsqueda.
        </p>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

    <script>

        document.addEventListener('DOMContentLoaded', () => {

            const inputBusqueda = document.getElementById('input-busqueda');
            const contenedor = document.getElementById('contenedor-logros');
            const sinResultados = document.getElementById('sinResultados');
            const filtro = document.getElementById('filtroLogros');

            let pagina = 1;

            const fetchLogros = () => {

                const texto = inputBusqueda.value;
                const tipoFiltro = filtro.value;

                fetch(`procesarLogros.php?buscar=${encodeURIComponent(texto)}&filtro=${tipoFiltro}&pagina=${pagina}&ajax=true`)
                    .then(response => response.text())
                    .then(html => {

                        contenedor.innerHTML = html;

                        if (html.includes('no-results') || html.trim() === "") {
                            sinResultados.hidden = false;
                        } else {
                            sinResultados.hidden = true;
                        }

                        document.querySelectorAll('.pag-btn').forEach(btn => {

                            btn.addEventListener('click', () => {

                                pagina = btn.dataset.pagina;

                                fetchLogros();

                                window.scrollTo({
                                    top: 0,
                                    behavior: 'smooth'
                                });

                            });

                        });

                    });

            };

            inputBusqueda.addEventListener('input', () => {
                pagina = 1;
                fetchLogros();
            });

            filtro.addEventListener('change', () => {
                pagina = 1;
                fetchLogros();
            });

            fetchLogros();

        });

    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('nav');
        if (menuToggle) {
            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                nav.classList.toggle('open');
            });
        }
    });
</script>
</body>
</html>