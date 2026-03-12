<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Eliminar Logro</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .dataTables_wrapper .dataTables_length select { background-color: #2c3440 !important; color: #ffffff !important; border: 1px solid #444; padding: 5px; }
        .dataTables_wrapper .dataTables_filter input { background-color: #2c3440 !important; color: #ffffff !important; border: 1px solid #444; }
        .dataTables_wrapper { color: #ffffff !important; }
        .dataTables_wrapper .dataTables_paginate .paginate_button { color: #ffffff !important; }
        .dataTables_wrapper .dataTables_info { color: #ffffff !important; }
        #tablaLogros thead th { border-bottom: 2px solid #e0be00 !important; }
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
        <h1>Eliminar Logro</h1>
    </div>

    <div class="admin-container">
        <table id="tablaLogros" style="width: 100%;">
            <thead>
                <tr>
                    <th style="padding: 10px;">Título</th>
                    <th style="padding: 10px;">Acción</th>
                </tr>
            </thead>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('#tablaLogros').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": "obtener_datos_logros.php",
                "columns": [
                    { "data": "nombre_logro" },
                    { 
                        "data": "id_logro",
                        "render": function(data, type, row) {
                            return `<form action="procesarEliminarLogro.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este logro permanentemente?');">
                                        <input type="hidden" name="id" value="${data}">
                                        <button type="submit">Eliminar</button>
                                    </form>`;
                        }
                    }
                ],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });
        });
    </script>
</body>
</html>