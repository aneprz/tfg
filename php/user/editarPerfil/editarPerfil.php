<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login/login.php");
    exit();
}

$id = $_SESSION['id_usuario'];

// 1. Sacamos los datos básicos del usuario
$stmt = $conexion->prepare("SELECT gameTag, nombre_apellido, biografia, avatar FROM Usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$user = $resultado->fetch_assoc();

// 2. Sacamos su inventario de avatares exclusivos
$stmtAvatares = $conexion->prepare("
    SELECT t.nombre, t.imagen 
    FROM usuario_items ui 
    JOIN tienda_items t ON ui.id_item = t.id_item 
    WHERE ui.id_usuario = ? AND t.tipo = 'avatar'
");
$stmtAvatares->bind_param("i", $id);
$stmtAvatares->execute();
$avataresDesbloqueados = $stmtAvatares->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../../../estilos/estilos_editarPerfil.css">
    <link rel="icon" href="../../../media/logoplatino.png">
    <style>
        .grid-avatares { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 10px; }
        .avatar-label { cursor: pointer; text-align: center; width: 80px; }
        .avatar-radio { display: none; }
        .avatar-img { 
            width: 70px; height: 70px; border-radius: 10px; 
            border: 3px solid transparent; background: #222; 
            object-fit: contain; padding: 5px; transition: 0.2s;
        }
        /* Magia CSS: Si el radio oculto está marcado, el hermano (la imagen) se pone dorada */
        .avatar-radio:checked + .avatar-img { border-color: #f0c330; background: #333; transform: scale(1.05); }
        .avatar-nombre { font-size: 0.75rem; color: #aaa; display: block; margin-top: 5px; line-height: 1.2; }
    </style>
</head>
<body>
    
    <form action="procesar_editar.php" method="POST" enctype="multipart/form-data">
        <div>
            <label>Game Tag:</label><br>
            <input type="text" name="gameTag" value="<?php echo htmlspecialchars($user['gameTag']); ?>" required>
        </div>
        <br>
        <div>
            <label>Nombre y Apellidos:</label><br>
            <input type="text" name="nombreApellido" value="<?php echo htmlspecialchars($user['nombre_apellido']); ?>" required>
        </div>

        <br>

        <div>
            <label>Biografía:</label><br>
            <textarea name="biografia" rows="4" cols="30" maxlength="296"><?php echo htmlspecialchars($user['biografia']); ?></textarea>
        </div>

        <br><hr style="border-color: #333;"><br>

        <div>
            <label style="font-size: 1.2rem; color: #f0c330; font-weight: bold;">Tus Avatares Exclusivos</label><br>
            <p style="font-size: 0.85rem; color: #888;">Elige un avatar que hayas ganado en las cajas de botín.</p>
            
            <?php if(count($avataresDesbloqueados) > 0): ?>
                <div class="grid-avatares">
                    <?php foreach($avataresDesbloqueados as $avatar): ?>
                        <label class="avatar-label">
                            <input type="radio" name="avatar_inventario" value="<?php echo htmlspecialchars($avatar['imagen']); ?>" class="avatar-radio">
                            <img src="../../../media/<?php echo htmlspecialchars($avatar['imagen']); ?>" class="avatar-img" alt="<?php echo htmlspecialchars($avatar['nombre']); ?>">
                            <span class="avatar-nombre"><?php echo htmlspecialchars($avatar['nombre']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="background: #1a1c23; padding: 15px; border-radius: 5px; border: 1px dashed #444; color: #888; display: inline-block;">
                    Aún no has desbloqueado ningún avatar. ¡Prueba suerte en las cajas!
                </div>
            <?php endif; ?>
        </div>

        <br><hr style="border-color: #333;"><br>

        <div>
            <label style="color: #fff;">O sube una foto desde tu PC:</label><br>
            <?php $fotoActual = !empty($user['avatar']) ? $user['avatar'] : '../../../media/perfil_default.jpg'; ?>
            <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                <img src="../../../media/<?php echo htmlspecialchars($fotoActual); ?>" width="60" style="border-radius: 50%; object-fit: cover; height: 60px;">
                <input type="file" name="avatar_archivo" accept="image/*">
            </div>
        </div>

        <br><br>

        <button type="submit" style="background: #f0c330; color: #000; font-weight: bold; padding: 10px 20px; border: none; cursor: pointer;">Guardar Cambios</button>
        <a href="../perfiles/perfilSesion.php" style="margin-left: 15px; color: #aaa;">Cancelar</a>
    </form>
</body>
</html>