<?php
require_once __DIR__ . '/../../db/conexiones.php';

if (!$conexion) {
    die("Error de conexión al servidor de base de datos.");
}


$id_com = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_com > 0) {

    $sql = "SELECT p.*, u.gameTag FROM post p 
            JOIN Usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.id_comunidad = $id_com 
            ORDER BY p.fecha_publicacion ASC"; 

    $res = mysqli_query($conexion, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
        while ($post = mysqli_fetch_assoc($res)): ?>
            <div class="post-card">
                <div class="post-header">
                    <span class="post-autor">@<?php echo htmlspecialchars($post['gameTag']); ?></span>
                    <span class="post-fecha"><?php echo date('H:i', strtotime($post['fecha_publicacion'])); ?></span>
                </div>
                <div class="post-contenido">
                    <p><?php echo nl2br(htmlspecialchars($post['contenido'])); ?></p>
                </div>
            </div>
        <?php endwhile;
    } else {
        echo '<p style="text-align:center; color:#5c6370; padding:20px;">No hay mensajes en esta comunidad. ¡Sé el primero en escribir!</p>';
    }
}
?>