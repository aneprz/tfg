<?php
require_once __DIR__ . '/../../db/conexiones.php';

$id_com = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nombre_canal = isset($_GET['canal']) ? $_GET['canal'] : 'todos';

if ($id_com > 0) {
    if ($nombre_canal === 'todos') {
        $sql = "SELECT p.*, u.gameTag FROM post p 
                JOIN Usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.id_comunidad = $id_com 
                ORDER BY p.fecha_publicacion ASC";
    } else {
        // Filtrar por canal usando la etiqueta en el contenido
        $sql = "SELECT p.*, u.gameTag FROM post p 
                JOIN Usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.id_comunidad = $id_com 
                AND p.contenido LIKE '[CANAL:$nombre_canal]%'
                ORDER BY p.fecha_publicacion ASC";
    }

    $res = mysqli_query($conexion, $sql);

    if ($res && mysqli_num_rows($res) > 0) {
        while ($post = mysqli_fetch_assoc($res)):
            // Limpiar la etiqueta del contenido para mostrar
            $contenido_mostrar = preg_replace('/^\[CANAL:[^\]]+\] /', '', $post['contenido']);
            ?>
            <div class="post-card">
                <div class="post-header">
                    <span class="post-autor">@<?php echo htmlspecialchars($post['gameTag']); ?></span>
                    <span class="post-fecha"><?php echo date('H:i', strtotime($post['fecha_publicacion'])); ?></span>
                </div>
                <div class="post-contenido">
                    <p><?php echo nl2br(htmlspecialchars($contenido_mostrar)); ?></p>
                </div>
            </div>
        <?php endwhile;
    } else {
        echo '<p style="text-align:center; color:#5c6370; padding:20px;">No hay mensajes en este canal.</p>';
    }
}
?>