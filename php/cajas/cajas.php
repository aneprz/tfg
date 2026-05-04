<?php
session_start();
require_once '../../db/conexiones.php';
// Si este archivo va a estar dentro de una carpeta (ej: php/cajas/cajas.php), 
// recuerda ajustar las rutas de los href y src añadiendo "../../"

$idUsuarioSesion = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;
$admin = ($_SESSION['admin'] ?? false) === true;

// Aquí más adelante haremos la consulta para sacar las cajas de la base de datos
// $sqlCajas = "SELECT * FROM lootboxes"; ...
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salsabox - Cajas Misteriosas</title>

    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="icon" href="../../media/logoPlatino.png">

    <style>
        /* ESTILOS ESPECÍFICOS PARA LAS CAJAS (Llévalo a tu CSS luego) */
        .seccion-cajas {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .grid-cajas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .caja-item {
            background-color: #1a1c23; /* Tono oscuro de fondo de carta */
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 1px solid #2d313d;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .caja-item:hover {
            transform: translateY(-5px);
        }

        /* Estilos por rareza */
        .caja-basica:hover { border-color: #aeb4c4; box-shadow: 0 5px 20px rgba(174, 180, 196, 0.2); }
        .caja-epica:hover { border-color: #c724b1; box-shadow: 0 5px 20px rgba(199, 36, 177, 0.3); }
        .caja-legendaria:hover { border-color: #f0c330; box-shadow: 0 5px 20px rgba(240, 195, 48, 0.4); }
        .caja-hueco {
            height: 150px; 
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        .caja-imagen {
            width: 280px;  /* Hazla todo lo grande que quieras aquí */
            height: 280px; /* Hazla todo lo grande que quieras aquí */
            object-fit: contain;
            filter: drop-shadow(0px 10px 10px rgba(0,0,0,0.5));
            transition: transform 0.3s;
        }

        .caja-item:hover .caja-imagen {
            transform: scale(1.05); /* Efecto al pasar el ratón */
        }

        .caja-titulo {
            font-size: 1.2rem;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .caja-precio {
            font-size: 1.1rem;
            color: #f0c330; /* El dorado de tu web */
            font-weight: bold;
            margin-bottom: 20px;
        }

        .boton-abrir {
            background-color: #f0c330;
            color: #111;
            border: none;
            padding: 10px 30px;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            text-transform: uppercase;
            transition: background-color 0.2s;
            width: 100%;
        }

        .boton-abrir:hover {
            background-color: #dcb028;
        }

        .caja-epica .boton-abrir { background-color: #c724b1; color: white; }
        .caja-epica .boton-abrir:hover { background-color: #a31d91; }

        /* Etiqueta de contenido */
        .ver-contenido {
            margin-top: 15px;
            font-size: 0.85rem;
            color: #888;
            cursor: pointer;
            text-decoration: underline;
        }
        .ver-contenido:hover {
            color: #fff;
        }
    </style>
</head>

<body>

<header>
    <div class="tituloWeb">
        <img src="../../media/logoPlatino.png" width="40" alt="Logo">
        <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
    </div>

    <nav>
        <ul>
            <li><a href="../../index.php">Inicio</a></li>
            <li><a href="../videojuegos/juegos.php">Juegos</a></li>
            <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
            <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
            <li><a href="cajas.php" class="activo">Cajas</a></li>
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
    <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
    <?php else: ?>
        <div class="user-actions">
             <div class="chat-wrapper" style="margin-right: 10px; display: inline-block; vertical-align: middle;">
                <a href="php/chat/bandeja.php" id="chat-icon" style="color: inherit; text-decoration: none; position: relative; display: flex; align-items: center;">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="26" height="26">
                        <path d="M12 2C6.477 2 2 6.14 2 11.25c0 2.457 1.047 4.675 2.75 6.275L4 21l3.75-1.5c1.33.4 2.76.625 4.25.625 5.523 0 10-4.14 10-9.25S17.523 2 12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span id="chat-badge" style="position: absolute; top: -8px; right: -10px; background: #f0c330; color: #000; border-radius: 50%; min-width: 18px; height: 18px; font-size: 10px; font-weight: bold; text-align: center; line-height: 18px; display: none; padding: 0 4px;">0</span>
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

            <a class="tag" href="php/user/perfiles/perfilSesion.php">
                <?php echo htmlspecialchars($_SESSION['tag']); ?>
            </a>
        </div>
    <?php endif; ?>
</header>

<div class="central">
    <h1>Cajas de Botín</h1>
    <p>
        Prueba tu suerte. Abre cajas misteriosas y consigue juegos, avatares exclusivos 
        o puntos extra para tu cuenta. ¿Te tocará el drop legendario?
    </p>
</div>

<main class="seccion-cajas">
    <h2>Colecciones Destacadas</h2>

    <div class="grid-cajas">
        
        <!-- CAJA BÁSICA -->
        <div class="caja-item caja-basica">
            <img src="../../media/caja_basica.png" alt="Caja Bronce" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja_indie.png'">
            <div class="caja-titulo">Caja Indie</div>
            <div class="caja-precio">150 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(1)">Abrir Caja</button>
            <div class="ver-contenido">Ver contenido posible</div>
        </div>

        <!-- CAJA ÉPICA -->
        <div class="caja-item caja-epica">
            <img src="../../media/caja_epica.png" alt="Caja Épica" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja-epica.png'">
            <div class="caja-titulo">Caja Triple A</div>
            <div class="caja-precio">500 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(2)">Abrir Caja</button>
            <div class="ver-contenido">Ver contenido posible</div>
        </div>

        <!-- CAJA LEGENDARIA -->
        <div class="caja-item caja-legendaria">
            <img src="../../media/caja_legendaria.png" alt="Caja Legendaria" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja-legendaria.png'">
            <div class="caja-titulo">Caja SalsaBox Pro</div>
            <div class="caja-precio">1200 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(3)">Abrir Caja</button>
            <div class="ver-contenido">Ver contenido posible</div>
        </div>

        <!-- CAJA TEMÁTICA -->
        <div class="caja-item caja-basica">
            <img src="../../media/caja_terror.png" alt="Caja Terror" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja-horror.png'">
            <div class="caja-titulo">Caja Survival Horror</div>
            <div class="caja-precio">300 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(4)">Abrir Caja</button>
            <div class="ver-contenido">Ver contenido posible</div>
        </div>

    </div>
</main>

<footer>
    <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
</footer>

<script>
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            nav.classList.toggle('open');
        });
    }

    // Función temporal para probar los botones
    function abrirCaja(idCaja) {
        <?php if (!isset($_SESSION['id_usuario'])): ?>
            alert("¡Debes iniciar sesión para abrir cajas!");
            window.location.href = "../sesiones/login/login.php";
            return;
        <?php endif; ?>
        
        // Aquí conectaremos con la animación de ruleta y el PHP por AJAX más adelante
        alert("Preparando ruleta para la caja ID: " + idCaja + "...");
    }
</script>

<script src="js/notificaciones.js"></script>
</body>
</html>