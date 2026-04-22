<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    $admin = true;

    $res = $conexion->query("SELECT id_comunidad, nombre FROM Comunidad ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Eliminar Comunidad</title>
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
        <h1>Eliminar Comunidad</h1>
    </div>
    <div class="admin-container">
        <input type="text" id="buscador" placeholder="Buscar comunidad por nombre..." onkeyup="filtrarTabla()" style="width: 100%; padding: 10px; margin-bottom: 20px;">

        <table id="tablaJuegos" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left;">
                    <th style="padding: 10px;">Nombre</th>
                    <th style="padding: 10px;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;">
                        <form action="procesarEliminarComunidad.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta comunidad permanentemente?');">
                            <input type="hidden" name="id" value="<?php echo $row['id_comunidad']; ?>">
                            <button type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        function filtrarTabla() {
            let input = document.getElementById("buscador").value.toLowerCase();
            let tabla = document.getElementById("tablaJuegos");
            let filas = tabla.getElementsByTagName("tr");
            
            for (let i = 1; i < filas.length; i++) {
                let titulo = filas[i].getElementsByTagName("td")[0].textContent.toLowerCase();
                filas[i].style.display = titulo.includes(input) ? "" : "none";
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