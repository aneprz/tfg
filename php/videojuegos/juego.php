<?php
session_start();

$slug = isset($_GET['slug']) ? strtolower(trim($_GET['slug'])) : '';

$juegos = [
    'elden-ring' => [
        'titulo' => 'Elden Ring',
        'imagen' => 'media/portadaEldenRing.jpg',
        'genero' => 'Action RPG',
        'anio' => '2022',
        'puntuacion' => '4.8',
        'resumen' => 'Mundo abierto oscuro, combates exigentes y exploracion libre en las Tierras Intermedias.'
    ],
    'hollow-knight' => [
        'titulo' => 'Hollow Knight',
        'imagen' => 'media/portadaHollowKnight.jpg',
        'genero' => 'Metroidvania',
        'anio' => '2017',
        'puntuacion' => '4.9',
        'resumen' => 'Aventura 2D con exploracion, precision en combate y una atmosfera inolvidable.'
    ],
    'cyberpunk-2077' => [
        'titulo' => 'Cyberpunk 2077',
        'imagen' => 'media/portadaCyberpunk.jpg',
        'genero' => 'RPG',
        'anio' => '2020',
        'puntuacion' => '4.0',
        'resumen' => 'Accion narrativa en Night City con progresion de personaje y decisiones de historia.'
    ],
    'stardew-valley' => [
        'titulo' => 'Stardew Valley',
        'imagen' => 'media/portadaStardewValley.jpg',
        'genero' => 'Simulacion',
        'anio' => '2016',
        'puntuacion' => '4.9',
        'resumen' => 'Gestiona tu granja, conoce al pueblo y construye tu vida a tu ritmo.'
    ],
    'zelda-totk' => [
        'titulo' => 'Zelda: Tears of the Kingdom',
        'imagen' => 'media/portadaZeldaTOTK.jpg',
        'genero' => 'Aventura',
        'anio' => '2023',
        'puntuacion' => '4.7',
        'resumen' => 'Exploracion vertical y creatividad total en Hyrule con nuevas habilidades.'
    ],
    'watch-dogs' => [
        'titulo' => 'Watch Dogs',
        'imagen' => 'media/portadaWatchdogs.jpg',
        'genero' => 'Accion mundo abierto',
        'anio' => '2014',
        'puntuacion' => '4.5',
        'resumen' => 'Hackeo urbano, persecuciones y misiones de infiltracion en Chicago.'
    ]
];

$juego = $juegos[$slug] ?? null;
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
                <li><a href="#">Listas</a></li>
                <li><a href="#">Comunidades</a></li>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesion</a>
        <?php else: ?>
            <a class="tag" href="../../php/user/perfiles/perfilSesion.php"><?php echo $_SESSION['tag']; ?></a>
        <?php endif; ?>
    </header>

    <main>
        <?php if($juego): ?>
            <section class="fichaJuego">
                <div class="imagenJuego">
                    <img src="<?php echo $juego['imagen']; ?>" alt="Portada de <?php echo htmlspecialchars($juego['titulo']); ?>">
                </div>
                <div class="contenidoJuego">
                    <h1><?php echo htmlspecialchars($juego['titulo']); ?></h1>
                    <p class="meta"><?php echo htmlspecialchars($juego['genero']); ?> • <?php echo htmlspecialchars($juego['anio']); ?> • ★ <?php echo htmlspecialchars($juego['puntuacion']); ?></p>
                    <p><?php echo htmlspecialchars($juego['resumen']); ?></p>

                    <div class="bloqueProximamente">
                        <h2>Informacion detallada (proximamente)</h2>
                        <p>Esta ficha esta preparada para que luego conectes sin problema tus datos reales desde base de datos.</p>
                    </div>

                    <a href="juegos.php" class="botonVolver">Volver al catalogo</a>
                </div>
            </section>
        <?php else: ?>
            <section class="fichaJuego vacio">
                <h1>Juego no encontrado</h1>
                <p>La ficha solicitada no existe en el catalogo visual actual.</p>
                <a href="juegos.php" class="botonVolver">Ir a juegos</a>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>
</body>
</html>
