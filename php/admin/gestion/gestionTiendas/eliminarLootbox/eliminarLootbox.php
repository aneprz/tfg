<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../../../index.php");
    exit();
}

// Sacamos todas las lootboxes de la base de datos
$res = mysqli_query($conexion, "SELECT id_item, nombre, precio, imagen, color_neon FROM Tienda_Items WHERE tipo = 'lootbox'");
$lootboxes = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Eliminar Lootbox</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
    <style>
        .lista-cajas { display: flex; flex-direction: column; gap: 15px; max-width: 600px; margin: 0 auto; }
        .caja-fila { display: flex; align-items: center; justify-content: space-between; background: #222; padding: 15px; border-radius: 8px; border: 1px solid #444; transition: transform 0.2s; }
        .caja-fila:hover { transform: scale(1.02); }
        .caja-info { display: flex; align-items: center; gap: 15px; }
        .caja-img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; background: #111; padding: 5px; }
        .btn-eliminar { background: #ff4444; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        .btn-eliminar:hover { background: #cc0000; }
    </style>
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../../../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../../../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../gestionTienda.php">Volver al panel</a></li>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../../../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <a class="tag" href="../../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Eliminar Cajas de Evento</h1>
        <p>Selecciona la caja que deseas borrar. ¡Cuidado, esta acción no se puede deshacer!</p>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div style="color: #2ecc71; margin-bottom: 20px; font-weight: bold; background: rgba(46, 204, 113, 0.1); padding: 10px; border-radius: 5px;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: #ff4444; margin-bottom: 20px; font-weight: bold; background: rgba(255, 68, 68, 0.1); padding: 10px; border-radius: 5px;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
    </div>

    <div class="admin-container">
        <div class="lista-cajas">
            <?php if (count($lootboxes) === 0): ?>
                <p style="text-align: center; color: #888; font-style: italic;">No hay cajas de evento creadas actualmente.</p>
            <?php else: ?>
                <?php foreach ($lootboxes as $caja): ?>
                    <div class="caja-fila" style="border-left: 5px solid <?php echo $caja['color_neon'] ?? '#fff'; ?>;">
                        <div class="caja-info">
                            <img src="../../../../../media/<?php echo htmlspecialchars($caja['imagen']); ?>" class="caja-img" alt="Caja">
                            <div>
                                <strong style="color: #fff; font-size: 1.1rem;"><?php echo htmlspecialchars($caja['nombre']); ?></strong><br>
                                <span style="color: #f0c330; font-size: 0.9rem; font-weight: bold;"><?php echo $caja['precio']; ?> pts</span>
                            </div>
                        </div>
                        <form action="procesarEliminarLootbox.php" method="POST" onsubmit="return confirm('⚠️ ¿Estás COMPLETAMENTE SEGURO de que quieres borrar la caja <?php echo htmlspecialchars($caja['nombre']); ?>? Se borrará para siempre junto con sus premios invisibles asociados.');">
                            <input type="hidden" name="id_lootbox" value="<?php echo $caja['id_item']; ?>">
                            <button type="submit" class="btn-eliminar">🗑️ Borrar</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>