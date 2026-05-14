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

    <style>
        /* Estilos específicos para el modal de biblioteca */
        .modal-biblioteca-contenedor {
            background: #1a1a1a;
            padding: 25px;
            border-radius: 15px;
            width: 100%;
            max-width: 900px;
            max-height: 85vh;
            overflow-y: auto;
            border: 1px solid #f0c330;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0c330;
        }

        .modal-header h2 {
            color: #f0c330;
            margin: 0;
            font-size: 1.8rem;
        }

        .cerrar-modal-btn {
            background: none;
            border: none;
            color: #f0c330;
            font-size: 28px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .cerrar-modal-btn:hover {
            transform: scale(1.1);
        }

        /* Estilos para la tabla */
        .biblioteca-tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .biblioteca-tabla thead tr {
            background: #2a2a2a;
            border-bottom: 2px solid #f0c330;
        }

        .biblioteca-tabla th {
            padding: 12px 15px;
            text-align: left;
            color: #f0c330;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .biblioteca-tabla td {
            padding: 12px 15px;
            border-bottom: 1px solid #333;
            color: #ddd;
            vertical-align: middle;
        }

        .biblioteca-tabla tr:hover {
            background: #252525;
            cursor: pointer;
        }

        /* Badges de estado */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            min-width: 100px;
        }

        .status-badge.Jugando {
            background: #2e7d32;
            color: white;
        }

        .status-badge.Completado {
            background: #1565c0;
            color: white;
        }

        .status-badge.Pendiente {
            background: #ff8f00;
            color: white;
        }

        .status-badge.Abandonado {
            background: #c62828;
            color: white;
        }

        /* Estrellas */
        .estrellas {
            display: inline-block;
            position: relative;
            font-size: 16px;
            font-family: Arial, sans-serif;
        }

        .estrellas::before {
            content: "★★★★★";
            letter-spacing: 3px;
            color: #444;
        }

        .estrellas .relleno {
            position: absolute;
            top: 0;
            left: 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .estrellas .relleno::before {
            content: "★★★★★";
            letter-spacing: 3px;
            color: #f0c330;
        }

        .sin-puntuacion {
            color: #888;
            font-size: 0.85rem;
        }

        /* Mensaje vacío */
        .biblioteca-vacia {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .biblioteca-vacia p {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .biblioteca-vacia .enlace-explorar {
            color: #f0c330;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 20px;
            border: 1px solid #f0c330;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .biblioteca-vacia .enlace-explorar:hover {
            background: #f0c330;
            color: #1a1a1a;
        }

        /* Mensaje de error */
        .biblioteca-error {
            text-align: center;
            padding: 40px;
            color: #ff6666;
        }

        /* Scroll personalizado para el modal */
        .modal-biblioteca-contenedor::-webkit-scrollbar {
            width: 8px;
        }

        .modal-biblioteca-contenedor::-webkit-scrollbar-track {
            background: #2a2a2a;
            border-radius: 10px;
        }

        .modal-biblioteca-contenedor::-webkit-scrollbar-thumb {
            background: #f0c330;
            border-radius: 10px;
        }

        /* Responsive para móviles */
        @media (max-width: 768px) {
            .modal-biblioteca-contenedor {
                padding: 15px;
                max-width: 95%;
            }

            .modal-header h2 {
                font-size: 1.4rem;
            }

            .biblioteca-tabla th,
            .biblioteca-tabla td {
                padding: 10px 8px;
                font-size: 0.85rem;
            }

            .biblioteca-tabla td:nth-child(1),
            .biblioteca-tabla th:nth-child(1) {
                display: none;
            }

            .status-badge {
                min-width: 80px;
                font-size: 0.7rem;
                padding: 3px 8px;
            }

            .estrellas {
                font-size: 12px;
            }

            .estrellas .relleno::before,
            .estrellas::before {
                letter-spacing: 2px;
            }
        }

        @media (max-width: 480px) {
            .biblioteca-tabla th,
            .biblioteca-tabla td {
                font-size: 0.75rem;
                padding: 8px 5px;
            }

            .status-badge {
                min-width: 65px;
                font-size: 0.65rem;
            }
        }
    </style>

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
                    <span id="chat-badge" style="position: absolute; top: -5px; right: -5px; background-color: #ff4444; color: white; font-size: 10px; font-weight: bold; padding: 2px 5px; border-radius: 10px; display: none;">0</span>
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
    <p>Busca por nombre y descubre todos los videojuegos del catálogo visual de SalsaBox.</p>
    <br>
    <div class="buscadorContainer">
        <input type="text" id="buscadorJuegos" placeholder="Buscar videojuego..." aria-label="Buscar videojuego">
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
    <button id="btn-biblioteca" class="btn-biblioteca" style="background: #f0c330; color: #000; border: none; padding: 8px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 30px;">
        Ver Mi Biblioteca
    </button>
</div>

<main>
    <h2>Todos los videojuegos</h2>
    <div class="juegos" id="gridJuegos"></div>
    <p id="sinResultados" class="sinResultados" hidden>No se encontraron juegos para esa búsqueda.</p>
    <div class="paginacion" id="paginacion"></div>
</main>

<footer>
    <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
</footer>

<!-- Modal Biblioteca -->
<div id="modal-biblioteca" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.95); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
    <div class="modal-biblioteca-contenedor">
        <div class="modal-header">
            <h2>📚 Mi Biblioteca</h2>
            <button id="cerrar-modal" class="cerrar-modal-btn">✖</button>
        </div>
        <div id="contenido-biblioteca">
            <div class="biblioteca-vacia">
                <p>Cargando tus juegos...</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const buscador = document.getElementById("buscadorJuegos");
    const orden = document.getElementById("ordenJuegos");
    const grid = document.getElementById("gridJuegos");
    const paginacion = document.getElementById("paginacion");
    const sinResultados = document.getElementById("sinResultados");
    let pagina = 1;

    function cargarJuegos() {
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
                        window.scrollTo({ top: 0, behavior: "smooth" });
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

// Menú hamburguesa
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            nav.classList.toggle('open');
        });
    }
});
</script>

