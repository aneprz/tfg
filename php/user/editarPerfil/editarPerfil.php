<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.php");
    exit();
}

$id = $_SESSION['id_usuario'];

$stmt = $conexion->prepare("SELECT gameTag, nombre_apellido, biografia, avatar FROM Usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$user = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../../../estilos/estilos_editarPerfil.css">
    <link rel="icon" href="../../../media/logoplatino.png">
</head>
<body>
    
    <form action="procesar_editar.php" method="POST">
        <div>
            <label>Game Tag:</label><br>
            <input type="text" name="gameTag" value="<?php echo htmlspecialchars($user['gameTag']); ?>" required>
        </div>
        <div>
            <label>Nombre y Apellidos:</label><br>
            <input type="text" name="nombreApellido" value="<?php echo htmlspecialchars($user['nombre_apellido']); ?>" required>
        </div>

        <br>

        <div>
            <label>Biografía:</label><br>
            <textarea name="biografia" rows="4" cols="30" maxlength="296"><?php echo htmlspecialchars($user['biografia']); ?></textarea>
        </div>

        <br>

        <div>
            <label>URL de la foto de perfil:</label><br>
            <input type="text" name="avatar" value="<?php echo htmlspecialchars($user['avatar']); ?>">
        </div>

        <br>

        <button type="submit">Guardar Cambios</button>
        <a href="../perfiles/perfilSesion.php">Cancelar</a>
    </form>
</body>
</html>