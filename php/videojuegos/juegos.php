<?php
session_start();
require '../../db/conexiones.php';

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

</header>


<div class="central">

    <h1>Encuentra tu próxima aventura</h1>

    <p>
        Busca por nombre y descubre todos los videojuegos del catálogo visual de SalsaBox.
    </p>

    <br>

    <div class="buscadorContainer">

        <input
            type="text"
            id="buscadorJuegos"
            placeholder="Buscar videojuego..."
            aria-label="Buscar videojuego"
        >

    </div>

    <br>

    <div class="filtrosContainer">

        <select id="ordenJuegos">

            <option value="nombre_asc">Nombre A → Z</option>
            <option value="nombre_desc">Nombre Z → A</option>

            <option value="nota_desc">Mejor puntuados</option>
            <option value="nota_asc">Peor puntuados</option>

            <option value="fecha_desc">Más recientes</option>
            <option value="fecha_asc">Más antiguos</option>

        </select>

    </div>

</div>


<main>

    <h2>Todos los videojuegos</h2>

    <div class="juegos" id="gridJuegos"></div>

    <p id="sinResultados" class="sinResultados" hidden>
        No se encontraron juegos para esa búsqueda.
    </p>

    <div class="paginacion" id="paginacion"></div>

</main>


<footer>
    <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
</footer>


<script>

document.addEventListener("DOMContentLoaded", () => {

    const buscador = document.getElementById("buscadorJuegos");
    const orden = document.getElementById("ordenJuegos");

    const grid = document.getElementById("gridJuegos");
    const paginacion = document.getElementById("paginacion");
    const sinResultados = document.getElementById("sinResultados");

    let pagina = 1;

    function cargarJuegos(){

        const texto = buscador.value;
        const ordenValor = orden.value;

        fetch(`procesarJuegos.php?buscar=${encodeURIComponent(texto)}&orden=${ordenValor}&pagina=${pagina}`)

            .then(res => res.json())

            .then(data => {

                grid.innerHTML = data.html;
                paginacion.innerHTML = data.paginacion;

                sinResultados.hidden = data.total > 0;

                document.querySelectorAll(".pag-btn").forEach(btn => {

                    btn.addEventListener("click", () => {

                        pagina = btn.dataset.pagina;
                        cargarJuegos();

                        window.scrollTo({
                            top: 0,
                            behavior: "smooth"
                        });

                    });

                });

            });

    }

    buscador.addEventListener("input", () => {

        pagina = 1;
        cargarJuegos();

    });

    orden.addEventListener("change", () => {

        pagina = 1;
        cargarJuegos();

    });

    cargarJuegos();

});

</script>
<script src="../../js/notificaciones.js"></script>

</body>
</html>