<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }

    $id_propio = $_SESSION['id_usuario'] ?? 0;
    $res = $conexion->query("SELECT id_usuario, gameTag, admin FROM Usuario WHERE id_usuario != $id_propio ORDER BY gameTag ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Gestionar Admins</title>
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
        <h1>Gestionar Permisos de Admin</h1>
        <p>Activa o desactiva los privilegios de administrador para los jugadores.</p>
    </div>

    <div class="admin-container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
        <input type="text" id="buscador" placeholder="Buscar jugador por GameTag..." onkeyup="filtrarTabla()" 
               style="width: 100%; padding: 12px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #2c3440; background: #1f252c; color: white;">

        <table id="tablaJugadores" style="width: 100%; border-collapse: collapse; background: var(--card-bg); border-radius: 8px; overflow: hidden;">
            <thead>
                <tr style="text-align: left; background: #2c3440;">
                    <th style="padding: 15px;">GameTag (Jugador)</th>
                    <th style="padding: 15px; text-align: center;">Privilegios Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td style="padding: 15px; border-bottom: 1px solid #2c3440;"><?php echo htmlspecialchars($row['gameTag']); ?></td>
                    <td style="padding: 15px; border-bottom: 1px solid #2c3440; text-align: center;">
                        <form action="procesarGestionarAdmins.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $row['id_usuario']; ?>">
                            <input type="hidden" name="nuevo_estado" value="<?php echo $row['admin'] ? '0' : '1'; ?>">
                            <label class="switch">
                                <input type="checkbox" <?php echo $row['admin'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <span class="slider"></span>
                            </label>
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
            let tabla = document.getElementById("tablaJugadores");
            let filas = tabla.getElementsByTagName("tr");
            
            for (let i = 1; i < filas.length; i++) {
                let nombre = filas[i].getElementsByTagName("td")[0].textContent.toLowerCase();
                filas[i].style.display = nombre.includes(input) ? "" : "none";
            }
        }
    </script>
</body>
</html>