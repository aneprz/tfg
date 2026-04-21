<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../../index.php");
    exit();
}

$id = (int)($_GET['id'] ?? 0);

$stmt = $conexion->prepare("SELECT * FROM Tienda_Items WHERE id_item = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if (!$item) {
    header("Location: listaEditarItem.php");
    exit();
}

$admin = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SalsaBox - Editar Item</title>
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

<div class="central"><h1>Editar Item</h1></div>

<div class="admin-container">
<form action="procesarEditarItem.php" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?php echo $id; ?>">

    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?php echo htmlspecialchars($item['nombre']); ?>" required>

    <label>Descripción:</label>
    <textarea name="descripcion"><?php echo htmlspecialchars($item['descripcion']); ?></textarea>

    <label>Tipo:</label>
    <select name="tipo" required>
        <?php
        $tipos = ['avatar', 'marco', 'fondo'];
        foreach ($tipos as $t) {
            $selected = ($t === $item['tipo']) ? 'selected' : '';
            echo "<option value='$t' $selected>" . ucfirst($t) . "</option>";
        }
        ?>
    </select>

    <label>Precio:</label>
    <input type="number" name="precio" value="<?php echo $item['precio']; ?>" required>

    <label>Rareza:</label>
    <select name="rareza">
        <?php
        $rareza = ['comun', 'raro', 'epico', 'legendario'];
        foreach ($rareza as $r) {
            $selected = ($r === $item['rareza']) ? 'selected' : '';
            echo "<option value='$r' $selected>" . ucfirst($r) . "</option>";
        }
        ?>
    </select>

    <label>Activo:</label>
    <select name="activo">
        <option value="1" <?php if ($item['activo']) echo 'selected'; ?>>Sí</option>
        <option value="0" <?php if (!$item['activo']) echo 'selected'; ?>>No</option>
    </select>

    <label>Imagen actual:</label><br>
    <img src="../../../../../media/<?php echo htmlspecialchars($item['imagen']); ?>" width="100"><br>

    <label>Cambiar imagen:</label>
    <input type="file" name="imagen" accept="image/*">

    <button type="submit">Guardar Cambios</button>

</form>
</div>
<script>
    (function() {
        var btnVolver = document.createElement('button');
        btnVolver.innerHTML = '← Volver';
        btnVolver.id = 'btnVolverMovil';
        btnVolver.style.cssText = 'display:none; position:fixed; bottom:20px; left:20px; background:#e0be00; color:#000; border:none; padding:12px 20px; border-radius:50px; font-weight:bold; cursor:pointer; z-index:9999; box-shadow:0 2px 10px rgba(0,0,0,0.3);';
        document.body.appendChild(btnVolver);
        btnVolver.onclick = function() {
            window.location.href = '../gestionTienda.php';
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