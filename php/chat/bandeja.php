<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) { header('Location: ../sesiones/login/login.php'); exit; }
$id_yo = (int)$_SESSION['id_usuario'];

/** * CONSULTA CORREGIDA: 
 * Mantenemos tu GROUP BY pero aseguramos que el id_conversacion 
 * sea el que pertenece a TÚ y TU AMIGO exclusivamente.
 */
$sqlContactos = "
    SELECT 
        u.id_usuario, 
        u.gameTag, 
        u.avatar,
        (SELECT cp1.id_conversacion 
         FROM chat_participante cp1
         INNER JOIN chat_participante cp2 ON cp1.id_conversacion = cp2.id_conversacion
         INNER JOIN chat_conversacion c ON cp1.id_conversacion = c.id_conversacion
         WHERE cp1.id_usuario = $id_yo 
           AND cp2.id_usuario = u.id_usuario 
           AND c.tipo = 'individual' 
         LIMIT 1) as id_conversacion
    FROM amigos a
    JOIN usuario u ON (a.id_usuario = u.id_usuario OR a.id_amigo = u.id_usuario)
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
    <style>
        .chat-toolbar { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #333; background: #1a1a1a; }
        .btn-tool { background: none; border: none; cursor: pointer; color: #f0c330; display: flex; align-items: center; text-decoration: none; font-size: 14px; gap: 5px; }
        .btn-tool:hover { color: #fff; }
        /* Estilo para el texto de Visto */
        #estado-visto { padding: 5px 20px; font-size: 11px; color: #888; text-align: right; min-height: 20px; }
    </style>
</head>
<body>
    <div class="contenedor-chat">
        <div class="lista-conversaciones">
            <div class="chat-toolbar">
                <a href="../../index.php" class="btn-tool" title="Volver al Inicio">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </a>
                <span style="color:#eee; font-weight:bold; font-size:12px;">MENSAJES</span>
                <button onclick="alert('Funcionalidad de grupo en desarrollo')" class="btn-tool" title="Crear Grupo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </button>
            </div>

            <div id="lista-items">
                <?php while($c = mysqli_fetch_assoc($resContactos)): ?>
                    <div class="chat-item" onclick="seleccionarContacto(<?php echo $c['id_usuario']; ?>, <?php echo $c['id_conversacion'] ?: 'null'; ?>, this)">
                        <img src="../../img/avatares/<?php echo $c['avatar'] ?: 'default.png'; ?>" 
                             onclick="event.stopPropagation(); window.location.href='../user/perfiles/perfilPublico.php?id=<?php echo $c['id_usuario']; ?>'"
                             alt="Avatar" style="width:40px; height:40px; border-radius:50%; cursor:pointer;">
                        <div class="chat-info">
                            <h4><?php echo htmlspecialchars($c['gameTag']); ?></h4>
                            <p><?php echo $c['id_conversacion'] ? 'Chat abierto' : 'Nuevo mensaje'; ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="ventana-chat">
            <div id="mensajes-scroll">
                <p style="text-align:center; color:#666; margin-top:100px;">Selecciona un amigo para chatear</p>
            </div>
            
            <form class="input-area" id="form-mensaje" style="display:none;">
                <input type="hidden" id="id_conversacion_activa" value="">
                <input type="text" id="input-texto" placeholder="Escribe un mensaje..." autocomplete="off">
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>

    <script src="../../js/chat.js"></script>
</body>
</html>