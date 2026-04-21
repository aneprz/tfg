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
    <title>SalsaBox - Administracion Videojuegos</title>
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
    <h1>Gestion de Videojuegos</h1>
    <p>Este es el apartado de administración de juegos de la página web.</p>
    </div>
    <div class="admin-container">
    <h2>Gestión de Videojuegos</h2>
    <div class="admin-grid">
        <a href="añadirVideojuego/añadirVideojuego.php" class="admin-card">
            <div class="card-icon">➕🎮</div>
            <span>Añadir Videojuego</span>
        </a>
        
        <a href="eliminarVideojuego/eliminarVideojuego.php" class="admin-card">
            <div class="card-icon">🗑️🎮</div>
            <span>Eliminar Videojuego</span>
        </a>
        
        <a href="editarVideojuego/listaEditarVideojuego.php" class="admin-card">
            <div class="card-icon">✏️🎮</div>
            <span>Editar Videojuego</span>
        </a>
    </div>
</div>

<script>
    (function() {
        var btnVolver = document.createElement('button');
        btnVolver.innerHTML = '← Volver';
        btnVolver.id = 'btnVolverMovil';
        btnVolver.style.cssText = 'display:none; position:fixed; bottom:20px; left:20px; background:#e0be00; color:#000; border:none; padding:12px 20px; border-radius:50px; font-weight:bold; cursor:pointer; z-index:9999; box-shadow:0 2px 10px rgba(0,0,0,0.3);';
        document.body.appendChild(btnVolver);
        btnVolver.onclick = function() {
            window.location.href = '../../indexAdmin.php';
        };
        function checkWidth() {
            btnVolver.style.display = window.innerWidth <= 768 ? 'block' : 'none';
        }
        window.addEventListener('resize', checkWidth);
        checkWidth();
    })();
</script>

</body>
</html>