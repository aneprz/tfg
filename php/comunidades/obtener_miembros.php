<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_comunidad = (int)$_GET['id'];
$miId = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;

$sql = "SELECT u.id_usuario, u.gameTag, u.avatar, a.estado, a.id_usuario AS solicitante
        FROM miembro_comunidad mc 
        JOIN usuario u ON mc.id_usuario = u.id_usuario 
        LEFT JOIN amigos a ON (
            (a.id_usuario = $miId AND a.id_amigo = u.id_usuario) OR 
            (a.id_usuario = u.id_usuario AND a.id_amigo = $miId)
        )
        WHERE mc.id_comunidad = $id_comunidad";

$res = mysqli_query($conexion, $sql);

while($m = mysqli_fetch_assoc($res)): 
    if ($m['id_usuario'] == $miId) continue; 

    $avatarPath = $m['avatar'] ? "../../" . $m['avatar'] : "../../media/usuarios/default.png";
?>
    <li style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="<?php echo $avatarPath; ?>" style="width:30px; height:30px; border-radius:50%; object-fit: cover;">
            <span style="color: #fff;">@<?php echo htmlspecialchars($m['gameTag']); ?></span>
        </div>
        <div>
            <?php if ($m['estado'] === 'aceptada'): ?>
                <span class="badge-amigo" style="color: #28a745; font-weight: bold;">Amigos ✓</span>
            
            <?php elseif ($m['estado'] === 'pendiente'): ?>
                <?php if ($m['solicitante'] == $miId): ?>
                    <button class="btn-pendiente" disabled style="background: #6c757d; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: not-allowed;">Pendiente</button>
                <?php else: ?>
                    <span style="color: #ffc107; font-size: 0.85em;">Te envió solicitud</span>
                <?php endif; ?>

            <?php else: ?>
                <button class="btn-agregar" data-id="<?php echo $m['id_usuario']; ?>" style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Agregar</button>
            <?php endif; ?>
        </div>
    </li>
<?php endwhile; ?>