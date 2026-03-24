<?php
session_start();
require '../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../sesiones/login/login.php");
    exit;
}

$admin = ($_SESSION['admin'] ?? false) === true;
$id_usuario = $_SESSION['id_usuario'];

$res = mysqli_query($conexion, "
SELECT ui.*, ti.nombre, ti.tipo, ti.imagen
FROM Usuario_Items ui
JOIN Tienda_Items ti ON ti.id_item = ui.id_item
WHERE ui.id_usuario = $id_usuario
ORDER BY ti.tipo, ui.equipado DESC
");
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SalsaBox - Inventario</title>

    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_juegos.css">
    <link rel="stylesheet" href="../../estilos/estilos_tienda.css">
    
    <link rel="icon" href="../../media/logoPlatino.png">

</head>

<body>

<header>

    <div class="tituloWeb">
        <img src="../../media/logoPlatino.png" width="40">
        <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
    </div>

    <nav>
        <ul>

            <li><a href="../../index.php">Inicio</a></li>
            <li><a href="../videojuegos/juegos.php">Juegos</a></li>
            <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
            <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
            <li><a href="../tienda/tienda.php" class="activo">Tienda</a></li>
            <li><a href="../logros/logros.php">Logros</a></li>
            <li><a href="../ranking/ranking.php">Ranking</a></li>
            <?php if ($admin): ?>
                <li><a href="../admin/indexAdmin.php">Admin</a></li>
            <?php endif; ?>

        </ul>
    </nav>

    <?php if (!isset($_SESSION['tag'])): ?>

        <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">
            Iniciar sesión
        </a>

    <?php else: ?>
        <div class="user-actions">
            <div class="notif-wrapper">
                <div id="bell-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22C13.1 22 14 21.1 14 20H10C10 21.1 10.9 22 12 22ZM18 16V11C18 7.93 16.37 5.36 13.5 4.68V4C13.5 3.17 12.83 2.5 12 2.5C11.17 2.5 10.5 3.17 10.5 4V4.68C7.64 5.36 6 7.92 6 11V16L4 18V19H20V18L18 16Z" fill="currentColor"/>
                    </svg>
                    <span id="notif-badge">0</span>
                </div>

                <div id="notif-dropdown">
                    <div class="notif-header">
                        <span>Notificaciones</span>
                        <button onclick="marcarLeidas()">Limpiar</button>
                    </div>
                    <ul id="notif-list"></ul>
                </div>
            </div>
            <a class="tag" href="../../php/user/perfiles/perfilSesion.php">
                <?php echo htmlspecialchars($_SESSION['tag']); ?>
            </a>
        </div>
    <?php endif; ?>

        <script src="../../js/notificaciones.js"></script>

</header>


<!-- SUBNAV -->
<div class="subnav">

    <div class="subnav-container">

        <a href="tienda.php" class="subnav-link">Tienda</a>
        <a href="inventario.php" class="subnav-link activo">Inventario</a>

    </div>

</div>


<div class="central">

    <h1>Tu inventario</h1>

    <p>
        Gestiona y equipa los objetos que has comprado.
    </p>

</div>


<main>

    <h2>Tus items</h2>

    <div class="juegos">

        <?php while ($item = mysqli_fetch_assoc($res)): ?>

            <div class="juego">

                <div class="portadaJuego">
                    <img src="../../media/<?php echo htmlspecialchars($item['imagen']); ?>">
                </div>

                <div class="infoJuego">

                    <div class="tituloJuego">
                        <?php echo htmlspecialchars($item['nombre']); ?>
                    </div>

                    <div class="precioItem">
                        <?php echo ucfirst($item['tipo']); ?>
                    </div>

                    <?php if ($item['equipado']): ?>

                        <form action="desequipar_item.php" method="POST">
                            <input type="hidden" name="id_item" value="<?php echo $item['id_item']; ?>">
                            <button class="btn-comprar">Desequipar</button>
                        </form>

                    <?php else: ?>

                        <form action="equipar_item.php" method="POST">
                            <input type="hidden" name="id_item" value="<?php echo $item['id_item']; ?>">
                            <button class="btn-comprar">Equipar</button>
                        </form>

                    <?php endif; ?>

                </div>

            </div>

        <?php endwhile; ?>

    </div>

</main>


<footer>
    <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
</footer>

</body>
</html>