<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Comunidad - SalsaBox</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_comunidades.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="icon" href="../../media/logoPlatino.png">
    <style>
        .select2-container--default .select2-selection--single {
            background-color: var(--card-bg) !important;
            border: 1px solid #2c3440 !important;
            height: 48px !important;
            padding: 8px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: white !important;
            line-height: 30px !important;
        }
        .select2-dropdown {
            background-color: var(--card-bg) !important;
            border: 1px solid var(--accent-color) !important;
        }
        .select2-search__field {
            background-color: #0d0f12 !important;
            color: white !important;
        }
        .select2-results__option--highlighted {
            background-color: var(--accent-color) !important;
            color: #000 !important;
        }
    </style>
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="../../php/jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="comunidades.php" class="activo">Comunidades</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesion</a>
        <?php else: ?>
            <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Crear Nueva Comunidad</h1>
        <p>Establece un lugar de encuentro para los fans de tus juegos favoritos.</p>
    </div>

    <main>
        <form action="procesar_agregar_comunidad.php" method="POST" enctype="multipart/form-data" class="search-section" style="max-width: 600px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="nombre" style="color: var(--accent-color); font-weight: bold;">Nombre de la Comunidad</label>
                <input type="text" name="nombre" id="nombre" class="search-input" placeholder="Ej: Fans de Elden Ring" required style="width: 100%;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="id_videojuego" style="color: var(--accent-color); font-weight: bold;">Videojuego Principal</label>
                <select name="id_videojuego" id="id_videojuego" class="select-buscable" required style="width: 100%;">
                    <option value="">Selecciona un videojuego...</option>
                    <?php
                    $sqlJuegos = "SELECT id_videojuego, titulo FROM videojuego ORDER BY titulo ASC";
                    $resJ = mysqli_query($conexion, $sqlJuegos);
                    while($j = mysqli_fetch_assoc($resJ)){
                        echo "<option value='{$j['id_videojuego']}'>".htmlspecialchars($j['titulo'])."</option>";
                    }
                    ?>
                </select>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="banner" style="color: var(--accent-color); font-weight: bold;">Imagen de Banner</label>
                <input type="file" name="banner" id="banner" class="search-input" accept="image/*" required style="width: 100%; padding: 8px;">
            </div>

            <input type="hidden" name="id_creador" value="<?php echo $_SESSION['id_usuario']; ?>">

            <button type="submit" class="btn-agregar" style="border: none; cursor: pointer; align-self: center; width: 100%;">
                Crear Comunidad
            </button>
        </form>
    </main>

    <script>
    $(document).ready(function() {
        $('.select-buscable').select2({
            placeholder: "Buscar videojuego...",
            allowClear: true
        });
    });
    </script>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>
</body>
</html>