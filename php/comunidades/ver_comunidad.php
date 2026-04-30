<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_comunidad = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_comunidad <= 0) { 
    header("Location: comunidades.php"); 
    exit; 
}

$sqlComunidad = "SELECT c.*, v.titulo AS juego_nombre, v.portada AS foto_juego 
                 FROM Comunidad c 
                 LEFT JOIN Videojuego v ON c.id_videojuego_principal = v.id_videojuego 
                 WHERE c.id_comunidad = $id_comunidad";

$resCom = mysqli_query($conexion, $sqlComunidad);

if ($resCom && mysqli_num_rows($resCom) > 0) {
    $comunidad = mysqli_fetch_assoc($resCom);
} else {
    header("Location: comunidades.php?error=no_encontrada");
    exit;
}

$portada_db = $comunidad['foto_juego'] ?? '';
if (empty($portada_db)) {
    $ruta_portada = '../../media/default.png';
} elseif (strpos($portada_db, 'http') === 0) {
    $ruta_portada = $portada_db;
} else {
    $ruta_portada = '../../media/' . basename($portada_db);
}

$banner_db = $comunidad['banner_url'] ?? '';
if (empty($banner_db)) {
    $ruta_banner = $ruta_portada;
} elseif (strpos($banner_db, 'http') === 0) {
    $ruta_banner = $banner_db;
} else {
    $ruta_banner = '../../media/' . basename($banner_db);
}

$esMiembro = false;
if (isset($_SESSION['id_usuario'])) {
    $id_user = (int)$_SESSION['id_usuario'];
    $check = mysqli_query($conexion, "SELECT 1 FROM miembro_comunidad WHERE id_comunidad = $id_comunidad AND id_usuario = $id_user");
    if ($check && mysqli_num_rows($check) > 0) {
        $esMiembro = true;
    }
}
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($comunidad['nombre']); ?> - SalsaBox</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_comunidad_interna.css">
</head>
<body class="body-comunidad">
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" width="40px">
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
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="24" height="24">
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
                <a class="tag" href="../user/perfiles/perfilSesion.php">
                    <?php echo htmlspecialchars($_SESSION['tag']); ?>
                </a>
            </div>
        <?php endif; ?>

        <script src="../../js/notificaciones.js"></script>
    </header>

    <div class="layout-comunidad">
        <aside class="sidebar-canales">
            <h3># Canales</h3>
            <nav class="lista-canales">
                <a href="#" class="canal activo"><span>#</span> general</a>
                <a href="#" class="canal"><span>#</span> clips-y-capturas</a>
            </nav>
        </aside>

        <main class="muro-comunidad">
            <div class="banner-top" style="background-image: linear-gradient(to bottom, transparent, #14181c), url('<?php echo htmlspecialchars($ruta_banner); ?>')">
                <h1><?php echo htmlspecialchars($comunidad['nombre']); ?></h1>
            </div>

            <section class="feed" id="chat-feed">
                <?php
                $sqlPosts = "SELECT p.*, u.gameTag FROM Post p 
                             JOIN Usuario u ON p.id_usuario = u.id_usuario 
                             WHERE p.id_comunidad = $id_comunidad 
                             ORDER BY p.fecha_publicacion ASC";
                $resPosts = mysqli_query($conexion, $sqlPosts);
                if ($resPosts) {
                    while ($post = mysqli_fetch_assoc($resPosts)): ?>
                        <div class="post-card">
                            <div class="post-header">
                                <span class="post-autor">@<?php echo htmlspecialchars($post['gameTag']); ?></span>
                                <span class="post-fecha"><?php echo date('H:i', strtotime($post['fecha_publicacion'])); ?></span>
                            </div>
                            <div class="post-contenido"><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></div>
                        </div>
                    <?php endwhile; 
                } ?>
            </section>

            <div class="input-post">
    <?php if ($esMiembro): ?>
        <form action="guardar_post.php" method="POST">
            <input type="hidden" name="id_comunidad" value="<?php echo $id_comunidad; ?>">
            <textarea name="contenido" placeholder="Escribe un mensaje..." required></textarea>
            <div class="input-acciones">
                <button type="submit" class="botonCrearCuenta">Enviar</button>
            </div>
        </form>
    <?php else: ?>
        <div style="text-align: center; padding: 20px; background: #2c3440; border-radius: 8px;">
            <p style="color: #e0be00; margin-bottom: 10px;">🔒 No eres miembro de esta comunidad</p>
            <p style="color: #aaa; font-size: 0.9rem;">Únete para poder escribir mensajes y participar en la comunidad.</p>
            <a href="gestionar_miembro.php?accion=unirse&id_comunidad=<?php echo $id_comunidad; ?>" class="botonUnirse" style="display: inline-block; margin-top: 10px;">Unirse a la comunidad</a>
        </div>
    <?php endif; ?>
</div>
        </main>

        <aside class="sidebar-info">
            <div class="info-juego-card">
                <img src="<?php echo htmlspecialchars($ruta_portada); ?>" width="100%" style="border-radius:8px">
                <h4>Juego vinculado</h4>
                <p><strong><?php echo htmlspecialchars($comunidad['juego_nombre'] ?? 'Sin juego'); ?></strong></p>
                
                <div class="botones-comunidad">
                    <?php if ($esMiembro): ?>
                        <a href="gestionar_miembro.php?accion=salir&id_comunidad=<?php echo $id_comunidad; ?>" class="botonSalir">Abandonar</a>
                    <?php else: ?>
                        <a href="gestionar_miembro.php?accion=unirse&id_comunidad=<?php echo $id_comunidad; ?>" class="botonUnirse">Unirse</a>
                    <?php endif; ?>
                    
                    <button id="btnVerMiembros" class="btn-secundario">Ver Miembros</button>
                </div>
            </div>
        </aside>
    </div>

    <div id="modalMiembros" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Miembros de la comunidad</h3>
            <ul class="lista-miembros-modal">
                <?php
                $sqlMiembros = "SELECT u.id_usuario, u.gameTag FROM miembro_comunidad mc 
                                JOIN Usuario u ON mc.id_usuario = u.id_usuario 
                                WHERE mc.id_comunidad = $id_comunidad";
                
                $resMiembros = mysqli_query($conexion, $sqlMiembros);

                if (!$resMiembros) {
                    echo '<li style="color: red;">Error en BD: ' . mysqli_error($conexion) . '</li>';
                } elseif (mysqli_num_rows($resMiembros) > 0) {
                    $id_sesion_actual = $_SESSION['id_usuario'] ?? 0;

                    while ($miembro = mysqli_fetch_assoc($resMiembros)) {
                        $id_miembro = $miembro['id_usuario'];
                        $nombre_miembro = htmlspecialchars($miembro['gameTag']);

                        if ($id_miembro == $id_sesion_actual) {
                            $ruta_perfil = '../user/perfiles/perfilSesion.php'; 
                        } else {
                            $ruta_perfil = '../user/amistades/perfilOtros.php?id=' . $id_miembro; 
                        }

                        echo '<li><a href="' . $ruta_perfil . '" style="color: inherit; text-decoration: none; display: block; width: 100%;">' . $nombre_miembro . '</a></li>';
                    }
                } else {
                    echo '<li>Aún no hay miembros en esta comunidad.</li>';
                }
                ?>
            </ul>
        </div>
    </div>

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
    <script src="../../js/social.js"></script>
</body>
</html>