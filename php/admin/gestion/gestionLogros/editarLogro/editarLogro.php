<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    
    $id = (int)($_GET['id'] ?? 0);
    $logro = $conexion->query("SELECT * FROM logros WHERE id_logro = $id")->fetch_assoc();
    if (!$logro) { header("Location: listaEditarLogro.php"); exit(); }
    $admin = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Editar Logros</title>
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
                <li><a href="../gestionLogros.php">Volver al panel de gestión</a></li>
            </ul>
        </nav>
        <?php if(isset($_SESSION['tag'])) : ?>
            <a class="tag" href="../../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central"><h1>Editar Logros</h1></div>
    <div class="admin-container">
        <form action="procesarEditarLogro.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <label>Título:</label> <input type="text" name="nombre" value="<?php echo htmlspecialchars($logro['nombre_logro']); ?>" required>
            <label>Descripción:</label> <textarea name="descripcion" required><?php echo htmlspecialchars($logro['descripcion']); ?></textarea>
            <label>Puntos:</label> <input type="text" name="puntos" value="<?php echo htmlspecialchars($logro['puntos_logro']); ?>" required>
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>