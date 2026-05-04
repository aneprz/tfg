<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$comunidades = [];
$id_usuario_sesion = $_SESSION['id_usuario'] ?? 0;

if (isset($conexion) && $conexion) {
   $sqlComunidades = "
        SELECT 
            c.id_comunidad, 
            c.nombre, 
            c.banner_url,
            v.portada AS portada_juego,
            v.titulo AS juego_nombre,
            (SELECT COUNT(*) FROM Miembro_Comunidad WHERE id_comunidad = c.id_comunidad) AS total_miembros,
            (SELECT COUNT(*) FROM Miembro_Comunidad WHERE id_comunidad = c.id_comunidad AND id_usuario = $id_usuario_sesion) AS ya_es_miembro
        FROM Comunidad c
        LEFT JOIN Videojuego v ON c.id_videojuego_principal = v.id_videojuego
        GROUP BY c.id_comunidad, c.nombre, v.portada, v.titulo
        ORDER BY total_miembros DESC, c.nombre ASC";

    $res = mysqli_query($conexion, $sqlComunidades);
    if ($res) {
        while ($fila = mysqli_fetch_assoc($res)) {
            $comunidades[] = $fila;
        }
    }
}
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidades - SalsaBox</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_comunidades.css">
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
                <li><a href="../../php/jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="comunidades.php" class="activo">Comunidades</a></li>
                <li><a href="../tienda/tienda.php">Tienda</a></li>
                <li><a href="../cajas/cajas.php">Cajas</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
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
        <h1>Comunidades</h1> 
        <a href="agregar_comunidad.php" class="btn-agregar">Añadir Comunidad</a>
        <br>
        <br>
        <p>Únete a grupos de tus juegos favoritos, comparte clips y conoce a otros jugadores.</p>
    </div>

    <main>
        <h2>Explorar Grupos</h2>
        <div class="grid-comunidades">
            <?php if (count($comunidades) > 0): ?>
                <?php foreach ($comunidades as $com): ?>
                    <article class="card-comunidad">
                        <div class="banner-comunidad" style="background-image: url('../../media/<?php echo !empty($com['banner_url']) ? htmlspecialchars($com['banner_url']) : htmlspecialchars($com['portada_juego']); ?>')">
                            <div class="overlay-miembros">
                                ● <?php echo number_format($com['total_miembros'], 0, ',', '.'); ?> miembros
                            </div>
                        </div>
                        <div class="cuerpo-comunidad">
                            <span class="juego-tag"><?php echo htmlspecialchars($com['juego_nombre']); ?></span>
                            <h3><?php echo htmlspecialchars($com['nombre']); ?></h3>
                            
                            <div class="acciones-com">
                                <a href="ver_comunidad.php?id=<?php echo $com['id_comunidad']; ?>" class="btn-entrar">Ver Muro</a>
                                
                                <?php if (!isset($_SESSION['id_usuario'])): ?>
                                    <a href="../sesiones/login/login.php" class="botonUnirse">Unirse</a>
                                <?php else: ?>
                                    <button 
                                        type="button"
                                        class="botonUnirse btn-accion-comunidad" 
                                        data-id="<?php echo $com['id_comunidad']; ?>" 
                                        data-accion="<?php echo ($com['ya_es_miembro'] > 0) ? 'salir' : 'unirse'; ?>"
                                        style="font-weight: bold; border-radius: 8px; padding: 8px 15px; cursor: pointer; <?php echo ($com['ya_es_miembro'] > 0) 
                                            ? 'background-color: #ff4d4d; color: #000; border: 2px solid #cc0000;' 
                                            : 'background-color: #f1c40f; color: #000; border: 2px solid #f1c40f;'; ?>">
                                        <?php echo ($com['ya_es_miembro'] > 0) ? 'Abandonar' : 'Unirse'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="aviso-vacio">No se encontraron comunidades creadas en la base de datos.</p>
            <?php endif; ?>
        </div>
    </main>

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

    <script src="../../js/comunidades.js"></script>
</body>
</html>