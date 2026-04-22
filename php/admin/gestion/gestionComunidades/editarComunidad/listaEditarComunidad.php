<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    $admin = true;
    $res = $conexion->query("SELECT id_comunidad, nombre FROM comunidad ORDER BY nombre ASC");
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
    <div class="central">
        <h1>Seleccionar Comunidad</h1>
    </div>
    <div class="admin-container">
    <input type="text" id="buscador" placeholder="🔍 Buscar comunidad..." onkeyup="filtrar()" style="width:100%; padding:12px; border: 1px solid #ddd; border-radius: 8px;">

    <table class="admin-table" id="tablaJuegos">
        <thead>
            <tr>
                <th>Título de la Comunidad</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td>
                        <a href="editarComunidad.php?id=<?php echo (int)$row['id_comunidad']; ?>" class="btn-editar">Editar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    function filtrar() {
        let val = document.getElementById("buscador").value.toLowerCase();
        let filas = document.getElementById("tablaJuegos").getElementsByTagName("tr");
        for(let i = 1; i < filas.length; i++) {
            let titulo = filas[i].cells[0].textContent.toLowerCase();
            filas[i].style.display = titulo.includes(val) ? "" : "none";
        }
    }
</script>
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