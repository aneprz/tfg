<?php
session_start();
require_once __DIR__ . '/db/conexiones.php';

function estrellasDesdeRating($rating) {
    if ($rating === null || $rating === '') {
        return '☆☆☆☆☆';
    }

    $valor = (float) $rating;
    $llenas = (int) round($valor);
    $llenas = max(0, min(5, $llenas));

    return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

$juegosPopulares = [];
$comunidades = [];
$idUsuarioSesion = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;

if (isset($conexion) && $conexion) {
    $sqlJuegos = "
        SELECT
            v.id_videojuego,
            v.titulo,
            v.rating_medio,
            v.portada
        FROM videojuego v
        ORDER BY v.rating_medio DESC, v.titulo ASC
        LIMIT 12
    ";

    $resJuegos = mysqli_query($conexion, $sqlJuegos);
    if ($resJuegos) {
        while ($fila = mysqli_fetch_assoc($resJuegos)) {
            $juegosPopulares[] = $fila;
        }
        mysqli_free_result($resJuegos);
    }

    $sqlComunidades = "
        SELECT
            c.id_comunidad,
            c.nombre,
            COUNT(mc.id_usuario) AS total_miembros,
            MAX(CASE WHEN mc.id_usuario = $idUsuarioSesion THEN 1 ELSE 0 END) AS es_miembro
        FROM comunidad c
        LEFT JOIN miembro_comunidad mc ON mc.id_comunidad = c.id_comunidad
        GROUP BY c.id_comunidad, c.nombre
        ORDER BY total_miembros DESC, c.nombre ASC
        LIMIT 6
    ";

    $resComunidades = mysqli_query($conexion, $sqlComunidades);
    if ($resComunidades) {
        while ($fila = mysqli_fetch_assoc($resComunidades)) {
            $comunidades[] = $fila;
        }
        mysqli_free_result($resComunidades);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salsabox - Tu diario de videojuegos</title>
    <link rel="stylesheet" href="estilos/estilos_index.css">
    <link rel="icon" href="media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="media/logoPlatino.png" alt="" width="40px">
            <a href="index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="activo">Inicio</a></li>
                <li><a href="php/videojuegos/juegos.php">Juegos</a></li>
                <li><a href="php/jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="php/comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="php/logros/logros.php">Logros</a></li>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesion</a>
        <?php else: ?>
            <a class="tag" href="/php/user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Registra, puntua y debate.</h1>
        <p>La red social para amantes de los videojuegos. Guarda lo que has jugado, puntua tus favoritos y unete a las comunidades de tus juegos preferidos.</p>
    </div>

    <main>
        <h2>Populares esta semana</h2>
        <div class="juegos">
            <?php if (count($juegosPopulares) > 0): ?>
                <?php foreach ($juegosPopulares as $juego): ?>
                    <a href="php/videojuegos/juego.php?id=<?php echo (int) $juego['id_videojuego']; ?>" class="juego">
                        <div class="portadaJuego">
                            <img src="<?php echo htmlspecialchars($juego['portada'] ?: 'media/logoPlatino.png'); ?>" alt="Portada de <?php echo htmlspecialchars($juego['titulo']); ?>">
                        </div>
                        <div class="infoJuego">
                            <div class="tituloJuego"><?php echo htmlspecialchars($juego['titulo']); ?></div>
                            <div class="puntuacionJuego">
                                <?php
                                    $rating = $juego['rating_medio'];
                                    echo estrellasDesdeRating($rating) . ' ' . ($rating !== null ? number_format((float) $rating, 1) : 'Sin nota');
                                ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay videojuegos cargados en la base de datos todavia.</p>
            <?php endif; ?>
        </div>

        <h2>Comunidades Activas</h2>
        <div class="comunidades">
            <?php if (count($comunidades) > 0): ?>
                <?php foreach ($comunidades as $comunidad): ?>
                    <div class="comunidad">
                        <div class="infoComunidad">
                            <h3><?php echo htmlspecialchars($comunidad['nombre']); ?></h3>
                            <p><?php echo number_format((int) $comunidad['total_miembros'], 0, ',', '.'); ?> miembros</p>
                        </div>
                        <?php if (!isset($_SESSION['id_usuario'])): ?>
                            <a href="php/sesiones/login/login.php" class="botonUnirse">Unirse</a>
                        <?php elseif ((int) $comunidad['es_miembro'] === 1): ?>
                            <a
                                href="php/comunidades/ver_comunidad.php?id=<?php echo (int) $comunidad['id_comunidad']; ?>"
                                class="botonUnirse"
                            >Ver muro</a>
                        <?php else: ?>
                            <a
                                href="php/comunidades/gestionar_miembro.php?accion=unirse&id_comunidad=<?php echo (int) $comunidad['id_comunidad']; ?>"
                                class="botonUnirse"
                            >Unirse</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay comunidades cargadas en la base de datos todavia.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

</body>
</html>
