<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    
    $id = (int)($_GET['id'] ?? 0);
    $comunidad = $conexion->query("SELECT * FROM comunidad WHERE id_comunidad = $id")->fetch_assoc();
    
    if (!$comunidad) { 
        header("Location: listaEditarComunidad.php"); 
        exit(); 
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Editar Comunidad</title>
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
                <li><a href="../gestionComunidades.php">Volver al panel de gestión</a></li>
            </ul>
        </nav>
        <?php if(isset($_SESSION['tag'])) : ?>
            <a class="tag" href="../../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central"><h1>Editar Comunidad</h1></div>
    <div class="admin-container">
        <form action="procesarEditarComunidad.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_comunidad" value="<?php echo $id; ?>">
            
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="nombre" style="color: var(--accent-color); font-weight: bold;">Nombre de la Comunidad</label>
                <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($comunidad['nombre']); ?>" class="search-input" required style="width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="id_videojuego" style="color: var(--accent-color); font-weight: bold;">Videojuego Principal</label>
                <select name="id_videojuego" id="id_videojuego" class="search-input" required style="width: 100%;">
                    <?php
                    $sqlJuegos = "SELECT id_videojuego, titulo FROM videojuego ORDER BY titulo ASC";
                    $resJ = mysqli_query($conexion, $sqlJuegos);
                    while($j = mysqli_fetch_assoc($resJ)){
                        $selected = ($j['id_videojuego'] == $comunidad['id_videojuego_principal']) ? 'selected' : '';
                        echo "<option value='{$j['id_videojuego']}' $selected>".htmlspecialchars($j['titulo'])."</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="banner" style="color: var(--accent-color); font-weight: bold;">Imagen de Banner (Dejar vacío para mantener actual)</label>
                <input type="file" name="banner" id="banner" class="search-input" accept="image/*" style="width: 100%; padding: 8px;">
            </div>

            <button type="submit" class="btn-agregar" style="border: none; cursor: pointer; align-self: center; width: 100%;">
                Guardar Cambios
            </button>
        </form>
    </div>
    <script>
    // Botón volver flotante para móvil
    (function() {
        // Crear el botón
        var btnVolver = document.createElement('button');
        btnVolver.innerHTML = '← Volver';
        btnVolver.id = 'btnVolverMovil';
        btnVolver.style.cssText = 'display:none; position:fixed; bottom:20px; left:20px; background:#e0be00; color:#000; border:none; padding:12px 20px; border-radius:50px; font-weight:bold; cursor:pointer; z-index:9999; box-shadow:0 2px 10px rgba(0,0,0,0.3);';
        
        document.body.appendChild(btnVolver);
        
        // Función para obtener la URL anterior
        btnVolver.onclick = function() {
            if (document.referrer && document.referrer !== '') {
                window.location.href = document.referrer;
            } else {
                // Si no hay referrer, ir a la página principal de admin
                window.location.href = '../gestionComunidades.php';
            }
        };
        
        // Mostrar solo en móvil (ancho menor a 768px)
        function checkWidth() {
            if (window.innerWidth <= 768) {
                btnVolver.style.display = 'block';
            } else {
                btnVolver.style.display = 'none';
            }
        }
        
        window.addEventListener('resize', checkWidth);
        checkWidth();
    })();
</script>
</body>
</html>