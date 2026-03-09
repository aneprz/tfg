<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    $admin = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Añadir Logro</title>
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
    <div class="central">
        <h1>Añadir Logro</h1>
    </div>
    <div class="admin-container">
        <form action="procesarAñadirLogro.php" method="POST" enctype="multipart/form-data">
            <label for="nombre">Título:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="descripcion">Descripcion:</label>
            <input type="text" id="descripcion" name="descripcion" required>

            <label for="puntos">Puntos:</label>
            <input type="number" id="puntos" name="puntos" required>

            <button type="submit">Añadir Logro</button>
        </form>
    </div>
    </div>
</body>
</html>