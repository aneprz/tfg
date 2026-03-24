<?php
session_start();
require_once __DIR__ . '/db/conexiones.php';

function resolverPortada($portada)
{
    $portada = is_string($portada) ? trim($portada) : '';

    if ($portada === '') {
        return 'media/logoPlatino.png';
    }

    if (preg_match('~^https?://~i', $portada) === 1 || strpos($portada, 'data:') === 0) {
        return $portada;
    }

    $portada = str_replace('\\', '/', ltrim($portada, '/'));

    if (preg_match('~(^|/)\\.\\.(?:/|$)~', $portada) === 1) {
        return 'media/logoPlatino.png';
    }

    if (strpos($portada, '/') === false) {
        $portada = 'media/' . $portada;
    }

    $rutaFs = __DIR__ . '/' . $portada;

    return is_file($rutaFs) ? $portada : 'media/logoPlatino.png';
}

$juegosPopulares = [];
$comunidades = [];
$idUsuarioSesion = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;
$admin = ($_SESSION['admin'] ?? false) === true;

if (isset($conexion) && $conexion) {

    $limiteJuegos = 6;

    $sqlJuegosPopularesSemana = "
        SELECT
            v.id_videojuego,
            v.titulo,
            v.rating_medio,
            v.portada,
            s.avg_players_semana
        FROM Videojuego v
        INNER JOIN (
            SELECT
                steam_appid,
                AVG(current_players) AS avg_players_semana,
                MAX(captured_at) AS last_captured_at
            FROM Steam_Players_History
            WHERE captured_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY steam_appid
        ) s ON s.steam_appid = v.steam_appid
        ORDER BY s.avg_players_semana DESC,
                 s.last_captured_at DESC,
                 v.rating_medio DESC,
                 v.titulo ASC
        LIMIT $limiteJuegos
    ";

    $resJuegos = mysqli_query($conexion, $sqlJuegosPopularesSemana);

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
        FROM Comunidad c
        LEFT JOIN Miembro_Comunidad mc
            ON mc.id_comunidad = c.id_comunidad
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
        <img src="media/logoPlatino.png" width="40">
        <a href="index.php" class="logo">Salsa<span>Box</span></a>
    </div>

    <nav>
        <ul>
            <li><a href="index.php" class="activo">Inicio</a></li>
            <li><a href="php/videojuegos/juegos.php">Juegos</a></li>
            <li><a href="php/jugadores/jugadores.php">Jugadores</a></li>
            <li><a href="php/comunidades/comunidades.php">Comunidades</a></li>
            <li><a href="php/logros/logros.php">Logros</a></li>
            <li><a href="php/ranking/ranking.php">Ranking</a></li>
            <?php if ($admin): ?>
                <li><a href="php/admin/indexAdmin.php">Admin</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <?php if (!isset($_SESSION['tag'])): ?>
    <a href="php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
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

            <a class="tag" href="/php/user/perfiles/perfilSesion.php">
                <?php echo htmlspecialchars($_SESSION['tag']); ?>
            </a>
        </div>
    <?php endif; ?>

    <script src="js/notificaciones.js"></script>
</header>


<div class="central">

    <h1>Registra, puntua y debate.</h1>

    <p>
        La red social para amantes de los videojuegos.
        Guarda lo que has jugado, puntua tus favoritos
        y unete a las comunidades de tus juegos preferidos.
    </p>

</div>


<main>

<h2>Populares esta semana</h2>

<div class="juegos">

<?php if (count($juegosPopulares) > 0): ?>

<?php foreach ($juegosPopulares as $juego): ?>

<?php
    $rating = $juego['rating_medio'];
    $estrellas = ($rating !== null) ? max(0, min(5, $rating / 2)) : 0;
    $porcentaje = ($estrellas / 5) * 100;
?>

<a href="php/videojuegos/juego.php?id=<?php echo (int)$juego['id_videojuego']; ?>" class="juego">

    <div class="portadaJuego">
        <img
            src="<?php echo htmlspecialchars(resolverPortada($juego['portada'])); ?>"
            alt="Portada de <?php echo htmlspecialchars($juego['titulo']); ?>"
        >
    </div>

    <div class="infoJuego">

        <div class="tituloJuego">
            <?php echo htmlspecialchars($juego['titulo']); ?>
        </div>

        <div class="puntuacionJuego">

            <div class="estrellas">
                <div class="relleno" style="width: <?php echo $porcentaje; ?>%"></div>
            </div>

            <span class="nota">
                <?php echo ($rating !== null ? number_format((float)$rating, 1) : "Sin nota"); ?>
            </span>

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

        <p>
            <?php echo number_format((int)$comunidad['total_miembros'], 0, ',', '.'); ?>
            miembros
        </p>

    </div>

<?php if (!isset($_SESSION['id_usuario'])): ?>

    <a href="php/sesiones/login/login.php" class="botonUnirse">
        Unirse
    </a>

<?php elseif ((int)$comunidad['es_miembro'] === 1): ?>

    <a
        href="php/comunidades/ver_comunidad.php?id=<?php echo (int)$comunidad['id_comunidad']; ?>"
        class="botonUnirse"
    >
        Ver muro
    </a>

<?php else: ?>

    <a
        href="php/comunidades/gestionar_miembro.php?accion=unirse&id_comunidad=<?php echo (int)$comunidad['id_comunidad']; ?>"
        class="botonUnirse"
    >
        Unirse
    </a>

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

<script src="js/notificaciones.js"></script>
</body>
</html>