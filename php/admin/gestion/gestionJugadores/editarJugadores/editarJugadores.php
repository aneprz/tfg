<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    
    $id = (int)($_GET['id'] ?? 0);
    $usuario = $conexion->query("SELECT * FROM Usuario WHERE id_usuario = $id")->fetch_assoc();
    
    if (!$usuario) { header("Location: listaEditarJugadores.php"); exit(); }
    $admin = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Editar Jugador</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../../../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../../../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../gestionJugadores.php">Volver al panel de gestión</a></li>
            </ul>
        </nav>
        <?php if(isset($_SESSION['tag'])) : ?>
            <a class="tag" href="../../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central"><h1>Editar Perfil de <?php echo htmlspecialchars($usuario['gameTag']); ?></h1></div>
    <div class="admin-container">
        <form action="procesarEditarJugadores.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            
            <label>GameTag:</label> 
            <input type="text" name="gameTag" value="<?php echo htmlspecialchars($usuario['gameTag']); ?>" required>
            
            <label>Nombre y Apellidos:</label> 
            <input type="text" name="nombre_apellido" value="<?php echo htmlspecialchars($usuario['nombre_apellido']); ?>">
            
            <label>Email:</label> 
            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            
            <label>Biografía:</label> 
            <textarea name="biografia" rows="4"><?php echo htmlspecialchars($usuario['biografia']); ?></textarea>
            
            <label>Avatar actual:</label><br>
            <?php 
                $rutaAvatar = !empty($usuario['avatar']) ? $usuario['avatar'] : '../../../../../media/perfil_default.png';
            ?>
            <img src="../../../../../media/<?php echo htmlspecialchars($rutaAvatar); ?>" width="100" style="border-radius: 50%; margin: 10px 0;"><br>
            
            <label>Cambiar Avatar:</label>
            <input type="file" name="avatar" accept="image/*">
            
            <button type="submit" style="margin-top: 20px;">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>