<?php
session_start();

require_once __DIR__ . '/../../db/conexiones.php';

$comunidades = [];

if (isset($conexion) && $conexion) {
    $sqlComunidades = "
        SELECT 
            c.id_comunidad, 
            c.nombre, 
            v.portada,
            v.titulo AS juego_nombre,
            COUNT(mc.id_usuario) AS total_miembros
        FROM Comunidad c
        LEFT JOIN Videojuego v ON c.id_videojuego_principal = v.id_videojuego
        LEFT JOIN Miembro_comunidad mc ON mc.id_comunidad = c.id_comunidad
        GROUP BY c.id_comunidad, c.nombre, v.portada, v.titulo
        ORDER BY total_miembros DESC, c.nombre ASC";

    $res = mysqli_query($conexion, $sqlComunidades);
    if ($res) {
        while ($fila = mysqli_fetch_assoc($res)) {
            $comunidades[] = $fila;
        }
    }
}
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidades - SalsaBox</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_comunidades.css">
    <link rel="icon" href="../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="../../php/jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="comunidades.php" class="activo">Comunidades</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesion</a>
        <?php else: ?>
            <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Comunidades</h1> <a href="agregar_comunidad.php" class="btn-agregar">Añadir Comunidad</a>
        <p>Únete a grupos de tus juegos favoritos, comparte clips y conoce a otros jugadores.</p>
    </div>

    <main>
        <h2>Explorar Grupos</h2>
        <div class="grid-comunidades">
            <?php if (count($comunidades) > 0): ?>
                <?php foreach ($comunidades as $com): ?>
                    <article class="card-comunidad">
                        <div class="banner-comunidad" style="background-image: url('../../<?php echo htmlspecialchars($com['portada'] ?: 'media/logoPlatino.png'); ?>')">
                            <div class="overlay-miembros">
                                ● <?php echo number_format($com['total_miembros'], 0, ',', '.'); ?> miembros
                            </div>
                        </div>
                        <div class="cuerpo-comunidad">
                            <span class="juego-tag"><?php echo htmlspecialchars($com['juego_nombre']); ?></span>
                            <h3><?php echo htmlspecialchars($com['nombre']); ?></h3>
                            
                            <div class="acciones-com">
                                <a href="ver_comunidad.php?id=<?php echo $com['id_comunidad']; ?>" class="btn-entrar">Ver Muro</a>
                                <button class="botonUnirse">Unirse</button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="aviso-vacio">No se encontraron comunidades creadas en la base de datos.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>
</body>
</html>