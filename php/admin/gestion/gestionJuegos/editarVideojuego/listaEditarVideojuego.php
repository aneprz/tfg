<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { header("Location: ../../index.php"); exit(); }
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .dataTables_wrapper { color: #ffffff !important; }
        .dataTables_wrapper .dataTables_filter input { background-color: #2c3440 !important; color: #fff !important; border: 1px solid #444; }
        #tablaJuegos thead th { border-bottom: 2px solid #e0be00 !important; }
    </style>
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../../../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../../../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul><li><a href="../gestionJuegos.php">Volver al panel de gestión</a></li></ul>
        </nav>
        <?php if(isset($_SESSION['tag'])) : ?>
            <a class="tag" href="../../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central"><h1>Seleccionar Videojuego a Editar</h1></div>
    <div class="admin-container">
        <table id="tablaJuegos" style="width: 100%;">
            <thead><tr><th>Título</th><th>Acción</th></tr></thead>
        </table>
    </div>
    <script>
        $(document).ready(function() {
            $('#tablaJuegos').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "obtener_datos_juegos.php",
                "columns": [
                    { "data": "titulo" },
                    { "data": "id_videojuego", "render": function(data) {
                        return `<a href="editarVideojuego.php?id=${data}" class="btn-editar">Editar</a>`;
                    }}
                ],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });
        });
    </script>
</body>
</html>