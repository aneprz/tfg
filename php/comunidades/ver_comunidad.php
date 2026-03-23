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
                <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../logros/logros.php" class="activo">Logros</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="user-zone" style="display: flex; align-items: center; gap: 20px;">
            <div class="notif-wrapper" style="position: relative;">
                <svg id="bell-icon" viewBox="0 0 24 24" style="width:24px; cursor:pointer; fill:#ffcc00;"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                <div id="notif-badge" style="display:none; position:absolute; top:-5px; right:-8px; background:red; color:white; border-radius:50%; padding:2px 5px; font-size:10px;">0</div>
                <div id="notif-dropdown" style="display:none; position:absolute; right:0; top:40px; background:#1a1a1a; border:1px solid #333; width:280px; z-index:999; border-radius:8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                    <div class="notif-header" style="padding:10px; border-bottom:1px solid #333; color:#ffcc00; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:13px; font-weight:bold;">Notificaciones</span>
                        <button onclick="marcarLeidas()" style="background:none; border:none; color:#888; cursor:pointer; font-size:11px;">Limpiar</button>
                    </div>
                    <ul id="notif-list" style="list-style:none; margin:0; padding:0; max-height:250px; overflow-y:auto;"></ul>
                </div>
            </div>

            <?php if(isset($_SESSION['tag'])) : ?>
                <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
            <?php endif; ?>
        </div>

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
                <form action="guardar_post.php" method="POST">
                    <input type="hidden" name="id_comunidad" value="<?php echo $id_comunidad; ?>">
                    <textarea name="contenido" placeholder="Escribe un mensaje..." required></textarea>
                    <div class="input-acciones">
                        <button type="submit" class="botonCrearCuenta">Enviar</button>
                    </div>
                </form>
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
                            $ruta_perfil = '../user/perfiles/perfilOtros.php?id=' . $id_miembro; 
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

    <script src="../../js/comunidades.js"></script>
    <script src="../../js/social.js"></script>
</body>
</html>