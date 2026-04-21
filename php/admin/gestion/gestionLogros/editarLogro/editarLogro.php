<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $conexion->prepare("SELECT * FROM logros WHERE id_logro = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $logro = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$logro) { header("Location: listaEditarLogro.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Editar Logros</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container--default .select2-selection--single { background-color: #2c3440 !important; border: 1px solid #444 !important; height: 40px; }
        .select2-container--default .select2-selection__rendered { color: #ffffff !important; line-height: 40px !important; }
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
            <ul><li><a href="../gestionLogros.php">Volver al panel de gestión</a></li></ul>
        </nav>
        <?php if(isset($_SESSION['tag'])) : ?>
            <a class="tag" href="../../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central"><h1>Editar Logros</h1></div>
    <div class="admin-container">
        <form action="procesarEditarLogro.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <label>Título:</label> <input type="text" name="nombre" value="<?php echo htmlspecialchars($logro['nombre_logro']); ?>" required>
            <label>Descripción:</label> <textarea name="descripcion" required><?php echo htmlspecialchars($logro['descripcion']); ?></textarea>
            <label>Puntos:</label> <input type="number" name="puntos" value="<?php echo (int)$logro['puntos_logro']; ?>" required>
            <label>Videojuego:</label>
            <select id="buscador-juegos" name="id_videojuego" style="width: 100%;" required>
                <?php 
                    $j = $conexion->query("SELECT titulo FROM videojuego WHERE id_videojuego = " . (int)$logro['id_videojuego'])->fetch_assoc();
                    echo '<option value="'.(int)$logro['id_videojuego'].'">'.htmlspecialchars($j['titulo']).'</option>';
                ?>
            </select>
            <button type="submit" style="margin-top:20px;">Guardar Cambios</button>
        </form>
    </div>
    <script>
        $(document).ready(function() {
            $('#buscador-juegos').select2({
                placeholder: "Buscar juego...",
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
    <script>
    (function() {
        var btnVolver = document.createElement('button');
        btnVolver.innerHTML = '← Volver';
        btnVolver.id = 'btnVolverMovil';
        btnVolver.style.cssText = 'display:none; position:fixed; bottom:20px; left:20px; background:#e0be00; color:#000; border:none; padding:12px 20px; border-radius:50px; font-weight:bold; cursor:pointer; z-index:9999; box-shadow:0 2px 10px rgba(0,0,0,0.3);';
        document.body.appendChild(btnVolver);
        btnVolver.onclick = function() {
            window.location.href = '../gestionLogros.php';
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