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
    <title>SalsaBox - Editar Items</title>

    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- 🔥 ESTILOS IGUALES QUE VIDEOJUEGOS -->
    <style>
        .dataTables_wrapper { color: #ffffff !important; }

        .dataTables_wrapper .dataTables_filter input {
            background-color: #2c3440 !important;
            color: #fff !important;
            border: 1px solid #444;
        }

        #tablaItems thead th {
            border-bottom: 2px solid #e0be00 !important;
        }

        .btn-editar {
            background-color: #00cfe8;
            color: #000;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-editar:hover {
            background-color: #00a8c0;
        }
    </style>
</head>

<body>

<header>
    <div class="tituloWeb">
        <img src="../../../../../media/logoPlatino.png" width="40px">
        <a href="../../../../../index.php" class="logo">Salsa<span>Box</span></a>
    </div>

    <nav>
        <ul>
            <!-- 🔥 MISMO LINK QUE VIDEOJUEGOS -->
            <li><a href="../gestionTienda.php">Volver al panel de gestión</a></li>
        </ul>
    </nav>

    <!-- 🔥 ESTO TE FALTABA -->
    <?php if(isset($_SESSION['tag'])) : ?>
        <a class="tag" href="../../../../user/perfiles/perfilSesion.php">
            <?php echo htmlspecialchars($_SESSION['tag']); ?>
        </a>
    <?php endif; ?>
</header>

<div class="central">
    <h1>Seleccionar Item a Editar</h1>
</div>

<div class="admin-container">
    <table id="tablaItems" style="width:100%;">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Acción</th>
            </tr>
        </thead>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#tablaItems').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "../obtener_datos_items.php",
        "columns": [
            { "data": "nombre" },
            {
                "data": "id_item",
                "render": function(data) {
                    return `<a class="btn-editar" href="editarItem.php?id=${data}">Editar</a>`;
                }
            }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
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