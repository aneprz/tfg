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
            <li><a href="../tienda/tienda.php">Tienda</a></li>
            <li><a href="../logros/logros.php">Logros</a></li>
            <li><a href="../ranking/ranking.php">Ranking</a></li>
            <?php if ($admin): ?>
                <li><a href="../admin/indexAdmin.php">Admin</a></li>
            <?php endif; ?>

        </ul>
    </nav>

    <button class="menu-toggle" aria-label="Menú">☰</button>

    <?php if (!isset($_SESSION['tag'])): ?>

        <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">
            Iniciar sesión
        </a>

    <?php else: ?>
        <div class="user-actions">
            <!-- CHATS -->
             <div class="chat-wrapper" style="margin-right: 10px; display: inline-block; vertical-align: middle;">
                <a href="../chat/bandeja.php" id="chat-icon" style="color: inherit; text-decoration: none; position: relative; display: flex; align-items: center;">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="26" height="26">
                        <path d="M12 2C6.477 2 2 6.14 2 11.25c0 2.457 1.047 4.675 2.75 6.275L4 21l3.75-1.5c1.33.4 2.76.625 4.25.625 5.523 0 10-4.14 10-9.25S17.523 2 12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span id="chat-badge" style="
                        position: absolute;
                        top: -5px;
                        right: -5px;
                        background-color: #ff4444;
                        color: white;
                        font-size: 10px;
                        font-weight: bold;
                        padding: 2px 5px;
                        border-radius: 10px;
                        display: none;
                    ">0</span>
                </a>
            </div>
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

    <!-- Botón Biblioteca -->
    <button id="btn-biblioteca" class="btn-biblioteca" style="background: #f0c330; color: #000; border: none; padding: 8px 15px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-right: 10px; margin-top: 30px;">
        Ver Mi Biblioteca
    </button>

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

<script>
    // Menú hamburguesa
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('nav');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                nav.classList.toggle('open');
                console.log('Menú clickeado'); // Para verificar que funciona
            });
        }
    });
</script>

<script src="../../js/notificaciones.js"></script>
<!-- Modal Biblioteca -->
<div id="modal-biblioteca" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #1a1a1a; padding: 25px; border-radius: 15px; width: 100%; max-width: 800px; max-height: 90vh; overflow-y: auto; border: 1px solid #f0c330;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #f0c330; margin: 0;">📚 Mi Biblioteca</h2>
            <button id="cerrar-modal" style="background: none; border: none; color: #f0c330; font-size: 24px; cursor: pointer;">✖</button>
        </div>
        <div id="contenido-biblioteca" style="color: white;">
            <p style="text-align: center; padding: 40px;">Cargando tus juegos...</p>
        </div>
    </div>
</div>

<script>
// Modal biblioteca
const modalBiblioteca = document.getElementById('modal-biblioteca');
const btnBiblioteca = document.getElementById('btn-biblioteca');
const cerrarModal = document.getElementById('cerrar-modal');

if (btnBiblioteca) {
    btnBiblioteca.addEventListener('click', function() {
        modalBiblioteca.style.display = 'flex';
        cargarBiblioteca();
    });
}

if (cerrarModal) {
    cerrarModal.addEventListener('click', function() {
        modalBiblioteca.style.display = 'none';
    });
}

// Cerrar al hacer clic fuera
window.addEventListener('click', function(e) {
    if (e.target === modalBiblioteca) {
        modalBiblioteca.style.display = 'none';
    }
});

function cargarBiblioteca() {
    const contenido = document.getElementById('contenido-biblioteca');
    contenido.innerHTML = '<p style="text-align: center; padding: 40px;">Cargando tus juegos...</p>';
    
    fetch('obtener_biblioteca.php')
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                contenido.innerHTML = '<p style="text-align: center; color: #ff6666;">Error al cargar tu biblioteca</p>';
                return;
            }
            
            if (data.length === 0) {
                contenido.innerHTML = '<p style="text-align: center; color: #888;">No tienes juegos en tu biblioteca. ¡Explora y añade algunos!</p>';
                return;
            }
            
            let html = `
                <table>
                    <thead>
                        <tr>
                            <th>Portada</th>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Puntuación</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.forEach(juego => {
                const urlJuego = `juego.php?id=${juego.id_videojuego}`;
                const puntuacion5 = juego.puntuacion ? (juego.puntuacion / 2) : 0;
                const porcentaje = juego.puntuacion ? (juego.puntuacion / 10) * 100 : 0;
                
                html += `
                    <tr data-href="${urlJuego}" style="cursor: pointer;">
                        <td>
                            <img src="../../media/${juego.portada}" class="portada" alt="${juego.titulo}">
                        </td>
                        <td style="font-weight: bold; color: #f0c330;">
                            ${juego.titulo}
                        </td>
                        <td>
                            <span class="status-badge ${juego.estado}">${juego.estado}</span>
                        </td>
                        <td>
                            ${juego.puntuacion ? `
                                <span class="estrellas">
                                    <span class="relleno" style="width: ${porcentaje}%"></span>
                                </span>
                            ` : 'Sin puntuar'}
                        </td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            contenido.innerHTML = html;
            
            // Añadir evento de clic a las filas para ir al juego
            document.querySelectorAll('#contenido-biblioteca tr[data-href]').forEach(function(row) {
                row.addEventListener('click', function(e) {
                    if (e.target && e.target.closest && e.target.closest('a, button, input, textarea, select, label')) {
                        return;
                    }
                    window.location.href = row.getAttribute('data-href');
                });
            });
        })
        .catch(err => {
            console.error('Error:', err);
            contenido.innerHTML = '<p style="text-align: center; color: #ff6666;">Error al cargar tu biblioteca</p>';
        });
}
</script>
</body>
</html>