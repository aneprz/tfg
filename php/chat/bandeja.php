<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) { header('Location: ../sesiones/login/login.php'); exit; }
$id_yo = (int)$_SESSION['id_usuario'];


$sqlContactos = "
    (SELECT 
        c.id_conversacion,
        'grupal' as tipo,
        c.nombre_grupo as nombre,
        'grupo_default.png' as avatar,
        NULL as id_receptor
    FROM chat_participante cp
    JOIN chat_conversacion c ON cp.id_conversacion = c.id_conversacion
    WHERE cp.id_usuario = $id_yo AND c.tipo = 'grupal')
    
    UNION
    
    (SELECT 
        c.id_conversacion,
        'individual' as tipo,
        u.gameTag as nombre,
        u.avatar as avatar,
        u.id_usuario as id_receptor
    FROM chat_participante cp1
    JOIN chat_conversacion c ON cp1.id_conversacion = c.id_conversacion
    JOIN chat_participante cp2 ON (cp2.id_conversacion = c.id_conversacion AND cp2.id_usuario != $id_yo)
    JOIN usuario u ON cp2.id_usuario = u.id_usuario
    WHERE cp1.id_usuario = $id_yo AND c.tipo = 'individual')
    
    ORDER BY id_conversacion DESC";

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
                <button onclick="abrirModalGrupo()" class="btn-tool" title="Crear Grupo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </button>
            </div>

            <div id="lista-items">
                <?php 
                if ($resContactos && mysqli_num_rows($resContactos) > 0):
                    while($c = mysqli_fetch_assoc($resContactos)): 
                        // Validamos los datos para evitar errores de impresión
                        $idConv = isset($c['id_conversacion']) ? (int)$c['id_conversacion'] : 0;
                        $tipoChat = isset($c['tipo']) ? $c['tipo'] : 'individual';
                        $nombreChat = isset($c['nombre']) ? htmlspecialchars($c['nombre']) : 'Usuario';
                        $avatarChat = !empty($c['avatar']) ? $c['avatar'] : 'default.png';
                        $idReceptor = !empty($c['id_receptor']) ? (int)$c['id_receptor'] : 'null';
                ?>
                    <div class="chat-item" onclick="seleccionarContacto(<?php echo $idReceptor; ?>, <?php echo $idConv; ?>, this)">
                        <img src="../../img/avatares/<?php echo ($tipoChat == 'grupal') ? 'grupo_default.png' : $avatarChat; ?>" 
                            style="width:45px; height:45px; border-radius:50%; object-fit: cover;">
                        <div class="chat-info">
                            <h4 style="color: <?php echo ($tipoChat == 'grupal') ? '#f0c330' : '#fff'; ?>;">
                                <?php echo $nombreChat; ?>
                            </h4>
                            <p style="font-size:11px; color:#888;">
                                <?php echo ($tipoChat == 'grupal') ? 'Grupo' : 'Chat Privado'; ?>
                            </p>
                        </div>
                    </div>
                <?php 
                    endwhile; 
                else: ?>
                    <p style="text-align:center; color:#555; margin-top:20px;">No tienes chats activos.</p>
                <?php endif; ?>
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
        <div id="modal-grupo" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
        <div style="background:#1a1a1a; padding:30px; border-radius:15px; width:100%; max-width:500px; border:1px solid #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            
            <h3 style="color:#f0c330; margin-bottom:20px; text-align:center; font-size:20px; letter-spacing:0.5px;">Crear Nuevo Grupo</h3>
            
            <div style="margin-bottom:20px;">
                <input type="text" id="nombre-grupo" placeholder="Nombre del grupo (ej: Salsa Lovers)..." style="width:100%; padding:12px; background:#0e0e0e; border:1px solid #333; color:white; border-radius:8px; font-size:14px;">
            </div>
            
            <p style="font-size:12px; color:#888; margin-bottom:12px;">Selecciona a los miembros (tus amigos):</p>
            
            <div id="lista-amigos-grupo" style="max-height:280px; overflow-y:auto; margin-bottom:25px; background:#0e0e0e; border:1px solid #333; border-radius:8px; padding:5px;">
                <?php 
                // Re-ejecutamos la consulta de amigos para asegurarnos que traiga la info
                $sqlAmigos = "SELECT u.id_usuario, u.gameTag, u.avatar FROM amigos a JOIN usuario u ON (a.id_usuario = u.id_usuario OR a.id_amigo = u.id_usuario) WHERE (a.id_usuario = $id_yo OR a.id_amigo = $id_yo) AND u.id_usuario != $id_yo AND a.estado = 'aceptada'";
                $resAmigos = mysqli_query($conexion, $sqlAmigos);
                
                if ($resAmigos && mysqli_num_rows($resAmigos) > 0):
                    while($amigo = mysqli_fetch_assoc($resAmigos)): ?>
                        <label style="display:flex; align-items:center; gap:15px; padding:12px; border-bottom:1px solid #222; cursor:pointer; color:white; transition: 0.2s; -webkit-user-select: none; user-select: none;">
                            <input type="checkbox" class="check-amigo" value="<?php echo $amigo['id_usuario']; ?>" style="width:18px; height:18px; cursor:pointer; accent-color: #f0c330;">
                            
                            <img src="../../img/avatares/<?php echo $amigo['avatar'] ?: 'default.png'; ?>" 
                                style="width:30px; height:30px; border-radius:50%; object-fit: cover; border: 1px solid #333;">
                            
                            <span style="flex:1; font-size:14px;"><?php echo htmlspecialchars($amigo['gameTag']); ?></span>
                        </label>
                    <?php endwhile; 
                else: ?>
                    <p style="text-align:center; color:#555; padding:20px; font-size:13px;">No tienes amigos disponibles para invitar.</p>
                <?php endif; ?>
            </div>

            <div style="display:flex; gap:15px;">
                <button onclick="cerrarModalGrupo()" style="flex:1; padding:12px; background:#333; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold; transition: 0.2s;">
                    Cancelar
                </button>
                <button onclick="crearGrupoProcesar()" style="flex:1; padding:12px; background:#f0c330; color:black; border:none; border-radius:8px; cursor:pointer; font-weight:bold; transition: 0.2s;">
                    Crear Grupo
                </button>
            </div>
        </div>
    </div>

    <script src="../../js/chat.js"></script>
</body>
</html>