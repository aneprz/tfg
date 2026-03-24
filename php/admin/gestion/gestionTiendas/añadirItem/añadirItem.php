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
    <title>SalsaBox - Añadir Item</title>

    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
</head>

<body>

<header>
    <div class="tituloWeb">
        <img src="../../../../../media/logoPlatino.png" width="40px">
        <a href="../../../../../index.php" class="logo">Salsa<span>Box</span></a>
    </div>

    <nav>
        <ul>
            <li><a href="../gestionTienda.php">Volver al panel de gestión</a></li>
        </ul>
    </nav>

    <?php if(isset($_SESSION['tag'])) : ?>
        <a class="tag" href="../../../../user/perfiles/perfilSesion.php">
            <?php echo htmlspecialchars($_SESSION['tag']); ?>
        </a>
    <?php endif; ?>
</header>

<div class="central">
    <h1>Añadir Item a la Tienda</h1>
</div>

<div class="admin-container">

<form action="procesarAñadirItem.php" method="POST" enctype="multipart/form-data">

    <label>Nombre:</label>
    <input type="text" name="nombre" required>

    <label>Descripción:</label>
    <textarea name="descripcion"></textarea>

    <label>Tipo:</label>
    <select name="tipo" required>
        <option value="avatar">Avatar</option>
        <option value="marco">Marco</option>
        <option value="fondo">Fondo</option>
    </select>

    <label>Precio:</label>
    <input type="number" name="precio" min="0" required>

    <label>Rareza:</label>
    <select name="rareza">
        <option value="comun">Común</option>
        <option value="raro">Raro</option>
        <option value="epico">Épico</option>
        <option value="legendario">Legendario</option>
    </select>

    <label>Activo:</label>
    <select name="activo">
        <option value="1">Sí</option>
        <option value="0">No</option>
    </select>

    <label>Imagen:</label>
    <input type="file" name="imagen" accept="image/*" required>

    <button type="submit">Añadir Item</button>

</form>

</div>

</body>
</html>