<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    $admin = true;

    $res = $conexion->query("SELECT id_usuario, gameTag FROM Usuario ORDER BY gameTag ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Eliminar Jugador</title>
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
                <li><a href="../gestionJugadores.php">Volver al panel de gestión</a></li>
            </ul>
        </nav>
        <?php if(isset($_SESSION['tag'])) : ?>
            <a class="tag" href="../../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central">
        <h1>Eliminar Jugador</h1>
    </div>
    <div class="admin-container">
        <input type="text" id="buscador" placeholder="Buscar jugador por nombre..." onkeyup="filtrarTabla()" style="width: 100%; padding: 10px; margin-bottom: 20px;">

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
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($row['gameTag']); ?></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;">
                        <form action="procesarEliminarJugadores.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este jugador permanentemente?');">
                            <input type="hidden" name="id" value="<?php echo $row['id_usuario']; ?>">
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
</body>
</html>