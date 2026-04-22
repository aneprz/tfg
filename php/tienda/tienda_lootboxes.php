<?php
session_start();
require '../../db/conexiones.php';

$admin = ($_SESSION['admin'] ?? false) === true;

/* =========================
   PUNTOS USUARIO
   ========================= */

$puntos = 0;

if (isset($_SESSION['id_usuario'])) {
    $id = $_SESSION['id_usuario'];
    $res = mysqli_query($conexion, "SELECT puntos_actuales FROM Usuario WHERE id_usuario = $id");
    $puntos = mysqli_fetch_assoc($res)['puntos_actuales'] ?? 0;
}

$avatar_usuario = "../../media/perfil_default.jpg";

if (isset($_SESSION['id_usuario'])) {
    $id = $_SESSION['id_usuario'];
    $res = mysqli_query($conexion, "SELECT avatar FROM Usuario WHERE id_usuario = $id");
    $data = mysqli_fetch_assoc($res);

    if (!empty($data['avatar'])) {
        $avatar_usuario = "../../media/" . $data['avatar'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SalsaBox - Lootboxes</title>
<link rel="stylesheet" href="../../estilos/estilos_index.css">
<link rel="stylesheet" href="../../estilos/estilos_juegos.css">
<link rel="stylesheet" href="../../estilos/estilos_tienda.css">
<link rel="icon" href="../../media/logoPlatino.png">
<style>
/* ===== MODAL LOOTBOX PRO ===== */
.modal-content {
    background: var(--card-bg);
    border: 1px solid #2c3440;
    box-shadow: 0 20px 50px rgba(0,0,0,0.8);
}

.carruselLootbox {
    width: 100%;
    overflow: hidden;
    border: 2px solid #2c3440;
    border-radius: 10px;
    background: #1f252c;
    padding: 15px 0;
    position: relative;
}

.carruselLootbox::before,
.carruselLootbox::after {
    content: "";
    position: absolute;
    top: 0;
    width: 80px;
    height: 100%;
    z-index: 2;
}

.carruselLootbox::before { left: 0; background: linear-gradient(to right, #1f252c, transparent); }
.carruselLootbox::after { right: 0; background: linear-gradient(to left, #1f252c, transparent); }

.carrusel-track { display: flex; gap: 15px; }

.carrusel-item { min-width: 100px; text-align: center; }

.carrusel-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid transparent;
    background: #2c3440;
}

.carrusel-item.legendario img { border-color: gold; }
.carrusel-item.epico img { border-color: #9b59b6; }
.carrusel-item.raro img { border-color: #3498db; }
.carrusel-item.comun img { border-color: #555; }

.carrusel-item.ganador img {
    transform: scale(1.2);
    border-color: var(--accent-color);
    box-shadow: 0 0 15px var(--accent-color);
}

.itemGanado {
    margin-top: 15px;
    animation: fadeInUp 0.5s ease;
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
        <li><a href="../videojuegos/juegos.php">Juegos</a></li>
        <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
        <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
        <li><a href="../tienda/tienda.php" class="activo">Tienda</a></li>
        <li><a href="../logros/logros.php">Logros</a></li>
        <li><a href="../ranking/ranking.php">Ranking</a></li>
        <?php if ($admin): ?>
            <li><a href="../admin/indexAdmin.php">Admin</a></li>
        <?php endif; ?>
    </ul>
</nav>
</nav>

<button class="menu-toggle" aria-label="Menú">☰</button>
<?php if (!isset($_SESSION['tag'])): ?>
    <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
<?php else: ?>
    <div class="user-actions">
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
<script src="../../js/notificaciones.js"></script>
</header>

<div class="subnav">
    <div class="subnav-container">
        <a href="tienda.php" class="subnav-link">Tienda</a>
        <a href="inventario.php" class="subnav-link">Inventario</a>
        <a href="tienda_lootboxes.php" class="subnav-link activo">Cajas</a>
    </div>
</div>

<div class="central">
    <h1>Compra tus Lootboxes</h1>
    <p>Compra lootboxes y recibe tus items al instante.</p>
    <div class="puntosUsuario">Tus puntos: <strong id="puntosUsuario"><?php echo $puntos; ?></strong></div>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
</div>

<main>
    <h2>Lootboxes disponibles</h2>
    <div class="juegos" id="gridTiendaLootboxes"></div>
    <p id="sinResultados" class="sinResultados" hidden>No se encontraron lootboxes.</p>
    <div class="paginacion" id="paginacion"></div>
</main>

<div id="modalLootbox" class="modal">
    <div class="modal-content">
        <span class="cerrarLootbox">&times;</span>
        <h2>Abrir Lootbox</h2>
        <div id="carruselLootbox" class="carruselLootbox"></div>
        <div id="itemGanado" class="itemGanado" hidden>
            <h3>¡Has ganado:</h3>
            <p id="nombreItemGanado"></p>
            <img id="imgItemGanado" src="" alt="">
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const grid = document.getElementById("gridTiendaLootboxes");
    const paginacion = document.getElementById("paginacion");
    const sinResultados = document.getElementById("sinResultados");
    const puntosUsuario = document.getElementById("puntosUsuario");

    let pagina = 1;
    let cargando = false;

    function cargarLootboxes() {
        fetch(`procesarLootboxes.php?pagina=${pagina}`)
            .then(res => res.json())
            .then(data => {
                if(data.error){ console.error(data.error); return; }
                grid.innerHTML = data.html;
                paginacion.innerHTML = data.paginacion;
                sinResultados.hidden = data.total > 0;

                document.querySelectorAll(".pag-btn").forEach(btn => {
                    btn.addEventListener("click", () => {
                        pagina = btn.dataset.pagina;
                        cargarLootboxes();
                        window.scrollTo({ top: 0, behavior: "smooth" });
                    });
                });

                document.querySelectorAll(".btn-comprar-lootbox").forEach(btn => {
                    btn.addEventListener("click", async e => {
                        e.preventDefault();
                        if (cargando) return;
                        cargando = true;
                        btn.disabled = true;
                        btn.textContent = "Abriendo...";
                        const id_lootbox = btn.dataset.id;
                        try {
                            const res = await fetch('abrir_lootbox_ajax.php', {
                                method:'POST',
                                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                                body:`id_lootbox=${id_lootbox}`
                            }).then(r=>r.json());

                            if(!res.ok){ alert(res.error || "Error inesperado"); return; }
                            const nuevosPuntos = res.nuevosPuntos;
                            abrirLootboxAnimacion(res.items, res.ganado, res, nuevosPuntos);
                        } catch (err) {
                            console.error(err);
                            alert("Error de conexión");
                        } finally {
                            cargando = false;
                            btn.disabled = false;
                            btn.textContent = "Abrir caja";
                        }
                    });
                });
            });
    }

    cargarLootboxes();

    const modal = document.getElementById("modalLootbox");
    const cerrar = document.querySelector(".cerrarLootbox");
    const carrusel = document.getElementById("carruselLootbox");
    const itemGanadoDiv = document.getElementById("itemGanado");
    const nombreItemGanado = document.getElementById("nombreItemGanado");
    const imgItemGanado = document.getElementById("imgItemGanado");

    cerrar.onclick = () => modal.style.display = "none";
    window.onclick = e => { if(e.target === modal) modal.style.display = "none"; };

    function abrirLootboxAnimacion(items, ganador, res, nuevosPuntos) {
        carrusel.innerHTML = '';
        itemGanadoDiv.hidden = true;
        const track = document.createElement('div');
        track.classList.add('carrusel-track');
        track.style.willChange = "transform";
        track.style.transition = "none";

        const lista = [];
        const totalItems = 120;
        const posicionGanador = Math.floor(totalItems * 0.7);

        for (let i = 0; i < totalItems; i++) {
            lista.push(items[Math.floor(Math.random() * items.length)]);
        }
        lista[posicionGanador] = ganador;

        lista.forEach(it => {
            const div = document.createElement('div');
            div.classList.add('carrusel-item', it.rareza || 'comun');
            div.innerHTML = `<img src='../../media/${it.imagen}'>`;
            track.appendChild(div);
        });

        carrusel.appendChild(track);
        modal.style.display = "flex";

        setTimeout(() => {
            const primerItem = track.querySelector('.carrusel-item');
            const segundoItem = track.children[1];
            if (!primerItem || !segundoItem) return;

            const itemRect = primerItem.getBoundingClientRect();
            const segundoRect = segundoItem.getBoundingClientRect();
            const itemWidthTotal = segundoRect.left - itemRect.left;
            const carruselRect = carrusel.getBoundingClientRect();
            const desplazamientoCentro = (carruselRect.width / 2) - (itemRect.width / 2);

            const destinoFinal = (posicionGanador * itemWidthTotal) - desplazamientoCentro;
            const duracion = 6500;
            const inicio = performance.now();

            function easeOutQuint(t) { return 1 - Math.pow(1 - t, 5); }

            let animFrameId = null;
            const itemsDOM = track.querySelectorAll('.carrusel-item');

            function animar(now) {
                let tiempo = (now - inicio) / duracion;
                if (tiempo >= 1) {
                    cancelAnimationFrame(animFrameId);
                    track.style.transform = `translate3d(-${destinoFinal}px, 0, 0)`;
                    itemsDOM.forEach(item => { item.style.transform = ''; });
                    itemsDOM[posicionGanador].classList.add('ganador');
                    nombreItemGanado.textContent = ganador.nombre;
                    if (res && res.duplicado) { nombreItemGanado.textContent += ` (Duplicado → +${res.valorDevuelto} pts)`; }
                    imgItemGanado.src = `../../media/${ganador.imagen}`;
                    itemGanadoDiv.hidden = false;
                    puntosUsuario.textContent = nuevosPuntos;
                    return;
                }
                const t = easeOutQuint(tiempo);
                const desplazamiento = destinoFinal * t;
                track.style.transform = `translate3d(-${desplazamiento}px, 0, 0)`;

                itemsDOM.forEach((item, idx) => {
                    const itemCenter = idx * itemWidthTotal + (itemRect.width / 2);
                    const distancia = Math.abs(itemCenter - (desplazamiento + (carruselRect.width / 2)));
                    const escala = Math.max(1, 1.25 - distancia / 350);
                    item.style.transform = `scale(${escala})`;
                });
                animFrameId = requestAnimationFrame(animar);
            }
            animFrameId = requestAnimationFrame(animar);
        }, 50);
    }
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('nav');
        if (menuToggle) {
            menuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                nav.classList.toggle('open');
            });
        }
    });
</script>
<script src="../../js/social.js"></script>
</body>
</html>