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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container .select2-selection--single { background-color: #2c3440 !important; border: 1px solid #444 !important; height: 40px !important; }
        .select2-container .select2-selection__rendered { color: #fff !important; line-height: 40px !important; }
        .select2-dropdown { background-color: #2c3440 !important; border: 1px solid #444 !important; }
        .select2-search__field { background-color: #2c3440 !important; color: #fff !important; }
        .select2-results__option { color: #fff !important; }
        .select2-results__option--highlighted { background-color: #e0be00 !important; color: #000 !important; }
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
        <form action="procesarAñadirLogro.php" method="POST">
            <label for="nombre">Título:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="descripcion">Descripcion:</label>
            <input type="text" id="descripcion" name="descripcion" required>

            <label for="puntos">Puntos:</label>
            <input type="number" id="puntos" name="puntos" required>

            <label for="buscador-juegos">Videojuego:</label>
            <select id="buscador-juegos" name="id_videojuego" style="width: 100%;" required>
                <option value="">Selecciona un juego...</option>
            </select>

            <button type="submit" style="margin-top: 20px;">Añadir Logro</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('#buscador-juegos').select2({
                placeholder: "Busca un juego...",
                ajax: {
                    url: 'buscar_juegos.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(p) { return {q: p.term}; },
                    processResults: function(d) { return {results: d.results}; }
                },
                minimumInputLength: 2
            });
        });
    </script>
</body>
</html>