<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_comunidad = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_comunidad <= 0) { header("Location: comunidades.php"); exit; }

$sqlComunidad = "SELECT c.*, v.titulo AS juego_nombre, v.portada 
                 FROM Comunidad c 
                 LEFT JOIN Videojuego v ON c.id_videojuego_principal = v.id_videojuego 
                 WHERE c.id_comunidad = $id_comunidad";
$resCom = mysqli_query($conexion, $sqlComunidad);
$comunidad = mysqli_fetch_assoc($resCom);

$esMiembro = false;
if (isset($_SESSION['id_usuario'])) {
    $id_user = $_SESSION['id_usuario'];
    $check = mysqli_query($conexion, "SELECT 1 FROM miembro_comunidad WHERE id_comunidad = $id_comunidad AND id_usuario = $id_user");
    if (mysqli_num_rows($check) > 0) $esMiembro = true;
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
        </nav>
        <div class="user-zone">
            <?php if(isset($_SESSION['tag'])) : ?>
                <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
            <?php endif; ?>
        </div>
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
            <div class="banner-top" style="background-image: linear-gradient(to bottom, transparent, #14181c), url('../../<?php echo $comunidad['portada']; ?>')">
                <h1><?php echo htmlspecialchars($comunidad['nombre']); ?></h1>
            </div>

            <section class="feed" id="chat-feed">
                <?php
                $sqlPosts = "SELECT p.*, u.gameTag FROM post p 
                             JOIN usuario u ON p.id_usuario = u.id_usuario 
                             WHERE p.id_comunidad = $id_comunidad 
                             ORDER BY p.fecha_publicacion ASC";
                $resPosts = mysqli_query($conexion, $sqlPosts);
                while ($post = mysqli_fetch_assoc($resPosts)): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <span class="post-autor">@<?php echo htmlspecialchars($post['gameTag']); ?></span>
                            <span class="post-fecha"><?php echo date('H:i', strtotime($post['fecha_publicacion'])); ?></span>
                        </div>
                        <div class="post-contenido"><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></div>
                    </div>
                <?php endwhile; ?>
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
                <img src="../../<?php echo $comunidad['portada']; ?>" width="100%" style="border-radius:8px">
                <h4>Juego vinculado</h4>
                <p><strong><?php echo $comunidad['juego_nombre']; ?></strong></p>
                
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
                </ul>
        </div>
    </div>

    <script src="../../js/comunidades.js"></script>
    <script src="../../js/social.js"></script>
</body>
</html>