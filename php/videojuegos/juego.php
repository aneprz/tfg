<?php
session_start();
require '../../db/conexiones.php';

function estrellasDesdeRating($rating) {
    if ($rating === null || $rating === '') {
        return '☆☆☆☆☆';
    }

    $valor = (float) $rating;
    $llenas = (int) round($valor);
    $llenas = max(0, min(5, $llenas));

    return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

function resolverPortada($portada) {
    if (empty($portada)) {
        return '../../media/logoPlatino.png';
    }

    if (strpos($portada, 'http') === 0 || strpos($portada, '/') === 0) {
        return $portada;
    }

    return '/media/' . $portada;
}

$idJuego = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$juego = null;

if (isset($conexion) && $conexion && $idJuego > 0) {
    $sql = "
        SELECT 
            v.id_videojuego, 
            v.titulo, 
            v.descripcion, 
            v.fecha_lanzamiento, 
            v.developer, 
            v.rating_medio, 
            v.portada, 
            v.genero AS generos
        FROM Videojuego v
        WHERE v.id_videojuego = ?
        LIMIT 1
    ";

    try {
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $idJuego);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        if ($resultado) {
            $juego = mysqli_fetch_assoc($resultado) ?: null;
            mysqli_free_result($resultado);
        }
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        $juego = null;
        error_log('Error al cargar juego.php: ' . $e->getMessage());
    }
}
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Ficha de Juego</title>
    <link rel="stylesheet" href="../../estilos/estilos_juego.css">
    <link rel="icon" href="../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="juegos.php" class="activo">Juegos</a></li>
                <li><a href="../../php/jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesion</a>
        <?php else: ?>
            <a class="tag" href="../../php/user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>

    <main>
        <?php if($juego): ?>
            <section class="fichaJuego">
                <div class="imagenJuego">
                    <img src="<?php echo htmlspecialchars(resolverPortada($juego['portada'])); ?>" alt="Portada de <?php echo htmlspecialchars($juego['titulo']); ?>">
                </div>
                <div class="contenidoJuego">
                    <h1><?php echo htmlspecialchars($juego['titulo']); ?></h1>
                    <p class="meta">
                        <?php
                            $partesMeta = [];
                            if (!empty($juego['generos'])) {
                                $partesMeta[] = $juego['generos'];
                            }
                            if (!empty($juego['fecha_lanzamiento'])) {
                                $partesMeta[] = date('Y', strtotime($juego['fecha_lanzamiento']));
                            }
                            if (!empty($juego['developer'])) {
                                $partesMeta[] = $juego['developer'];
                            }

                            $rating = $juego['rating_medio'];
                            $textoRating = $rating !== null ? estrellasDesdeRating($rating) . ' ' . number_format((float) $rating, 1) : 'Sin nota';
                            $partesMeta[] = $textoRating;

                            echo htmlspecialchars(implode(' • ', $partesMeta));
                        ?>
                    </p>
                    <p><?php echo htmlspecialchars($juego['descripcion'] ?: 'Este juego aun no tiene descripcion.'); ?></p>

                    <div class="bloqueProximamente">
                        <h2>Detalles del juego</h2>
                        <p>Fecha de lanzamiento: <?php echo !empty($juego['fecha_lanzamiento']) ? htmlspecialchars($juego['fecha_lanzamiento']) : 'Sin fecha'; ?></p>
                        <p>Developer: <?php echo !empty($juego['developer']) ? htmlspecialchars($juego['developer']) : 'Sin developer'; ?></p>
                    </div>

                    <a href="juegos.php" class="botonVolver">Volver al catalogo</a>
                </div>
            </section>
        <?php else: ?>
            <section class="fichaJuego vacio">
                <h1>Juego no encontrado</h1>
                <p>La ficha solicitada no existe en la base de datos actual.</p>
                <a href="juegos.php" class="botonVolver">Ir a juegos</a>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>
</body>
</html>