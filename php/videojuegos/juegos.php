<?php
session_start();
require '../../db/conexiones.php';

function ratingAEstrellas($rating) {
    if ($rating === null || $rating === '') {
        return 0;
    }

    return max(0, min(5, (float)$rating / 2));
}

function resolverPortada($portada) {
    $portada = is_string($portada) ? trim($portada) : '';

    if ($portada === '') {
        return '../../media/logoPlatino.png';
    }

    if (preg_match('~^https?://~i', $portada) === 1 || strpos($portada, 'data:') === 0) {
        return $portada;
    }

    $portada = str_replace('\\', '/', ltrim($portada, '/'));

    if (preg_match('~(^|/)\\.\\.(?:/|$)~', $portada) === 1) {
        return '../../media/logoPlatino.png';
    }

    if (strpos($portada, '/') === false) {
        $portada = 'media/' . $portada;
    }

    $rutaWeb = '../../' . $portada;
    $rutaFs = __DIR__ . '/../../' . $portada;

    return is_file($rutaFs) ? $rutaWeb : '../../media/logoPlatino.png';
}

$juegos = [];

/* PAGINACIÓN */

$porPagina = 48;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$offset = ($pagina - 1) * $porPagina;
$totalPaginas = 1;

if (isset($conexion) && $conexion) {

    /* TOTAL DE JUEGOS */

    $totalQuery = mysqli_query($conexion, "SELECT COUNT(*) as total FROM Videojuego");

    if ($totalQuery) {
        $filaTotal = mysqli_fetch_assoc($totalQuery);
        $totalJuegos = (int)$filaTotal['total'];
        $totalPaginas = ceil($totalJuegos / $porPagina);

        mysqli_free_result($totalQuery);
    }

    /* JUEGOS DE LA PAGINA ACTUAL */

    $sql = "
        SELECT id_videojuego, titulo, rating_medio, portada
        FROM Videojuego
        ORDER BY titulo ASC
        LIMIT $porPagina OFFSET $offset
    ";

    $resultado = mysqli_query($conexion, $sql);

    if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $juegos[] = $fila;
        }

        mysqli_free_result($resultado);
    }
}

$admin = ($_SESSION['admin'] ?? false) === true;
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SalsaBox - Juegos</title>

    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_juegos.css">

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
            <li><a href="juegos.php" class="activo">Juegos</a></li>
            <li><a href="../../php/jugadores/jugadores.php">Jugadores</a></li>
            <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
            <li><a href="../logros/logros.php">Logros</a></li>

            <?php if ($admin): ?>
                <li><a href="../admin/indexAdmin.php">Admin</a></li>
            <?php endif; ?>

        </ul>

    </nav>

    <?php if (!isset($_SESSION['tag'])) : ?>

        <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">
            Iniciar sesion
        </a>

    <?php else: ?>

        <a class="tag" href="../../php/user/perfiles/perfilSesion.php">
            <?php echo htmlspecialchars($_SESSION['tag']); ?>
        </a>

    <?php endif; ?>

</header>


<div class="central">

    <h1>Encuentra tu proxima aventura</h1>

    <p>
        Busca por nombre y descubre todos los videojuegos del catalogo visual de SalsaBox.
    </p>

    <br>

    <div class="buscadorContainer">
        <input type="text"
               id="buscadorJuegos"
               placeholder="Buscar videojuego..."
               aria-label="Buscar videojuego">
    </div>

</div>


<main>

    <h2>Todos los videojuegos</h2>

    <div class="juegos" id="gridJuegos">

        <?php if (count($juegos) > 0): ?>

            <?php foreach ($juegos as $juego): ?>

                <a class="juegoLink"
                   href="juego.php?id=<?php echo (int)$juego['id_videojuego']; ?>"
                   data-titulo="<?php echo htmlspecialchars(strtolower($juego['titulo'])); ?>">

                    <article class="juego">

                        <div class="portadaJuego">

                            <img
                                src="<?php echo htmlspecialchars(resolverPortada($juego['portada'])); ?>"
                                alt="Portada de <?php echo htmlspecialchars($juego['titulo']); ?>">

                        </div>

                        <div class="infoJuego">

                            <div class="tituloJuego">
                                <?php echo htmlspecialchars($juego['titulo']); ?>
                            </div>

                            <?php
                            $rating = $juego['rating_medio'];
                            $estrellas = ratingAEstrellas($rating);
                            $porcentaje = ($estrellas / 5) * 100;
                            ?>

                            <div class="puntuacionJuego">

                                <div class="estrellas">
                                    <div class="relleno" style="width: <?php echo $porcentaje; ?>%"></div>
                                </div>

                                <span class="nota">
                                    <?php echo ($rating !== null ? number_format((float)$rating, 1) : 'Sin nota'); ?>
                                </span>

                            </div>

                        </div>

                    </article>

                </a>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No hay videojuegos cargados en la base de datos todavia.</p>

        <?php endif; ?>

    </div>


    <p id="sinResultados" class="sinResultados" hidden>
        No se encontraron juegos para esa busqueda.
    </p>


    <!-- PAGINACIÓN -->

    <div class="paginacion">

        <?php if ($pagina > 1): ?>
            <a href="?pagina=<?php echo $pagina - 1; ?>" class="flecha">←</a>
        <?php endif; ?>

        <?php if ($pagina < $totalPaginas): ?>
            <a href="?pagina=<?php echo $pagina + 1; ?>" class="flecha">→</a>
        <?php endif; ?>

    </div>

</main>


<footer>

    <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>

</footer>


<script>

    const buscador = document.getElementById('buscadorJuegos');
    const tarjetas = document.querySelectorAll('.juegoLink');
    const sinResultados = document.getElementById('sinResultados');

    buscador.addEventListener('input', function () {

        const termino = this.value.toLowerCase().trim();
        let visibles = 0;

        tarjetas.forEach(function (tarjeta) {

            const titulo = tarjeta.dataset.titulo;
            const coincide = titulo.includes(termino);

            tarjeta.style.display = coincide ? 'block' : 'none';

            if (coincide) {
                visibles++;
            }

        });

        sinResultados.hidden = visibles !== 0;

    });

</script>

</body>
</html>