<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

// Verificamos sesión para seguridad
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Logros</title>
    <link rel="stylesheet" href="../../estilos/estilos_logros.css">
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
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
                <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="logros.php" class="activo">Logros</a></li>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Logros</h1>
        <p>Aquí podrás ver los logros que podrás ir ganando a lo largo de tu experiencia como gamer.</p>
        
        <div class="buscadorContainer">
            <input type="text" id="input-busqueda" placeholder="Buscar por nombre de logro o videojuego..." aria-label="Buscar logro">
        </div>
    </div>

    <main>
        <h2>Galería de Retos</h2>
        <div class="logros-grid" id="contenedor-logros">
            </div>
        <p id="sinResultados" class="sinResultados" hidden style="color: white; text-align: center; margin-top: 20px;">
            No se encontraron logros que coincidan con tu búsqueda.
        </p>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const inputBusqueda = document.getElementById('input-busqueda');
        const contenedor = document.getElementById('contenedor-logros');
        const sinResultados = document.getElementById('sinResultados');

        const fetchLogros = () => {
            const texto = inputBusqueda.value;
            fetch(`procesarLogros.php?buscar=${encodeURIComponent(texto)}&ajax=true`)
                .then(response => response.text())
                .then(html => {
                    contenedor.innerHTML = html;
                    // Si el servidor devuelve el aviso de no resultados o está vacío
                    if (html.includes('no-results') || html.trim() === "") {
                        sinResultados.hidden = false;
                    } else {
                        sinResultados.hidden = true;
                    }
                })
                .catch(error => console.error('Error:', error));
        };

        inputBusqueda.addEventListener('input', fetchLogros);
        fetchLogros(); // Carga inicial
    });
    </script>
</body>
</html>