<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['eliminar_id'])) {
        $id_borrar = $_POST['eliminar_id'];
        $sql = "DELETE FROM Amigos WHERE (id_usuario = ? AND id_amigo = ?) OR (id_usuario = ? AND id_amigo = ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiii", $id_usuario, $id_borrar, $id_borrar, $id_usuario);
        $stmt->execute();
    } 
    elseif (isset($_POST['aceptar_id'])) {
        $id_aceptar = $_POST['aceptar_id'];
        $sql = "UPDATE Amigos SET estado = 'aceptada' WHERE id_usuario = ? AND id_amigo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $id_aceptar, $id_usuario);
        $stmt->execute();
    }
    header("Location: mis_amigos.php");
    exit();
}

$sql_pendientes = "SELECT u.id_usuario, u.gameTag, u.avatar 
                   FROM Usuario u 
                   JOIN Amigos a ON u.id_usuario = a.id_usuario 
                   WHERE a.id_amigo = ? AND a.estado = 'pendiente'";
$query_p = $conexion->prepare($sql_pendientes);
$query_p->bind_param("i", $id_usuario);
$query_p->execute();
$res_pendientes = $query_p->get_result();

$sql_amigos = "SELECT u.id_usuario, u.gameTag, u.avatar, u.biografia 
               FROM Usuario u 
               JOIN Amigos a ON (u.id_usuario = a.id_usuario OR u.id_usuario = a.id_amigo)
               WHERE ((a.id_usuario = ? OR a.id_amigo = ?) AND a.estado = 'aceptada')
               AND u.id_usuario <> ? 
               GROUP BY u.id_usuario";
$query_a = $conexion->prepare($sql_amigos);
$query_a->bind_param("iii", $id_usuario, $id_usuario, $id_usuario);
$query_a->execute();
$res_amigos = $query_a->get_result();
$total_amigos = $res_amigos->num_rows;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Mis Amigos</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">
    <style>
        body { background-color: #14181c; color: white; font-family: 'Segoe UI', sans-serif; margin: 0; }
        .container-lista { width: 90%; max-width: 800px; margin: 40px auto; padding: 20px; }
        .section-title { color: #e0be00; border-bottom: 1px solid #2c3440; padding-bottom: 10px; margin-top: 30px; font-size: 1.2rem; text-transform: uppercase; letter-spacing: 1px; }
        .item-card { display: flex; justify-content: space-between; align-items: center; background: #1b2129; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #2c3440; transition: 0.3s; }
        .item-card:hover { border-color: #e0be00; transform: translateY(-2px); }
        .card-pendiente { border-left: 4px solid #e0be00; background: #1f252e; }
        .info-perfil { display: flex; align-items: center; text-decoration: none; color: inherit; flex-grow: 1; overflow: hidden; }
        .avatar { width: 55px; height: 55px; border-radius: 50%; object-fit: cover; border: 2px solid #e0be00; margin-right: 15px; background: #2c3440; flex-shrink: 0; }
        .tag-name { margin: 0; font-size: 1.1rem; color: #fff; }
        .bio-text { margin: 5px 0 0; color: #9ab3bc; font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 400px; }
        .botones { display: flex; gap: 10px; margin-left: 10px; }
        .btn { padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; border: none; transition: 0.3s; text-decoration: none; font-size: 0.85rem; white-space: nowrap; }
        .btn-aceptar { background: #e0be00; color: #000; }
        .btn-aceptar:hover { background: #fff; }
        .btn-eliminar { background: rgba(255, 68, 68, 0.1); color: #ff4444; border: 1px solid #ff4444; }
        .btn-eliminar:hover { background: #ff4444; color: white; }
        .empty-msg { color: #9ab3bc; font-style: italic; padding: 20px 0; text-align: center; }
        .back-link { color: #e0be00; text-decoration: none; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container-lista">
    <a href="perfilSesion.php" class="back-link">← Volver a mi Perfil</a>

    <?php if ($res_pendientes->num_rows > 0): ?>
        <h2 class="section-title">Solicitudes Pendientes</h2>
        <?php while ($sol = $res_pendientes->fetch_assoc()): ?>
            <?php 
                $avatar_sol = trim($sol['avatar'] ?? '');
                $img_sol = (empty($avatar_sol)) ? "../../../media/perfil_default.jpg" : "../../../media/" . $avatar_sol;
            ?>
            <div class="item-card card-pendiente">
                <div class="info-perfil">
                    <img src="<?php echo htmlspecialchars($img_sol); ?>" class="avatar">
                    <div>
                        <h3 class="tag-name"><?php echo htmlspecialchars($sol['gameTag']); ?></h3>
                        <p style="margin:0; font-size: 0.75rem; color: #e0be00;">Te ha enviado una solicitud</p>
                    </div>
                </div>
                <form method="POST" class="botones">
                    <input type="hidden" name="aceptar_id" value="<?php echo $sol['id_usuario']; ?>">
                    <button type="submit" class="btn btn-aceptar">Aceptar</button>
                    <button type="submit" name="eliminar_id" value="<?php echo $sol['id_usuario']; ?>" class="btn btn-eliminar">Rechazar</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <h2 class="section-title">Mis Amigos (<?php echo $total_amigos; ?>)</h2>
    
    <?php if ($total_amigos > 0): ?>
        <?php while ($row = $res_amigos->fetch_assoc()): ?>
            <?php 
                $avatar_amigo = trim($row['avatar'] ?? '');
                $img_amigo = (empty($avatar_amigo)) ? "../../../media/perfil_default.jpg" : "../../../media/" . $avatar_amigo;
            ?>
            <div class="item-card">
                <a href="../amistades/perfilOtros.php?id=<?php echo $row['id_usuario']; ?>" class="info-perfil">
                    <img src="<?php echo htmlspecialchars($img_amigo); ?>" class="avatar">
                    <div>
                        <h3 class="tag-name"><?php echo htmlspecialchars($row['gameTag']); ?></h3>
                        <p class="bio-text">
                            <?php echo htmlspecialchars(mb_strlen($row['biografia'] ?? '') > 60 ? mb_substr($row['biografia'], 0, 60)."..." : ($row['biografia'] ?: "Jugador de SalsaBox")); ?>
                        </p>
                    </div>
                </a>
                
                <form method="POST" onsubmit="return confirm('¿Eliminar a <?php echo addslashes($row['gameTag']); ?>?');">
                    <input type="hidden" name="eliminar_id" value="<?php echo $row['id_usuario']; ?>">
                    <button type="submit" class="btn btn-eliminar">Eliminar</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-msg">Aún no tienes amigos aceptados. ¡Explora la comunidad!</p>
    <?php endif; ?>
    
</div>

</body>
</html>