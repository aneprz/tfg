<?php
    session_start();
    require_once __DIR__ . '/../../db/conexiones.php';
    $admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Comunidad - SalsaBox</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_comunidades.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="icon" href="../../media/logoPlatino.png">
    <style>
        .select2-container--default .select2-selection--single { background-color: var(--card-bg) !important; border: 1px solid #2c3440 !important; height: 48px !important; padding: 8px; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: white !important; line-height: 30px !important; }
        .select2-dropdown { background-color: var(--card-bg) !important; border: 1px solid var(--accent-color) !important; }
        .select2-search__field { background-color: #0d0f12 !important; color: white !important; }
        .select2-results__option--highlighted { background-color: var(--accent-color) !important; color: #000 !important; }
    </style>
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
                <li><a href="../../php/jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="comunidades.php" class="activo">Comunidades</a></li>
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
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesion</a>
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
    </header>

    <div class="central">
        <h1>Crear Nueva Comunidad</h1>
        <p>Establece un lugar de encuentro para los fans de tus juegos favoritos.</p>
    </div>

    <main>
        <form action="procesar_agregar_comunidad.php" method="POST" enctype="multipart/form-data" class="search-section" style="max-width: 600px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="nombre" style="color: var(--accent-color); font-weight: bold;">Nombre de la Comunidad</label>
                <input type="text" name="nombre" id="nombre" class="search-input" placeholder="Ej: Fans de Elden Ring" required style="width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="id_videojuego" style="color: var(--accent-color); font-weight: bold;">Videojuego Principal</label>
                <select name="id_videojuego" id="id_videojuego" class="select-buscable" required style="width: 100%;">
                    <option value="">Buscar videojuego...</option>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="banner" style="color: var(--accent-color); font-weight: bold;">Imagen de Banner</label>
                <input type="file" name="banner" id="banner" class="search-input" accept="image/*" required style="width: 100%; padding: 8px;">
            </div>

            <input type="hidden" name="id_creador" value="<?php echo $_SESSION['id_usuario']; ?>">

            <button type="submit" class="btn-agregar" style="border: none; cursor: pointer; align-self: center; width: 100%;">Crear Comunidad</button>
        </form>
    </main>

    <script>
    $(document).ready(function() {
        $('.select-buscable').select2({
            placeholder: "Buscar videojuego...",
            allowClear: true,
            ajax: {
                url: 'buscar_juegos.php',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data.results }; },
                cache: true
            },
            minimumInputLength: 2
        });
    });
    </script>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

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