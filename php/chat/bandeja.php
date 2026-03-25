<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) { header('Location: ../sesiones/login/login.php'); exit; }
$id_yo = (int)$_SESSION['id_usuario'];

/**
 * CONSULTA: 
 * Trae a los usuarios que son amigos del usuario actual.
 * También intentamos obtener el id_conversacion si ya existe una entre ambos.
 */
$sqlContactos = "
    SELECT 
        u.id_usuario, 
        u.gameTag, 
        u.avatar,
        MAX(c.id_conversacion) as id_conversacion
    FROM amigos a
    JOIN usuario u ON (a.id_usuario = u.id_usuario OR a.id_amigo = u.id_usuario)
    LEFT JOIN chat_participante cp1 ON cp1.id_usuario = $id_yo
    LEFT JOIN chat_participante cp2 ON cp2.id_usuario = u.id_usuario AND cp2.id_conversacion = cp1.id_conversacion
    LEFT JOIN chat_conversacion c ON c.id_conversacion = cp1.id_conversacion AND c.tipo = 'individual'
    WHERE (a.id_usuario = $id_yo OR a.id_amigo = $id_yo)
    AND u.id_usuario != $id_yo
    AND a.estado = 'aceptada'
    GROUP BY u.id_usuario, u.gameTag, u.avatar";

$resContactos = mysqli_query($conexion, $sqlContactos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SalsaBox - Mensajes</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_chat.css">
</head>
<body>
    <div class="contenedor-chat">
        <div class="lista-conversaciones">
            <div class="seccion-titulo">Mensajes Directos</div>
            <div id="lista-items">
                <?php while($contacto = mysqli_fetch_assoc($resContactos)): ?>
                    <div class="chat-item" 
                        onclick="seleccionarContacto(<?php echo $contacto['id_usuario']; ?>, <?php echo $contacto['id_conversacion'] ?: 'null'; ?>, this)">
                        
                        <img src="../../img/avatares/<?php echo $contacto['avatar'] ?: 'default.png'; ?>" 
                            style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        
                        <div class="chat-info">
                            <h4><?php echo htmlspecialchars($contacto['gameTag']); ?></h4>
                            <p><?php echo $contacto['id_conversacion'] ? 'Chat activo' : 'Sin mensajes'; ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="ventana-chat">
            <div id="mensajes-scroll">
                <p style="text-align:center; color:#666; margin-top:100px;">Selecciona una conversación</p>
            </div>
            
            <form class="input-area" id="form-mensaje" style="display:none;">
                <input type="hidden" id="id_conversacion_activa">
                <input type="text" id="input-texto" placeholder="Escribe un mensaje..." autocomplete="off">
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>

    <script src="../../js/chat.js"></script>
</body>
</html>