<?php
session_start();
require 'procesarJugadores.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Comunidad de Jugadores</title>
    <link rel="stylesheet" href="../../estilos/estilos_juegos.css">
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_jugadores.css">
    <link rel="icon" href="../../media/logoPlatino.png">
    <style>
        .user-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
    </style>
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
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="jugadores.php" class="activo">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Explora la comunidad</h1>
        <p>Encuentra a otros gamers, revisa sus perfiles y descubre nuevas amistades dentro de SalsaBox.</p><br>
        
        <div class="buscadorContainer">
            <input type="text" id="input-busqueda" placeholder="Buscar por GameTag..." aria-label="Buscar jugador">
        </div>
    </div>

    <main>
        <h2>Todos los jugadores</h2>
        <div class="user-grid" id="contenedor-resultados">
            </div>
        <p id="sinResultados" class="sinResultados" hidden>No se encontraron jugadores para esa búsqueda.</p>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputBusqueda = document.getElementById('input-busqueda');
        const contenedor = document.getElementById('contenedor-resultados');
        const sinResultados = document.getElementById('sinResultados');

        const fetchJugadores = () => {
            const texto = inputBusqueda.value;
            fetch(`procesarJugadores.php?buscar=${encodeURIComponent(texto)}&ajax=true`)
                .then(response => response.text())
                .then(html => {
                    contenedor.innerHTML = html;
                    if (html.includes('no-results') || html.trim() === "") {
                        sinResultados.hidden = false;
                    } else {
                        sinResultados.hidden = true;
                    }
                })
                .catch(error => console.error('Error:', error));
        };

        inputBusqueda.addEventListener('input', fetchJugadores);
        fetchJugadores(); 
    });
    </script>
</body>
</html>