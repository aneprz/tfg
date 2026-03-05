<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_comunidad = (int)$_GET['id'];
$miId = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 0;

// Consultamos los miembros (usando minúsculas en las tablas)
$sql = "SELECT u.id_usuario, u.gameTag, u.avatar 
        FROM miembro_comunidad mc 
        JOIN usuario u ON mc.id_usuario = u.id_usuario 
        WHERE mc.id_comunidad = $id_comunidad";

$res = mysqli_query($conexion, $sql);

while($m = mysqli_fetch_assoc($res)): 
    if ($m['id_usuario'] == $miId) continue; // No me muestro a mí mismo

    $idAmigo = $m['id_usuario'];
    // Verificamos si ya son amigos
    $checkAmigo = "SELECT 1 FROM amigos WHERE (id_usuario = $miId AND id_amigo = $idAmigo) OR (id_usuario = $idAmigo AND id_amigo = $miId)";
    $resCheck = mysqli_query($conexion, $checkAmigo);
    $yaSonAmigos = (mysqli_num_rows($resCheck) > 0);
?>
    <li>
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="../../<?php echo $m['avatar'] ?: 'media/usuarios/default.png'; ?>" style="width:30px; height:30px; border-radius:50%;">
            <span style="color: #fff;">@<?php echo htmlspecialchars($m['gameTag']); ?></span>
        </div>
        <div>
            <?php if ($yaSonAmigos): ?>
                <span class="badge-amigo">Amigos ✓</span>
            <?php else: ?>
                <button class="btn-agregar" data-id="<?php echo $idAmigo; ?>">Agregar</button>
            <?php endif; ?>
        </div>
    </li>
<?php endwhile; ?>