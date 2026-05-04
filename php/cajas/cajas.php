<?php
session_start();
require_once '../../db/conexiones.php';

$idUsuarioSesion = isset($_SESSION['id_usuario']) ? (int) $_SESSION['id_usuario'] : 0;
$admin = ($_SESSION['admin'] ?? false) === true;
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
        /* ESTILOS ESPECÍFICOS PARA LAS CAJAS */
        .seccion-cajas { padding: 40px 20px; max-width: 1200px; margin: 0 auto; }
        .grid-cajas { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 30px; margin-top: 30px; }
        .caja-item { background-color: #1a1c23; border-radius: 10px; padding: 20px; text-align: center; border: 1px solid #2d313d; transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; align-items: center; position: relative; overflow: hidden; }
        .caja-item:hover { transform: translateY(-5px); }
        .caja-basica:hover { border-color: #aeb4c4; box-shadow: 0 5px 20px rgba(174, 180, 196, 0.2); }
        .caja-epica:hover { border-color: #c724b1; box-shadow: 0 5px 20px rgba(199, 36, 177, 0.3); }
        .caja-legendaria:hover { border-color: #f0c330; box-shadow: 0 5px 20px rgba(240, 195, 48, 0.4); }
        .caja-horror:hover { border-color: #fd0000; box-shadow: 0 5px 20px rgba(174, 180, 196, 0.2); }
        .caja-hueco { height: 150px; width: 100%; display: flex; justify-content: center; align-items: center; margin-bottom: 20px; }
        .caja-imagen { width: 280px; height: 280px; object-fit: contain; filter: drop-shadow(0px 10px 10px rgba(0,0,0,0.5)); transition: transform 0.3s; }
        .caja-item:hover .caja-imagen { transform: scale(1.05); }
        .caja-titulo { font-size: 1.2rem; font-weight: bold; color: #fff; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .caja-precio { font-size: 1.1rem; color: #f0c330; font-weight: bold; margin-bottom: 20px; }
        .boton-abrir { background-color: #f0c330; color: #111; border: none; padding: 10px 30px; font-size: 1rem; font-weight: bold; border-radius: 5px; cursor: pointer; text-transform: uppercase; transition: background-color 0.2s; width: 100%; }
        .boton-abrir:hover { background-color: #dcb028; }
        .caja-epica .boton-abrir { background-color: #c724b1; color: white; }
        .caja-epica .boton-abrir:hover { background-color: #a31d91; }
        .ver-contenido { margin-top: 15px; font-size: 0.85rem; color: #888; cursor: pointer; text-decoration: underline; }
        .ver-contenido:hover { color: #fff; }

        /* NUEVOS ESTILOS DEL MODAL Y LA RULETA (CS:GO Style) */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .modal-box { background: #1a1c23; border: 2px solid #f0c330; border-radius: 10px; padding: 30px; text-align: center; color: white; box-shadow: 0 0 30px rgba(240, 195, 48, 0.3); }
        .modal-box h2 { margin-top: 0; color: #f0c330; }
        #btn-cerrar-modal { background: #f0c330; border: none; padding: 10px 20px; color: black; font-weight: bold; border-radius: 5px; cursor: pointer; margin-top: 15px; width: 100%; }
        
        .ruleta-box { width: 800px; max-width: 95%; }
        .ruleta-ventana { width: 100%; height: 160px; background: #111; border: 2px solid #2d313d; border-radius: 5px; position: relative; overflow: hidden; margin: 20px 0; box-shadow: inset 0 0 20px rgba(0,0,0,0.8); }
        .ruleta-selector { position: absolute; top: 0; bottom: 0; left: 50%; width: 4px; background: #ff4444; transform: translateX(-50%); z-index: 10; box-shadow: 0 0 10px #ff4444; }
        .ruleta-pista { display: flex; height: 100%; width: max-content; transition: transform 6s cubic-bezier(0.1, 0.9, 0.2, 1); transform: translateX(0); }
        .ruleta-item-track { width: 140px; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; border-right: 1px solid #333; background: linear-gradient(to bottom, #222, #1a1c23); flex-shrink: 0; }
        .ruleta-item-track img { width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px; }
        .ruleta-item-track span { font-size: 0.9rem; font-weight: bold; color: #aaa; }
        .premio-oro span { color: #f0c330; } 
        .mensaje-premio { font-size: 1.5rem; color: #f0c330; font-weight: bold; min-height: 35px; }
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
            <li><a href="../tienda/tienda.php">Tienda</a></li>
            <li><a href="cajas.php" class="activo">Cajas</a></li>
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

            <a class="tag" href="../user/perfiles/perfilSesion.php">
                <?php echo htmlspecialchars($_SESSION['tag']); ?>
            </a>
        </div>
    <?php endif; ?>
</header>

<div class="central">
    <h1>Cajas de Botín</h1>
    <p>Prueba tu suerte. Abre cajas misteriosas y consigue juegos, avatares exclusivos o puntos extra para tu cuenta. ¿Te tocará el drop legendario?</p>
</div>

<main class="seccion-cajas">
    <h2>Colecciones Destacadas</h2>

    <div class="grid-cajas">
        
        <!-- CAJA BÁSICA -->
        <div class="caja-item caja-basica">
            <div class="caja-hueco">
                <img src="../../media/caja_basica.png" alt="Caja Bronce" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja_indie.png'">
            </div>
            <div class="caja-titulo">Salsa Indie</div>
            <div class="caja-precio">150 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(1)">Abrir Caja</button>
            <div class="ver-contenido">Ver contenido posible</div>
        </div>

        <!-- CAJA ÉPICA -->
        <div class="caja-item caja-epica">
            <div class="caja-hueco">
                <img src="../../media/caja_epica.png" alt="Caja Épica" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja-epica.png'">
            </div>
            <div class="caja-titulo">Salsa Triple A</div>
            <div class="caja-precio">500 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(2)">Abrir Caja</button>
            <div class="ver-contenido">Ver contenido posible</div>
        </div>

        <!-- CAJA LEGENDARIA -->
        <div class="caja-item caja-legendaria">
            <div class="caja-hueco">
                <img src="../../media/caja_legendaria.png" alt="Caja Legendaria" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja-legendaria.png'">
            </div>
            <div class="caja-titulo">Salsa Goty</div>
            <div class="caja-precio">1200 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(3)">Abrir Caja</button>
            <div class="ver-contenido">Ver contenido posible</div>
        </div>

        <!-- CAJA TEMÁTICA -->
        <div class="caja-item caja-horror">
            <div class="caja-hueco">
                <img src="../../media/caja_terror.png" alt="Caja Terror" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja-horror.png'">
            </div>
            <div class="caja-titulo">Salsa Horror</div>
            <div class="caja-precio">300 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(4)">Abrir Caja</button>
            <div class="ver-contenido">Ver contenido posible</div>
        </div>

    </div>
</main>

<!-- NUEVO MODAL RULETA ESTILO CS:GO -->
<div id="modal-ruleta" class="modal-overlay" style="display: none;">
    <div class="modal-box ruleta-box">
        <h2 id="ruleta-titulo">Abriendo bote...</h2>
        
        <div class="ruleta-ventana" id="ruleta-ventana">
            <div class="ruleta-selector"></div>
            <div class="ruleta-pista" id="ruleta-pista"></div>
        </div>

        <p id="ruleta-mensaje" class="mensaje-premio"></p>
        <p id="ruleta-saldo"></p>
        <button id="btn-cerrar-modal" style="display: none;" onclick="cerrarRuleta()">Cerrar y recoger</button>
    </div>
</div>

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

    // EL NUEVO MOTOR JAVASCRIPT DE LA RULETA
    function abrirCaja(idCaja) {
        <?php if (!isset($_SESSION['id_usuario'])): ?>
            alert("¡Debes iniciar sesión para abrir cajas!");
            window.location.href = "../sesiones/login/login.php";
            return;
        <?php endif; ?>

        const modal = document.getElementById('modal-ruleta');
        const pista = document.getElementById('ruleta-pista');
        const titulo = document.getElementById('ruleta-titulo');
        const mensaje = document.getElementById('ruleta-mensaje');
        const saldo = document.getElementById('ruleta-saldo');
        const btnCerrar = document.getElementById('btn-cerrar-modal');

        modal.style.display = 'flex';
        titulo.innerText = "Girando...";
        mensaje.innerText = "";
        saldo.innerText = "";
        btnCerrar.style.display = 'none';
        
        pista.style.transition = 'none';
        pista.style.transform = 'translateX(0)';
        pista.innerHTML = '';

        const formData = new FormData();
        formData.append('id_caja', idCaja);

        fetch('abrir_caja_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const totalItems = 70;
                const indexGanador = 60; 
                const anchoItem = 140; 

                for (let i = 0; i < totalItems; i++) {
                    let div = document.createElement('div');
                    div.className = 'ruleta-item-track';
                    
                    if (i === indexGanador) {
                        div.classList.add('premio-oro');
                        div.innerHTML = `<img src="../../media/logoPlatino.png"><span>PREMIO</span>`;
                    } else {
                        let esJuego = Math.random() > 0.8;
                        div.innerHTML = esJuego 
                            ? `<img src="../../media/bote_indie.png" style="filter: grayscale(100%); opacity: 0.5;"><span>Juego</span>` 
                            : `<img src="../../media/logoPlatino.png" style="filter: grayscale(100%); opacity: 0.5;"><span>Puntos</span>`;
                    }
                    pista.appendChild(div);
                }

                pista.offsetHeight; 

                const anchoVentana = document.getElementById('ruleta-ventana').clientWidth;
                let distancia = (indexGanador * anchoItem); 
                distancia = distancia - (anchoVentana / 2);
                distancia = distancia + (anchoItem / 2);
                
                const randomOffset = Math.floor(Math.random() * 100) - 50;
                distancia = distancia + randomOffset;

                pista.style.transition = 'transform 6s cubic-bezier(0.1, 0.9, 0.2, 1)';
                pista.style.transform = `translateX(-${distancia}px)`;

                setTimeout(() => {
                    titulo.innerText = "¡Resultado!";
                    mensaje.innerText = data.mensaje;
                    saldo.innerText = "Tu nuevo saldo: " + data.nuevo_saldo + " Puntos";
                    btnCerrar.style.display = 'block';
                }, 6000); 

            } else {
                titulo.innerText = "Error";
                mensaje.innerText = data.mensaje;
                btnCerrar.style.display = 'block';
            }
        })
        .catch(error => {
            console.error("Error:", error);
            titulo.innerText = "Error de conexión";
            btnCerrar.style.display = 'block';
        });
    }

    function cerrarRuleta() {
        document.getElementById('modal-ruleta').style.display = 'none';
        location.reload(); 
    }
</script>

<script src="js/notificaciones.js"></script>
</body>
</html>