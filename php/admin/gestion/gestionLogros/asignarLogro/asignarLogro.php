<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';
    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) { 
        header("Location: ../../index.php"); 
        exit(); 
    }
    $jugadores = $conexion->query("SELECT id_usuario, gameTag FROM Usuario ORDER BY gameTag ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Asignar Logro</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable { background-color: #e0be00 !important; color: #000 !important; }
        .select2-container--default .select2-results__option { background-color: #2c3440; color: #ffffff; }
        .select2-container--default .select2-selection--single, .select2-search--dropdown .select2-search__field { background-color: #2c3440 !important; color: #ffffff !important; border: 1px solid #444; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { color: #ffffff !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow b { border-color: #ffffff transparent transparent transparent !important; }
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

    <div class="admin-container">
        <h1>Asignar Logro</h1>
        <form action="procesarAsignarLogro.php" method="POST">
            <label>Jugador:</label>
            <select name="id_usuario" class="buscador" required>
                <?php while($j = $jugadores->fetch_assoc()): ?>
                    <option value="<?php echo $j['id_usuario']; ?>"><?php echo htmlspecialchars($j['gameTag']); ?></option>
                <?php endwhile; ?>
            </select>

            <label style="margin-top:20px; display:block;">Juego:</label>
            <select id="select-juego" class="buscador" required></select>

            <label style="margin-top:20px; display:block;">Logro:</label>
            <select name="id_logro" id="select-logro" class="buscador" required></select>

            <button type="submit" style="margin-top: 20px;">Asignar Logro</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('.buscador').select2({ width: '100%' });
            
            $('#select-juego').select2({
                placeholder: "Buscar juego...",
                ajax: { 
                    url: 'obtener_juegos.php', 
                    dataType: 'json', 
                    delay: 250, 
                    data: function(p) { return {q: p.term}; }, 
                    processResults: function(d) { return {results: d.results}; }, 
                    cache: true 
                },
                width: '100%'
            });
            
            $('#select-juego').on('change', function() {
                let idJuego = $(this).val();
                let $sLogro = $('#select-logro');
                $sLogro.empty();
                $.getJSON('obtener_logros.php', {id_juego: idJuego}, function(data) {
                    $sLogro.append(new Option("Selecciona logro", ""));
                    data.forEach(item => $sLogro.append(new Option(item.text, item.id)));
                });
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