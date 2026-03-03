<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Juegos</title>
    <link rel="stylesheet" href="../../estilos/estilos_juegos.css">
    <link rel="icon" href="../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40">
            <a href="index.php" class="logo">Salsa<span>Box</span></a>
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
            <a href="php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesion</a>
        <?php else: ?>
            <a class="tag" href="../../php/user/perfiles/perfilSesion.php"><?php echo $_SESSION['tag']; ?></a>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Encuentra tu proxima aventura</h1>
        <p>Busca por nombre y descubre todos los videojuegos del catalogo visual de SalsaBox.</p>

        <div class="buscadorContainer">
            <input type="text" id="buscadorJuegos" placeholder="Buscar videojuego..." aria-label="Buscar videojuego">
        </div>
    </div>

    <main>
        <h2>Todos los videojuegos</h2>
        <div class="juegos" id="gridJuegos">
            <a class="juegoLink" href="juego.php?slug=elden-ring" data-titulo="elden ring">
                <article class="juego">
                    <div class="portadaJuego">
                        <img src="../../media/portadaEldenRing.jpg" alt="Portada de Elden Ring">
                    </div>
                    <div class="infoJuego">
                        <div class="tituloJuego">Elden Ring</div>
                        <div class="puntuacionJuego">★★★★★ 4.8</div>
                    </div>
                </article>
            </a>

            <a class="juegoLink" href="juego.php?slug=hollow-knight" data-titulo="hollow knight">
                <article class="juego">
                    <div class="portadaJuego">
                        <img src="../../media/portadaHollowKnight.jpg" alt="Portada de Hollow Knight">
                    </div>
                    <div class="infoJuego">
                        <div class="tituloJuego">Hollow Knight</div>
                        <div class="puntuacionJuego">★★★★★ 4.9</div>
                    </div>
                </article>
            </a>

            <a class="juegoLink" href="juego.php?slug=cyberpunk-2077" data-titulo="cyberpunk 2077">
                <article class="juego">
                    <div class="portadaJuego">
                        <img src="../../media/portadaCyberpunk.jpg" alt="Portada de Cyberpunk 2077">
                    </div>
                    <div class="infoJuego">
                        <div class="tituloJuego">Cyberpunk 2077</div>
                        <div class="puntuacionJuego">★★★★☆ 4.0</div>
                    </div>
                </article>
            </a>

            <a class="juegoLink" href="juego.php?slug=stardew-valley" data-titulo="stardew valley">
                <article class="juego">
                    <div class="portadaJuego">
                        <img src="../../media/portadaStardewValley.jpg" alt="Portada de Stardew Valley">
                    </div>
                    <div class="infoJuego">
                        <div class="tituloJuego">Stardew Valley</div>
                        <div class="puntuacionJuego">★★★★★ 4.9</div>
                    </div>
                </article>
            </a>

            <a class="juegoLink" href="juego.php?slug=zelda-totk" data-titulo="zelda tears of the kingdom">
                <article class="juego">
                    <div class="portadaJuego">
                        <img src="../../media/portadaZeldaTOTK.jpg" alt="Portada de Zelda: Tears of the Kingdom">
                    </div>
                    <div class="infoJuego">
                        <div class="tituloJuego">Zelda: Tears of the Kingdom</div>
                        <div class="puntuacionJuego">★★★★★ 4.7</div>
                    </div>
                </article>
            </a>

            <a class="juegoLink" href="juego.php?slug=watch-dogs" data-titulo="watch dogs">
                <article class="juego">
                    <div class="portadaJuego">
                        <img src="../../media/portadaWatchdogs.jpg" alt="Portada de Watch Dogs">
                    </div>
                    <div class="infoJuego">
                        <div class="tituloJuego">Watch Dogs</div>
                        <div class="puntuacionJuego">★★★★☆ 4.5</div>
                    </div>
                </article>
            </a>
        </div>

        <p id="sinResultados" class="sinResultados" hidden>No se encontraron juegos para esa busqueda.</p>
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
                    visibles += 1;
                }
            });

            sinResultados.hidden = visibles !== 0;
        });
    </script>
</body>
</html>
