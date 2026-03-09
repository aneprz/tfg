<?php
session_start();
require_once __DIR__ . '/../../../../db/conexiones.php';

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
    <title>SalsaBox - Administracion Logros</title>
    <link rel="stylesheet" href="../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../media/logoPlatino.png">
</head>
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../indexAdmin.php">Volver al panel de administración</a></li>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <a class="tag" href="../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central">
    <h1>Gestion de Logros</h1>
    <p>Este es el apartado de administración de logros de la página web.</p>
    </div>
    <div class="admin-container">
    <h2>Gestión de contenido</h2>
    <div class="admin-grid">
        <a href="añadirLogro/añadirLogro.php" class="admin-card">
            <div class="card-icon">➕🏆</div>
            <span>Añadir logros</span>
        </a>
        <a href="eliminarLogro/eliminarLogro.php" class="admin-card">
            <div class="card-icon">🗑️🏆</div>
            <span>Eliminar logros</span>
        </a>
        <a href="editarLogro/editarLogro.php" class="admin-card">
            <div class="card-icon">✏️🏆</div>
            <span>Editar logros</span>
        </a>
        <a href="asignarLogro/asignarLogro.php" class="admin-card">
            <div class="card-icon">👤🏆</div>
            <span>Asignacion de logros</span>
        </a>
    </div>
</div>

</body>
</html>