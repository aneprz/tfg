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
    <link rel="stylesheet" href="../../estilos/estilos_tienda.css">

    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="icon" href="../../media/logoPlatino.png">

    <style>
      /* =========================================================
   ESTILOS GENERALES DE LA SECCIÓN Y CUADRÍCULA
   ========================================================= */
.seccion-cajas { padding: 40px 20px; max-width: 1200px; margin: 0 auto; }
.grid-cajas { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 30px; margin-top: 30px; }

/* =========================================================
   ESTRUCTURA BASE DE TODAS LAS CAJAS
   ========================================================= */
.caja-item { 
    background-color: #16181f; 
    border-radius: 8px; 
    padding: 30px 20px 20px 20px; 
    text-align: center; 
    border: 2px solid #2d313d; 
    transition: all 0.3s ease; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    position: relative; 
    height: 100%; /* Obliga a todas las cajas a medir lo mismo */
    box-sizing: border-box;
}

/* EL CONTENEDOR DE LA IMAGEN: Fijo y estricto */
.caja-hueco { 
    height: 180px; 
    width: 100%; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    margin-bottom: 20px; 
    flex-shrink: 0; /* Impide que este hueco se encoja */
}

/* LA IMAGEN: Obediente al hueco */
.caja-imagen { 
    max-width: 100%; 
    max-height: 100%; 
    object-fit: contain; 
    filter: drop-shadow(0px 10px 15px rgba(0,0,0,0.6)); 
    transition: transform 0.3s ease, filter 0.3s ease; 
    display: block;
}

.caja-item:hover .caja-imagen { 
    transform: scale(1.08) translateY(-5px); 
}

