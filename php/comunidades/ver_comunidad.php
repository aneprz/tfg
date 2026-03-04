<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($conexion)) {
    die("Error de conexión.");
}

$id_comunidad = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_comunidad <= 0) {
    header("Location: comunidades.php");
    exit;
}

$sqlComunidad = "SELECT c.*, v.titulo AS juego_nombre, v.portada 
                 FROM comunidad c 
                 JOIN videojuego v ON c.id_videojuego_principal = v.id_videojuego 
                 WHERE c.id_comunidad = $id_comunidad";
$resCom = mysqli_query($conexion, $sqlComunidad);
$comunidad = mysqli_fetch_assoc($resCom);

if (!$comunidad) {
    die("Comunidad no encontrada.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($comunidad['nombre']); ?> - SalsaBox</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_comunidad_interna.css">
    <link rel="icon" href="../../media/logoPlatino.png">
</head>
<body class="body-comunidad">
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="comunidades.php" class="activo">Comunidades</a></li>
            </ul>
        </nav>
        <div class="user-zone">
            <?php if(isset($_SESSION['tag'])) : ?>
                <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
            <?php else: ?>
                <a href="../sesiones/login/login.php" class="botonCrearCuenta">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="layout-comunidad">
        <aside class="sidebar-canales">
            <div class="header-sidebar">
                <h3># Canales</h3>
            </div>
            <nav class="lista-canales">
                <a href="#" class="canal activo"><span>#</span> general</a>
                <a href="#" class="canal"><span>#</span> clips-y-capturas</a>
                <div style="height: 1px; background: #2c3440; margin: 15px 0;"></div>
                <p style="font-size: 0.7rem; color: #5c6370; margin-bottom: 10px; font-weight: bold;">VOZ</p>
                <a href="#" class="canal"><span>🔊</span> Lobby Principal</a>
            </nav>
        </aside>

        <main class="muro-comunidad">
            <div class="banner-top" style="background-image: linear-gradient(to bottom, transparent, #14181c), url('../../<?php echo htmlspecialchars($comunidad['portada']); ?>')">
                <h1><?php echo htmlspecialchars($comunidad['nombre']); ?></h1>
            </div>

            <div class="input-post">
                <form action="guardar_post.php" method="POST">
                    <input type="hidden" name="id_comunidad" value="<?php echo $id_comunidad; ?>">
                    <textarea name="contenido" placeholder="Escribe un mensaje en #general..." required></textarea>
                    <div class="input-acciones">
                        <button type="button" class="btn-subir-media">📁 Adjuntar</button>
                        <button type="submit" class="botonCrearCuenta">Publicar</button>
                    </div>
                </form>
            </div>

            <section class="feed">
                <?php
                $sqlPosts = "SELECT p.*, u.gameTag 
                             FROM post p 
                             JOIN usuario u ON p.id_usuario = u.id_usuario 
                             WHERE p.id_comunidad = $id_comunidad 
                             ORDER BY p.fecha_publicacion DESC";
                
                $resPosts = mysqli_query($conexion, $sqlPosts);

                if ($resPosts && mysqli_num_rows($resPosts) > 0): 
                    while ($post = mysqli_fetch_assoc($resPosts)): ?>
                        <article class="post-card">
                            <div class="post-header">
                                <span class="post-autor">@<?php echo htmlspecialchars($post['gameTag']); ?></span>
                                <span class="post-fecha"><?php echo date('d M, H:i', strtotime($post['fecha_publicacion'])); ?></span>
                            </div>
                            <div class="post-contenido">
                                <p><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></p>
                            </div>
                        </article>
                    <?php endwhile; 
                else: ?>
                    <div style="text-align: center; padding: 40px; color: #5c6370;">
                        <p>No hay mensajes en esta comunidad. ¡Sé el primero!</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>

        <aside class="sidebar-info">
            <div class="info-juego-card">
                <img src="../../<?php echo htmlspecialchars($comunidad['portada']); ?>" alt="Portada">
                <h4>Juego vinculado</h4>
                <p><strong><?php echo htmlspecialchars($comunidad['juego_nombre']); ?></strong></p>
                <button class="botonUnirse" style="width: 100%; margin-top: 20px;">Unirse al grupo</button>
            </div>
        </aside>
    </div>
</body>
</html>