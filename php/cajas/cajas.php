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
        /* RAREZAS DE LA RULETA */
        .rareza-gris { border-bottom: 4px solid #888; box-shadow: inset 0 -20px 20px -20px rgba(136,136,136,0.8); }
        .rareza-azul { border-bottom: 4px solid #4aa3f0; box-shadow: inset 0 -20px 20px -20px rgba(74,163,240,0.8); }
        .rareza-morado { border-bottom: 4px solid #c724b1; box-shadow: inset 0 -20px 20px -20px rgba(199,36,177,0.8); }
        .rareza-dorado { border-bottom: 4px solid #f0c330; box-shadow: inset 0 -20px 20px -20px rgba(240,195,48,0.8); }
        .rareza-dorado span { color: #f0c330 !important; text-shadow: 0 0 5px rgba(240,195,48,0.5); }
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
            <div class="ver-contenido" onclick="verProbabilidades(1)" style="cursor: pointer;">Ver contenido posible</div>
        </div>

        <!-- CAJA ÉPICA -->
        <div class="caja-item caja-epica">
            <div class="caja-hueco">
                <img src="../../media/caja_epica.png" alt="Caja Épica" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja-epica.png'">
            </div>
            <div class="caja-titulo">Salsa Triple A</div>
            <div class="caja-precio">500 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(2)">Abrir Caja</button>
            <div class="ver-contenido" onclick="verProbabilidades(2)" style="cursor: pointer;">Ver contenido posible</div>
        </div>

        <!-- CAJA LEGENDARIA -->
        <div class="caja-item caja-legendaria">
            <div class="caja-hueco">
                <img src="../../media/caja_legendaria.png" alt="Caja Legendaria" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja-legendaria.png'">
            </div>
            <div class="caja-titulo">Salsa Goty</div>
            <div class="caja-precio">1200 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(3)">Abrir Caja</button>
            <div class="ver-contenido" onclick="verProbabilidades(3)" style="cursor: pointer;">Ver contenido posible</div>
        </div>

       <!-- CAJA DE MARCOS -->
        <div class="caja-item caja-marcos">
            <div class="caja-hueco">
                <img src="../../media/caja_marcos.png" alt="Caja Marcos" class="caja-imagen" onerror="this.onerror=null; this.src='../../media/caja_indie.png'">
            </div>
            <div class="caja-titulo">Salsa Enmarcada</div>
            <div class="caja-precio">300 Puntos</div>
            <button class="boton-abrir" onclick="abrirCaja(4)">Abrir Caja</button>
            <div class="ver-contenido" onclick="verProbabilidades(4)" style="cursor: pointer;">Ver contenido posible</div>
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
<!-- MODAL PROBABILIDADES -->
<div id="modal-probabilidades" class="modal-overlay" style="display: none;">
    <div class="modal-box" style="width: 500px;">
        <h2>Contenido de la Caja</h2>
        <p style="color: #aaa; margin-bottom: 20px;">Probabilidades auditadas y garantizadas.</p>
        
        <ul id="lista-probabilidades" style="list-style: none; padding: 0; margin: 0; text-align: left;">
            <!-- Aquí se inyectarán los premios por JavaScript -->
        </ul>

        <button id="btn-cerrar-prob" style="background: #333; color: white; margin-top: 20px;" onclick="cerrarProbabilidades()">Cerrar</button>
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

                // Lógica inteligente de colores según la caja a la que juegues
                // Lógica inteligente de colores según la caja a la que juegues
                let claseGanador = 'rareza-gris'; 
                if (data.tipo_premio === 'avatar' || data.tipo_premio === 'marco') {
                    claseGanador = 'rareza-morado';
                } else if (data.tipo_premio === 'puntos') {
                    if (idCaja == 3) { // LÍMITES GOTY
                        if (data.puntos_premio <= 500) claseGanador = 'rareza-gris';
                        else if (data.puntos_premio <= 3000) claseGanador = 'rareza-azul';
                        else claseGanador = 'rareza-dorado';
                    } else if (idCaja == 2) { // LÍMITES TRIPLE A
                        if (data.puntos_premio <= 200) claseGanador = 'rareza-gris';
                        else if (data.puntos_premio <= 1000) claseGanador = 'rareza-azul';
                        else claseGanador = 'rareza-dorado';
                    } else { // LÍMITES INDIE (Por defecto)
                        if (data.puntos_premio <= 100) claseGanador = 'rareza-gris';
                        else if (data.puntos_premio <= 500) claseGanador = 'rareza-azul';
                        else claseGanador = 'rareza-dorado';
                    }
                }

                // Aseguramos imagen ganadora
                let imgGanador = data.imagen_premio ? '../../media/' + data.imagen_premio : '../../media/logoPlatino.png';
                
                // AQUÍ EL CAMBIO: Si son puntos, forzamos que el texto sea el número. Si es otra cosa, su nombre real.
                let textoGanador = data.tipo_premio === 'puntos' ? data.puntos_premio + ' Puntos' : data.nombre_premio;

                for (let i = 0; i < totalItems; i++) {
                    let div = document.createElement('div');
                    
                    if (i === indexGanador) {
                        div.className = `ruleta-item-track ${claseGanador}`;
                        // Inyectamos el texto limpio
                        div.innerHTML = `<img src="${imgGanador}"><span>${textoGanador}</span>`;
                    } else {
                        // RELLENO VISUAL (El teatro de la ruleta)
                        let random = Math.random() * 100;
                        let claseFalsa, imgFalsa, txtFalso;
                        if (idCaja == 4) { // SI ES LA DE MARCOS
                            claseFalsa = 'rareza-morado'; 
                            // Como tienes 54 marcos y se llaman 1.png, 2.png, etc...
                            let randomImg = Math.floor(Math.random() * 54) + 1; 
                            imgFalsa = '../../media/marcos/' + randomImg + '.png'; 
                            txtFalso = 'Marco Exclusivo';
                        } else if (idCaja == 3) { // SI ES LA GOTY
                            if (random < 50) { 
                                claseFalsa = 'rareza-gris'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '400 Puntos'; 
                            } else if (random < 80) { 
                                claseFalsa = 'rareza-azul'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '1800 Puntos'; 
                            } else if (random < 98) { 
                                claseFalsa = 'rareza-morado'; 
                                let randomImg = Math.floor(Math.random() * 5) + 1; 
                                imgFalsa = '../../media/premiosGoty/' + randomImg + '.png'; 
                                txtFalso = 'Avatar LEGENDARIO';
                            } else { 
                                claseFalsa = 'rareza-dorado'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '10000 Pts'; 
                            }
                        } else if (idCaja == 2) { // SI ES LA TRIPLE A
                            if (random < 50) { 
                                claseFalsa = 'rareza-gris'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '200 Puntos'; 
                            } else if (random < 80) { 
                                claseFalsa = 'rareza-azul'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '750 Puntos'; 
                            } else if (random < 98) { 
                                claseFalsa = 'rareza-morado'; 
                                let randomImg = Math.floor(Math.random() * 5) + 1; // Del 1 al 5
                                imgFalsa = '../../media/premiosTripleA/' + randomImg + '.png'; 
                                txtFalso = 'Avatar ÉPICO';
                            } else { 
                                claseFalsa = 'rareza-dorado'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '3000 Puntos'; 
                            }
                        } else { 
                            if (random < 50) { 
                                claseFalsa = 'rareza-gris'; 
                                imgFalsa = '../../media/logoPlatino.png'; 
                                txtFalso = '50 Puntos'; 
                            } else if (random < 80) { 
                                claseFalsa = 'rareza-azul'; 
                                imgFalsa = '../../media/logoPlatino.png'; 
                                txtFalso = '250 Puntos'; 
                            } else if (random < 98) { 
                                claseFalsa = 'rareza-morado'; 
                                let randomImg = Math.floor(Math.random() * 3) + 1; // Del 1 al 3
                                imgFalsa = '../../media/premiosIndie/' + randomImg + '.png'; 
                                txtFalso = 'Avatar Exclusivo';
                            } else { 
                                claseFalsa = 'rareza-dorado'; 
                                imgFalsa = '../../media/logoPlatino.png'; 
                                txtFalso = '1200 Puntos'; 
                            }
                        } // <--- ESTA ES LA LLAVE QUE TE FALTABA CERRAR AQUÍ

                        // Esto tiene que estar FUERA del if/else para que se aplique a TODAS las cajas
                        div.className = `ruleta-item-track ${claseFalsa}`;
                        div.innerHTML = `<img src="${imgFalsa}" style="opacity: 0.7;"><span>${txtFalso}</span>`;
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
                    // Mensaje final también con el texto limpio
                    mensaje.innerText = `¡Has conseguido: ${textoGanador}!`;
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
function verProbabilidades(idCaja) {
        const modal = document.getElementById('modal-probabilidades');
        const lista = document.getElementById('lista-probabilidades');
        
        modal.style.display = 'flex';
        lista.innerHTML = '<p style="text-align: center;">Cargando probabilidades...</p>';

        fetch('ver_probabilidades_ajax.php?id_caja=' + idCaja)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                lista.innerHTML = ''; 
                
                data.premios.forEach(premio => {
                    // AQUÍ EL CAMBIO PARA EL MODAL: Muestra directamente la cantidad de puntos
                    let nombre = premio.tipo_premio === 'puntos' ? premio.puntos_premio + ' Puntos' : premio.nombre_premio;

                    let rutaImagen = premio.imagen_premio ? '../../media/' + premio.imagen_premio : '../../media/logoPlatino.png';
                    
                    // Colores en la lista de probabilidades
                    let colorRareza = '#888'; // Gris
                    if (premio.tipo_premio === 'avatar' || premio.tipo_premio === 'marco') {
                        colorRareza = '#c724b1'; // Morado
                    } else if (premio.tipo_premio === 'puntos') {
                        if (idCaja == 3) { // GOTY
                            if (premio.puntos_premio > 500 && premio.puntos_premio <= 3000) colorRareza = '#4aa3f0'; 
                            else if (premio.puntos_premio > 3000) colorRareza = '#f0c330'; 
                        } else if (idCaja == 2) { // TRIPLE A
                            if (premio.puntos_premio > 200 && premio.puntos_premio <= 1000) colorRareza = '#4aa3f0'; // Azul
                            else if (premio.puntos_premio > 1000) colorRareza = '#f0c330'; // Dorado
                        } else { // INDIE
                            if (premio.puntos_premio > 100 && premio.puntos_premio <= 500) colorRareza = '#4aa3f0'; // Azul
                            else if (premio.puntos_premio > 500) colorRareza = '#f0c330'; // Dorado
                        }
                    }

                    lista.innerHTML += `
                        <li style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #333;">
                            <div style="display: flex; align-items: center;">
                                <img src="${rutaImagen}" style="width: 40px; height: 40px; object-fit: contain; margin-right: 15px; border-radius: 5px; background: #222; padding: 5px; border-bottom: 2px solid ${colorRareza};">
                                <div>
                                    <span style="font-weight: bold; color: #fff; display: block;">${nombre}</span>
                                    <span style="font-size: 0.8rem; color: ${colorRareza}; text-transform: uppercase; font-weight: bold;">${premio.tipo_premio}</span>
                                </div>
                            </div>
                            <div style="color: #f0c330; font-weight: bold; font-size: 1.1rem;">
                                ${parseFloat(premio.probabilidad)}%
                            </div>
                        </li>
                    `;
                });
            } else {
                lista.innerHTML = '<p style="color: #ff4444; text-align: center;">Error al cargar el contenido.</p>';
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function cerrarProbabilidades() {
        document.getElementById('modal-probabilidades').style.display = 'none';
    }
</script>

<script src="js/notificaciones.js"></script>
</body>
</html>