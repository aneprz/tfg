<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_objetivo = $_GET['id'];

// Consultamos el nombre del usuario dueño del perfil
$queryUser = $conexion->prepare("SELECT gameTag FROM Usuario WHERE id_usuario = ?");
$queryUser->bind_param("i", $id_objetivo);
$queryUser->execute();
$usuario = $queryUser->get_result()->fetch_assoc();

if (!$usuario) { die("Usuario no encontrado."); }

// Función para procesar el avatar (Soporta URL y ruta local)
function obtenerRutaAvatar($avatar_raw) {
    $avatar_raw = trim($avatar_raw ?? '');
    if (empty($avatar_raw)) {
        return "../../../media/perfil_default.jpg";
    }
    // Si es una URL externa (HTTP/HTTPS)
    if (filter_var($avatar_raw, FILTER_VALIDATE_URL) || strpos($avatar_raw, 'http') === 0) {
        return $avatar_raw;
    }
    // Si es ruta local, subimos 3 niveles
    $avatar_limpio = ltrim($avatar_raw, '/');
    return (strpos($avatar_limpio, 'media/') === 0) 
        ? "../../../" . $avatar_limpio 
        : "../../../media/" . $avatar_limpio;
}

$sql = "SELECT u.id_usuario, u.gameTag, u.avatar 
        FROM Usuario u 
        WHERE u.id_usuario IN (
            SELECT id_amigo FROM Amigos WHERE id_usuario = ? AND estado = 'aceptada'
            UNION
            SELECT id_usuario FROM Amigos WHERE id_amigo = ? AND estado = 'aceptada'
        ) AND u.id_usuario != ?";

$query = $conexion->prepare($sql);
$query->bind_param("iii", $id_objetivo, $id_objetivo, $id_objetivo);
$query->execute();
$resultado = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Amigos de <?php echo htmlspecialchars($usuario['gameTag']); ?> - SalsaBox</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">
    <style>
        /* Ajuste para asegurar que las imágenes se vean bien en la lista */
        .item-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e0be00;
        }
        .amigo-info-principal {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
        }
        .item-card {
            background: #1b2129;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #2c3440;
            transition: 0.3s;
        }
        .item-card:hover {
            border-color: #e0be00;
        }
        .btn-volver {
            color: #e0be00;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-lista">
        <a href="perfilOtros.php?id=<?php echo $id_objetivo; ?>" class="btn-volver">← Volver al Perfil</a>
        
        <h1 style="color: white; margin-bottom: 30px;">
            Amigos de <span style="color: #e0be00;"><?php echo htmlspecialchars($usuario['gameTag']); ?></span>
        </h1>

        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($row = $resultado->fetch_assoc()): ?>
                <div class="item-card">
                    <a href="perfilOtros.php?id=<?php echo $row['id_usuario']; ?>" class="amigo-info-principal" style="text-decoration: none;">
                        <img src="<?php echo obtenerRutaAvatar($row['avatar']); ?>" class="item-img" alt="Avatar">
                        <div class="item-content">
                            <h3 class="item-title" style="margin: 0; color: white;"><?php echo htmlspecialchars($row['gameTag']); ?></h3>
                            <p style="margin: 5px 0 0; color: #9ab3bc; font-size: 0.9rem;">Jugador de SalsaBox</p>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="empty-msg" style="color: #9ab3bc; text-align: center; margin-top: 50px;">
                Este usuario no tiene amigos aún.
            </p>
        <?php endif; ?>
    </div>
</body>
</html>