/* LOS TEXTOS */
.caja-info { width: 100%; }
.caja-titulo { font-size: 1.2rem; font-weight: 800; color: #fff; margin: 0 0 10px 0; letter-spacing: 1px; }
.caja-precio { font-size: 1.1rem; color: #f0c330; font-weight: bold; margin: 0; }

/* LA MAGIA DE LA ALINEACIÓN (Empuja el botón hacia abajo) */
.caja-footer { 
    margin-top: auto; /* <--- ESTO ALINEA LOS BOTONES ABAJO DEL TODO */
    width: 100%; 
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    padding-top: 25px;
}

.boton-abrir { 
    border: none; padding: 12px 20px; font-size: 1rem; font-weight: 800; border-radius: 6px; 
    cursor: pointer; transition: background-color 0.2s, transform 0.1s; width: 100%; 
}
.boton-abrir:active { transform: scale(0.96); }

.ver-contenido { margin-top: 15px; font-size: 0.85rem; color: #888; cursor: pointer; text-decoration: underline; transition: color 0.2s; }
.ver-contenido:hover { color: #fff; }


/* =========================================================
   COLORES EXCLUSIVOS DE CADA CAJA (Basado en tu captura)
   ========================================================= */

/* 1. CAJA INDIE (Azul) */
.caja-basica { border-color: #4aa3f0; }
.caja-basica:hover { box-shadow: 0 0 20px rgba(74, 163, 240, 0.2); }
.caja-basica .boton-abrir { background-color: #4aa3f0; color: #fff; }
.caja-basica .boton-abrir:hover { background-color: #388ad1; }

/* 2. CAJA TRIPLE A (Morada) */
.caja-epica { border-color: #c724b1; }
.caja-epica:hover { box-shadow: 0 0 20px rgba(199, 36, 177, 0.2); }
.caja-epica .boton-abrir { background-color: #c724b1; color: #fff; }
.caja-epica .boton-abrir:hover { background-color: #a31d91; }

/* 3. CAJA GOTY (Legendaria - Dorada) */
.caja-legendaria { border-color: #f0c330; background: linear-gradient(180deg, #16181f 0%, #241e0d 100%); }
.caja-legendaria:hover { box-shadow: 0 0 25px rgba(240, 195, 48, 0.3); }
.caja-legendaria .caja-imagen { filter: drop-shadow(0px 15px 25px rgba(240, 195, 48, 0.3)); }
.caja-legendaria .boton-abrir { background-color: #f0c330; color: #111; }
.caja-legendaria .boton-abrir:hover { background-color: #dcb028; }

/* 4. CAJA ENMARCADA (Blanca/Gris) */
.caja-enmarcada { border-color: #3a3f4e; }
.caja-enmarcada:hover { box-shadow: 0 0 20px rgba(255, 255, 255, 0.1); border-color: #5a6175;}
.caja-enmarcada .boton-abrir { background-color: #f5f5f5; color: #111; }
.caja-enmarcada .boton-abrir:hover { background-color: #d4d4d4; }

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
        /* =========================================================
   MODAL DE PROBABILIDADES (Responsive + Scroll)
   ========================================================= */

/* 1. El fondo oscuro que tapa la pantalla */
#modal-probabilidades {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.85);
    z-index: 9999;
    /* Usamos flexbox para centrar la caja en la pantalla */
    display: none; /* Se cambia a flex por JS */
    justify-content: center;
    align-items: center;
    padding: 20px; /* Margen de seguridad para móviles */
    box-sizing: border-box;
}

/* 2. La caja del modal (Busca la clase que uses en tu HTML para la caja blanca/gris, si no tiene clase, ponle .modal-caja-interna y añádela al HTML) */
#modal-probabilidades > div {
    background-color: #1a1c23;
    border: 2px solid #2d313d;
    border-radius: 10px;
    width: 100%;
    max-width: 450px; /* En PC medirá 450px, en móvil encogerá hasta el 100% */
    padding: 25px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.8);
    display: flex;
    flex-direction: column;
}

/* 3. LA MAGIA DEL SCROLL EN LA LISTA */
#lista-probabilidades {
    list-style: none;
    margin: 15px 0;
    padding: 0;
    padding-right: 10px; /* Deja espacio a la derecha para que no se pegue la barra */
    max-height: 50vh; /* NUNCA medirá más del 50% del alto de la pantalla del usuario */
    overflow-y: auto; /* Activa el scroll vertical automáticamente si se pasa de altura */
}

/* 4. ESTILIZAR LA BARRA DE SCROLL (Diseño AAA) */
#lista-probabilidades::-webkit-scrollbar {
    width: 6px;
}
#lista-probabilidades::-webkit-scrollbar-track {
    background: #111; 
    border-radius: 10px;
}
#lista-probabilidades::-webkit-scrollbar-thumb {
    background: #4aa3f0; 
    border-radius: 10px;
}
#lista-probabilidades::-webkit-scrollbar-thumb:hover {
    background: #f0c330; 
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
            <li><a href="tienda.php" class="activo">Tienda</a></li>
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
<div class="subnav">
    <div class="subnav-container">
        <a href="../tienda/tienda.php" class="subnav-link">Tienda</a>
        <a href="../tienda/inventario.php" class="subnav-link">Inventario</a>
        <a href="cajas.php" class="subnav-link activo">Cajas</a>
    </div>
</div>

<div class="central">
    <h1>Cajas de Botín</h1>
    <p>Prueba tu suerte. Abre cajas misteriosas y consigue juegos, avatares exclusivos o puntos extra para tu cuenta. ¿Te tocará el drop legendario?</p>
</div>

<main class="seccion-cajas">
    <h2>Colecciones Destacadas</h2>

    <div class="grid-cajas">
    
    <div class="caja-item caja-basica">
        <div class="caja-hueco">
            <img src="../../media/caja_indie.png" alt="Salsa Indie" class="caja-imagen">
        </div>
        <div class="caja-info">
            <h2 class="caja-titulo">SALSA INDIE</h2>
            <p class="caja-precio">150 Puntos</p>
        </div>
        <div class="caja-footer">
            <button class="boton-abrir" onclick="abrirCaja(1)">ABRIR CAJA</button>
            <span class="ver-contenido" onclick="verProbabilidades(1)">Ver contenido posible</span>
        </div>
    </div>

    <div class="caja-item caja-epica">
        <div class="caja-hueco">
            <img src="../../media/caja-epica.png" alt="Salsa Triple A" class="caja-imagen">
        </div>
        <div class="caja-info">
            <h2 class="caja-titulo">SALSA TRIPLE A</h2>
            <p class="caja-precio">500 Puntos</p>
        </div>
        <div class="caja-footer">
            <button class="boton-abrir" onclick="abrirCaja(2)">ABRIR CAJA</button>
            <span class="ver-contenido" onclick="verProbabilidades(2)">Ver contenido posible</span>
        </div>
    </div>

    <div class="caja-item caja-legendaria">
        <div class="caja-hueco">
            <img src="../../media/caja-legendaria.png" alt="Salsa Goty" class="caja-imagen">
        </div>
        <div class="caja-info">
            <h2 class="caja-titulo">SALSA GOTY</h2>
            <p class="caja-precio">1200 Puntos</p>
        </div>
        <div class="caja-footer">
            <button class="boton-abrir" onclick="abrirCaja(3)">ABRIR CAJA</button>
            <span class="ver-contenido" onclick="verProbabilidades(3)">Ver contenido posible</span>
        </div>
    </div>

    <div class="caja-item caja-enmarcada">
        <div class="caja-hueco">
            <img src="../../media/caja-marcos.png" alt="Salsa Enmarcada" class="caja-imagen">
        </div>
        <div class="caja-info">
            <h2 class="caja-titulo">SALSA ENMARCADA</h2>
            <p class="caja-precio">300 Puntos</p>
        </div>
        <div class="caja-footer">
            <button class="boton-abrir" onclick="abrirCaja(4)">ABRIR CAJA</button>
            <span class="ver-contenido" onclick="verProbabilidades(4)">Ver contenido posible</span>
        </div>
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
<?php
// --- NUEVO: Leemos la carpeta real para el teatro visual de la ruleta ---
$directorioFisicoMarcos = __DIR__ . '/../../media/marcos/';
// array_values(array_diff(...)) limpia el array de '.' y '..' y reindexa
$listaArchivosReales = array_values(array_diff(scandir($directorioFisicoMarcos), array('..', '.')));
?>
<script>
    const LISTA_MARCOS_REALES = <?php echo json_encode($listaArchivosReales); ?>;
    const menuToggle = document.querySelector('.menu-toggle');
    const nav = document.querySelector('nav');
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            nav.classList.toggle('open');
        });
    }

    // EL NUEVO MOTOR JAVASCRIPT DE LA RULETA
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

                // 1. Lógica inteligente de colores del PREMIO GANADOR
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

                // 2. Aseguramos imagen ganadora
                let imgGanador = data.imagen_premio ? '../../media/' + data.imagen_premio : '../../media/logoPlatino.png';
                let textoGanador = data.tipo_premio === 'puntos' ? data.puntos_premio + ' Puntos' : data.nombre_premio;

                // 3. BUCLE PARA DIBUJAR LA RULETA
                for (let i = 0; i < totalItems; i++) {
                    let div = document.createElement('div');
                    
                    if (i === indexGanador) {
                        div.className = `ruleta-item-track ${claseGanador}`;
                        div.innerHTML = `<img src="${imgGanador}"><span>${textoGanador}</span>`;
                    } else {
                        // RELLENO VISUAL (El teatro de la ruleta)
                        let random = Math.random() * 100;
                        let claseFalsa, imgFalsa, txtFalso;
                        
                        if (idCaja == 4) { // SI ES LA DE MARCOS (AQUÍ ESTÁ LA MAGIA ARREGLADA)
                            claseFalsa = 'rareza-morado'; 
                            let archivoAleatorio = LISTA_MARCOS_REALES[Math.floor(Math.random() * LISTA_MARCOS_REALES.length)];
                            imgFalsa = '../../media/marcos/' + archivoAleatorio; 
                            let txtLimpio = archivoAleatorio.replace('.png', '').split('_').pop().toUpperCase();
                            txtFalso = 'Marco ' + txtLimpio; 

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
                                let randomImg = Math.floor(Math.random() * 5) + 1; 
                                imgFalsa = '../../media/premiosTripleA/' + randomImg + '.png'; 
                                txtFalso = 'Avatar ÉPICO';
                            } else { 
                                claseFalsa = 'rareza-dorado'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '3000 Puntos'; 
                            }
                        } else { // CAJA INDIE
                            if (random < 50) { 
                                claseFalsa = 'rareza-gris'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '50 Puntos'; 
                            } else if (random < 80) { 
                                claseFalsa = 'rareza-azul'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '250 Puntos'; 
                            } else if (random < 98) { 
                                claseFalsa = 'rareza-morado'; 
                                let randomImg = Math.floor(Math.random() * 3) + 1; 
                                imgFalsa = '../../media/premiosIndie/' + randomImg + '.png'; 
                                txtFalso = 'Avatar Exclusivo';
                            } else { 
                                claseFalsa = 'rareza-dorado'; imgFalsa = '../../media/logoPlatino.png'; txtFalso = '1200 Puntos'; 
                            }
                        } 

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