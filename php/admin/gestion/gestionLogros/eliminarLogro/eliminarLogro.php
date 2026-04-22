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
    <title>SalsaBox - Eliminar Logro</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>

    .dataTables_wrapper .dataTables_length select { background-color: #2c3440 !important; color: #ffffff !important; border: 1px solid #444; padding: 5px; }
    .dataTables_wrapper .dataTables_filter input { background-color: #2c3440 !important; color: #ffffff !important; border: 1px solid #444; }
    .dataTables_wrapper { color: #ffffff !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button { color: #ffffff !important; }
    .dataTables_wrapper .dataTables_info { color: #ffffff !important; }
    #tablaLogros thead th { border-bottom: 2px solid #e0be00 !important; }

    .select2-container--default .select2-selection--single { background-color: #2c3440 !important; border: 1px solid #444 !important; height: 40px; }
    .select2-container--default .select2-selection__rendered { color: #ffffff !important; line-height: 40px !important; }
    .select2-dropdown { background-color: #2c3440 !important; border: 1px solid #444 !important; }
    .select2-search--dropdown .select2-search__field { background-color: #2c3440 !important; color: #ffffff !important; border: 1px solid #555 !important; }
    .select2-results__option { color: #ffffff !important; }
    .select2-results__option--highlighted { background-color: #e0be00 !important; color: #000 !important; }
    .select2-container--default .select2-selection__placeholder { color: #aaa !important; }
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
    <div class="central"><h1>Eliminar Logro</h1></div>
    <div class="admin-container">
        <div style="margin-bottom: 20px;">
            <label>Filtrar por juego:</label>
            <select id="filtroJuego" style="width: 300px;"></select>
        </div>
        <table id="tablaLogros" style="width: 100%;">
            <thead><tr><th>Título</th><th>Acción</th></tr></thead>
        </table>
    </div>
    <script>
        $(document).ready(function() {
            $('#filtroJuego').select2({
                placeholder: "Buscar juego...",
                ajax: { url: 'buscar_juegos.php', dataType: 'json', delay: 250, data: function(p) { return {q: p.term}; }, processResults: function(d) { return {results: d.results}; } },
                minimumInputLength: 2,
                allowClear: true
            });

            var table = $('#tablaLogros').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "obtener_datos_logros.php",
                    "data": function(d) { d.id_juego = $('#filtroJuego').val(); }
                },
                "columns": [
                    { "data": "nombre_logro" },
                    { "data": "id_logro", "render": function(data) {
                        return `<form action="procesarEliminarLogro.php" method="POST" onsubmit="return confirm('¿Seguro?');">
                                    <input type="hidden" name="id" value="${data}">
                                    <button type="submit">Eliminar</button>
                                </form>`;
                    }}
                ],
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });
            $('#filtroJuego').on('change', function() { table.ajax.reload(); });
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