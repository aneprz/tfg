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
    <title>SalsaBox - Eliminar Item</title>

    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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

<div class="central">
    <h1>Eliminar Item de Tienda</h1>
</div>

<div class="admin-container">
    <table id="tablaItems" style="width: 100%;">
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
            { "data": "id_item", "render": function(data) {

                return `
                <form action="procesarEliminarItem.php" method="POST"
                      onsubmit="return confirm('¿Eliminar este item permanentemente?');">
                    
                    <input type="hidden" name="id" value="${data}">
                    <button type="submit">Eliminar</button>
                
                </form>`;
            }}
        ],

        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
    });

});
</script>

</body>
</html>