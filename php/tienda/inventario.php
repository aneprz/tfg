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
            <li><a href="../logros/logros.php">Logros</a></li>

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

        <a class="tag" href="../../php/user/perfiles/perfilSesion.php">
            <?php echo htmlspecialchars($_SESSION['tag']); ?>
        </a>

    <?php endif; ?>

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

                        <button class="btn-comprar" disabled>
                            Equipado
                        </button>

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