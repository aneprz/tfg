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
    <title>SalsaBox - Añadir Videojuego</title>
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
    <div class="central">
        <h1>Añadir Videojuego</h1>
    </div>
    <div class="admin-container">
        <form action="procesarAñadirVideojuego.php" method="POST" enctype="multipart/form-data">
            <label for="nombre">Título:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="developer">Desarrollador:</label>
            <input type="text" id="developer" name="developer" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" required></textarea>

            <label for="fecha_lanzamiento">Fecha de lanzamiento:</label>
            <input type="date" id="fecha_lanzamiento" name="fecha_lanzamiento" required>

            <label for="portada">Portada (Imagen):</label>
            <input type="file" id="portada" name="portada" accept="image/*" required>

            <label for="genero">Género:</label>
            <select id="genero" name="genero" required>
                <?php
                $res = $conexion->query("SELECT nombre_genero FROM Genero");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['nombre_genero']}'>{$row['nombre_genero']}</option>";
                }
                ?>
            </select>

            <label for="plataforma">Plataforma:</label>
            <select id="plataforma" name="plataforma" required>
                <?php
                $res = $conexion->query("SELECT nombre_plataforma FROM Plataforma");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['nombre_plataforma']}'>{$row['nombre_plataforma']}</option>";
                }
                ?>
            </select>

            <button type="submit">Añadir Videojuego</button>
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
            window.location.href = '../gestionJuegos.php';
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