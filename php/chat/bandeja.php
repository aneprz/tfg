<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) { header('Location: ../sesiones/login/login.php'); exit; }
$id_yo = (int)$_SESSION['id_usuario'];

// Consulta optimizada para Grupos e Individuales
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
        .chat-item.activo { background: #222; border-left: 3px solid #f0c330; }
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
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </button>
            </div>

            <div id="lista-items">
                <?php if ($resContactos && mysqli_num_rows($resContactos) > 0):
                    while($c = mysqli_fetch_assoc($resContactos)): 
                        $idConv = (int)$c['id_conversacion'];
                        $tipoChat = $c['tipo'];
                        $nombreChat = htmlspecialchars($c['nombre']);
                        $avatarChat = !empty($c['avatar']) ? $c['avatar'] : 'default.png';
                        $idReceptor = !empty($c['id_receptor']) ? (int)$c['id_receptor'] : 'null';
                ?>
                    <div class="chat-item" onclick="seleccionarContacto(<?php echo $idReceptor; ?>, <?php echo $idConv; ?>, this)">
                        <img src="../../img/avatares/<?php echo ($tipoChat == 'grupal') ? 'grupo_default.png' : $avatarChat; ?>" style="width:45px; height:45px; border-radius:50%; object-fit: cover;">
                        <div class="chat-info">
                            <h4 style="color: <?php echo ($tipoChat == 'grupal') ? '#f0c330' : '#fff'; ?>;"><?php echo $nombreChat; ?></h4>
                            <p style="font-size:11px; color:#888;"><?php echo ($tipoChat == 'grupal') ? 'Grupo' : 'Chat Privado'; ?></p>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                    <p style="text-align:center; color:#555; margin-top:20px;">No tienes chats activos.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="ventana-chat">
            <div id="chat-header" style="display:none; align-items:center; padding:10px 20px; background:#1a1a1a; border-bottom:1px solid #333; gap:15px;">
                <div id="header-info" style="display:flex; align-items:center; gap:12px; cursor:pointer; flex:1;">
                    <img id="header-avatar" src="" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:1px solid #f0c330;">
                    <div>
                        <h4 id="header-nombre" style="margin:0; color:white; font-size:15px;"></h4>
                        <span id="header-estado" style="font-size:11px; color:#888;"></span>
                    </div>
                </div>
                <button id="btn-ajustes-grupo" style="display:none; background:none; border:none; color:#f0c330; cursor:pointer;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </button>
            </div>

            <div id="mensajes-scroll">
                <p style="text-align:center; color:#444; margin-top:150px;">Selecciona una conversación</p>
            </div>

            <div id="estado-visto"></div>

            <form id="form-mensaje" class="input-area" style="display:none; padding:15px; background:#1a1a1a;">
                <input type="hidden" id="id_conversacion_activa" value="">
                <input type="text" id="input-texto" placeholder="Escribe un mensaje..." autocomplete="off" style="flex:1; padding:10px; border-radius:5px; border:1px solid #333; background:#000; color:white;">
                <button type="submit" style="background:#f0c330; color:black; font-weight:bold; border:none; padding:10px 20px; border-radius:5px; margin-left:10px; cursor:pointer;">ENVIAR</button>
            </form>
        </div>
    </div>

    <div id="modal-grupo" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
        <div style="background:#1a1a1a; padding:30px; border-radius:15px; width:100%; max-width:500px; border:1px solid #333;">
            <h3 style="color:#f0c330; margin-bottom:20px; text-align:center;">Crear Nuevo Grupo</h3>
            <input type="text" id="nombre-grupo" placeholder="Nombre del grupo..." style="width:100%; padding:12px; background:#0e0e0e; border:1px solid #333; color:white; border-radius:8px; margin-bottom:15px;">
            <div id="lista-amigos-grupo" style="max-height:280px; overflow-y:auto; margin-bottom:25px; background:#0e0e0e; border:1px solid #333; border-radius:8px;">
                <?php 
                // Consulta corregida: Seleccionamos al usuario que NO es el usuario actual (id_yo)
                $sqlAmigos = "SELECT u.id_usuario, u.gameTag, u.avatar 
                            FROM amigos a 
                            JOIN usuario u ON (CASE 
                                                    WHEN a.id_usuario = $id_yo THEN a.id_amigo = u.id_usuario 
                                                    ELSE a.id_usuario = u.id_usuario 
                                                END)
                            WHERE (a.id_usuario = $id_yo OR a.id_amigo = $id_yo) 
                            AND u.id_usuario != $id_yo 
                            AND a.estado = 'aceptada'";

                $resAmigos = mysqli_query($conexion, $sqlAmigos);

                if ($resAmigos && mysqli_num_rows($resAmigos) > 0):
                    while($amigo = mysqli_fetch_assoc($resAmigos)): ?>
                        <label style="display:flex; align-items:center; gap:15px; padding:12px; border-bottom:1px solid #222; cursor:pointer; color:white; transition: background 0.2s;" onmouseover="this.style.background='#1a1a1a'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" class="check-amigo" value="<?php echo $amigo['id_usuario']; ?>" style="accent-color: #f0c330;">
                            
                            <?php 
                                // Verificamos si tiene avatar, si no, ponemos el de por defecto
                                $fotoPerfil = !empty($amigo['avatar']) ? "../../img/avatares/" . $amigo['avatar'] : "../../img/avatares/default.png"; 
                            ?>
                            <img src="<?php echo $fotoPerfil; ?>" style="width:35px; height:35px; border-radius:50%; object-fit: cover; border: 1px solid #333;">
                            
                            <span style="font-size: 14px; font-weight: 500;"><?php echo htmlspecialchars($amigo['gameTag']); ?></span>
                        </label>
                    <?php endwhile; 
                else: ?>
                    <div style="padding: 20px; text-align: center; color: #666; font-size: 13px;">
                        No tienes amigos conectados para añadir al grupo.
                    </div>
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:15px;">
                <button onclick="cerrarModalGrupo()" style="flex:1; padding:12px; background:#333; color:white; border:none; border-radius:8px; cursor:pointer;">Cancelar</button>
                <button onclick="crearGrupoProcesar()" style="flex:1; padding:12px; background:#f0c330; color:black; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">Crear Grupo</button>
            </div>
        </div>
    </div>

    <div id="modal-ajustes-grupo" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:10000; align-items:center; justify-content:center;">
        <div style="background:#1a1a1a; padding:25px; border-radius:15px; width:450px; border:1px solid #f0c330; max-height: 90vh; overflow-y: auto;">
            <h3 style="color:#f0c330; text-align:center; margin-top:0;">Ajustes de "Salsa Lovers"</h3>
            
            <form id="form-editar-grupo" enctype="multipart/form-data">
                <input type="hidden" id="ajuste_id_conv" name="id_conv">
                
                <div style="display:flex; align-items:center; gap:15px; margin-bottom:20px; background:#000; padding:10px; border-radius:10px;">
                    <input type="file" name="foto_grupo" id="input-foto-ajuste" style="display:none;">
                    <label for="input-foto-ajuste" style="cursor:pointer;">
                        <img id="preview-foto-ajuste" src="../../img/avatares/grupo_default.png" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #f0c330;">
                    </label>
                    <input type="text" id="edit-nombre-grupo" name="nuevo_nombre" style="flex:1; padding:8px; background:transparent; border:none; border-bottom:1px solid #333; color:white; font-size:16px;">
                </div>

                <h4 style="color:#888; font-size:12px; text-transform:uppercase;">Miembros actuales</h4>
                <div id="lista-gestion-miembros" style="margin-bottom:20px; background:#0e0e0e; border-radius:8px; padding:5px;">
                    </div>

                <h4 style="color:#888; font-size:12px; text-transform:uppercase;">Añadir nuevos</h4>
                <div id="lista-añadir-miembros" style="margin-bottom:20px; background:#0e0e0e; border-radius:8px; padding:5px; max-height:150px; overflow-y:auto;">
                    </div>

                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="cerrarModalAjustes()" style="flex:1; padding:12px; background:#333; color:white; border:none; border-radius:8px; cursor:pointer;">Cancelar</button>
                    <button type="submit" style="flex:1; padding:12px; background:#f0c330; color:black; font-weight:bold; border:none; border-radius:8px; cursor:pointer;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../js/chat.js"></script>
</body>
</html>