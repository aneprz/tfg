<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $id_amigo_borrar = $_POST['eliminar_id'];

    $sql_delete = "DELETE FROM Amigos WHERE (id_usuario = ? AND id_amigo = ?) OR (id_usuario = ? AND id_amigo = ?)";
    $stmt_delete = $conexion->prepare($sql_delete);
    $stmt_delete->bind_param("iiii", $id_usuario, $id_amigo_borrar, $id_amigo_borrar, $id_usuario);
    $stmt_delete->execute();
    
    header("Location: mis_amigos.php");
    exit();
}

$sql = "SELECT u.id_usuario, u.gameTag, u.avatar, u.biografia 
        FROM Usuario u 
        WHERE u.id_usuario IN (
            SELECT id_amigo FROM Amigos WHERE id_usuario = ?
            UNION
            SELECT id_usuario FROM Amigos WHERE id_amigo = ?
        ) AND u.id_usuario != ?";

$query = $conexion->prepare($sql);
$query->bind_param("iii", $id_usuario, $id_usuario, $id_usuario);
$query->execute();
$resultado = $query->get_result();
$total_amigos = $resultado->num_rows;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Amigos - SalsaBox</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">

    
</head>
<body>
    <div class="container-lista" style="width: 100%; max-width: 900px; margin: 0 auto; padding: 20px;">
        <a href="perfilSesion.php" class="btn-volver" style="color: #9ab3bc; text-decoration: none;">← Volver al Perfil</a>
        
        <h1 style="color: #e0be00; border-bottom: 2px solid #e0be00; padding-bottom: 10px;">
            Mis Amigos (<?php echo $total_amigos; ?>)
        </h1>

        <?php 
        if ($total_amigos > 0) {
            while ($row = $resultado->fetch_assoc()) {
                $avatar_raw = $row['avatar'];
                $ruta_avatar = (!empty($avatar_raw)) ? "../../../" . $avatar_raw : "../../../media/defaultAvatar.png";
                $bio = (!empty($row['biografia'])) ? $row['biografia'] : "Este gamer prefiere mantener el misterio.";
                ?>
                
                <div class="item-card">
                    <div class="amigo-info-principal">
                        <img src="<?php echo htmlspecialchars($ruta_avatar); ?>" 
                             alt="Avatar" 
                             style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-right: 20px; border: 2px solid #e0be00;">
                        
                        <div class="item-content">
                            <h3 class="item-title" style="margin: 0; color: #e0be00;">
                                <?php echo htmlspecialchars($row['gameTag']); ?>
                            </h3>
                            <p class="item-desc" style="margin: 5px 0 0; color: #9ab3bc; font-size: 0.9rem;">
                                <?php 
                                    $bio_corta = (strlen($bio) > 100) ? substr($bio, 0, 100) . "..." : $bio;
                                    echo htmlspecialchars($bio_corta); 
                                ?>
                            </p>
                        </div>
                    </div>

                    <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar a <?php echo $row['gameTag']; ?> de tu lista?');">
                        <input type="hidden" name="eliminar_id" value="<?php echo $row['id_usuario']; ?>">
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                </div>

                <?php
            }
        } else {
            echo "<p style='color: white;'>No tienes amigos aún. Tu ID es: $id_usuario</p>";
        }
        ?>
    </div>
</body>
</html>