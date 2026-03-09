<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    
    $id = (int)($_GET['id'] ?? 0);
    $juego = $conexion->query("SELECT * FROM Videojuego WHERE id_videojuego = $id")->fetch_assoc();
    if (!$juego) { header("Location: listaEditarVideojuego.php"); exit(); }
    $admin = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Editar Videojuego</title>
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
                <li><a href="../gestionJuegos.php">Volver al panel de gestión</a></li>
            </ul>
        </nav>
        <?php if(isset($_SESSION['tag'])) : ?>
            <a class="tag" href="../../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central"><h1>Editar Videojuego</h1></div>
    <div class="admin-container">
        <form action="procesarEditarVideojuego.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <label>Título:</label> <input type="text" name="nombre" value="<?php echo htmlspecialchars($juego['titulo']); ?>" required>
            <label>Desarrollador:</label> <input type="text" name="developer" value="<?php echo htmlspecialchars($juego['developer']); ?>" required>
            <label>Descripción:</label> <textarea name="descripcion" required><?php echo htmlspecialchars($juego['descripcion']); ?></textarea>
            <label>Género:</label>
            <select name="genero" required>
                <?php
                $resGen = $conexion->query("SELECT nombre_genero FROM Genero");
                while ($row = $resGen->fetch_assoc()) {
                    $selected = ($row['nombre_genero'] == $juego['genero']) ? 'selected' : '';
                    echo "<option value='".htmlspecialchars($row['nombre_genero'])."' $selected>".htmlspecialchars($row['nombre_genero'])."</option>";
                }
                ?>
            </select>

            <label>Plataforma:</label>
            <select name="plataforma" required>
                <?php
                $resPlat = $conexion->query("SELECT nombre_plataforma FROM Plataforma");
                while ($row = $resPlat->fetch_assoc()) {
                    $selected = ($row['nombre_plataforma'] == $juego['plataforma']) ? 'selected' : '';
                    echo "<option value='".htmlspecialchars($row['nombre_plataforma'])."' $selected>".htmlspecialchars($row['nombre_plataforma'])."</option>";
                }
                ?>
            </select>
            <label>Fecha Lanzamiento:</label> <input type="date" name="fecha" value="<?php echo $juego['fecha_lanzamiento']; ?>" required>
            <label>Portada actual:</label><br>
            <img src="../../../../../media/<?php echo htmlspecialchars($juego['portada']); ?>" width="100"><br>
            <input type="file" name="portada" accept="image/*">
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>