<script src="../../js/notificaciones.js"></script>

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

window.addEventListener('click', function(e) {
    if (e.target === modalBiblioteca) {
        modalBiblioteca.style.display = 'none';
    }
});

function cargarBiblioteca() {
    const contenido = document.getElementById('contenido-biblioteca');
    contenido.innerHTML = '<div class="biblioteca-vacia"><p>Cargando tus juegos...</p></div>';
    
    fetch('obtener_biblioteca.php')
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                contenido.innerHTML = '<div class="biblioteca-error">❌ Error al cargar tu biblioteca</div>';
                return;
            }
            
            if (data.length === 0) {
                contenido.innerHTML = `
                    <div class="biblioteca-vacia">
                        <p>📖 No tienes juegos en tu biblioteca.</p>
                        <a href="juegos.php" class="enlace-explorar">Explorar juegos</a>
                    </div>
                `;
                return;
            }
            
            let html = `
                <table class="biblioteca-tabla">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Mi puntuación</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            data.forEach(juego => {
                const urlJuego = `juego.php?id=${juego.id_videojuego}`;
                const porcentaje = juego.puntuacion ? (juego.puntuacion / 10) * 100 : 0;
                
                html += `
                    <tr data-href="${urlJuego}">
                        <td style="font-weight: bold; color: #f0c330;">${escapeHtml(juego.titulo)}</td>
                        <td>
                            <span class="status-badge ${escapeHtml(juego.estado)}">${escapeHtml(juego.estado)}</span>
                        </td>
                        <td>
                            ${juego.puntuacion ? `
                                <div class="estrellas">
                                    <span class="relleno" style="width: ${porcentaje}%"></span>
                                </div>
                                <span style="font-size: 0.75rem; color: #888; margin-left: 5px;">(${juego.puntuacion}/10)</span>
                            ` : '<span class="sin-puntuacion">Sin puntuar</span>'}
                        </td>
                    </tr>
                `;
            });
            
            html += `
                    </tbody>
                </table>
            `;
            
            contenido.innerHTML = html;
            
            document.querySelectorAll('#contenido-biblioteca tr[data-href]').forEach(function(row) {
                row.addEventListener('click', function(e) {
                    if (e.target && (e.target.closest('a') || e.target.closest('button') || 
                        e.target.closest('input') || e.target.closest('textarea') || 
                        e.target.closest('select') || e.target.closest('label'))) {
                        return;
                    }
                    window.location.href = row.getAttribute('data-href');
                });
            });
        })
        .catch(err => {
            console.error('Error:', err);
            contenido.innerHTML = '<div class="biblioteca-error">❌ Error al cargar tu biblioteca</div>';
        });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}
</script>

</body>
</html>