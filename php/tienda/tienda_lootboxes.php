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

/* CONTENEDOR */
.carruselLootbox {
    width: 100%;
    overflow: hidden;
    border: 2px solid #2c3440;
    border-radius: 10px;
    background: #1f252c;
    padding: 15px 0;
    position: relative;
}

/* EFECTO SOMBRA LATERAL */
.carruselLootbox::before,
.carruselLootbox::after {
    content: "";
    position: absolute;
    top: 0;
    width: 80px;
    height: 100%;
    z-index: 2;
}

.carruselLootbox::before {
    left: 0;
    background: linear-gradient(to right, #1f252c, transparent);
}

.carruselLootbox::after {
    right: 0;
    background: linear-gradient(to left, #1f252c, transparent);
}

/* TRACK (LO QUE SE MUEVE) */
.carrusel-track {
    display: flex;
    gap: 15px;
    transition: transform 2s cubic-bezier(0.25, 0.1, 0.25, 1); /* transición inicial */
}

/* ITEM */
.carrusel-item {
    min-width: 100px;
    text-align: center;
}

.carrusel-item img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid transparent;
    background: #2c3440;
}

/* RAREZA */
.carrusel-item.legendario img { border-color: gold; }
.carrusel-item.epico img { border-color: #9b59b6; }
.carrusel-item.raro img { border-color: #3498db; }
.carrusel-item.comun img { border-color: #555; }

/* GANADOR */
.carrusel-item.ganador img {
    transform: scale(1.2);
    border-color: var(--accent-color);
    box-shadow: 0 0 15px var(--accent-color);
}

/* TEXTO GANADO */
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
<?php if (!isset($_SESSION['tag'])): ?>
    <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
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
<script src="../../js/notificaciones.js"></script>
</header>

<!-- SUBMENU -->
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

<!-- Modal Lootbox -->
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
    let cargando = false; // 🚀 anti-spam

    function cargarLootboxes() {
        fetch(`procesarLootboxes.php?pagina=${pagina}`)
            .then(res => res.json())
            .then(data => {

                if(data.error){
                    console.error(data.error);
                    return;
                }

                grid.innerHTML = data.html;
                paginacion.innerHTML = data.paginacion;
                sinResultados.hidden = data.total > 0;

                // PAGINACIÓN
                document.querySelectorAll(".pag-btn").forEach(btn => {
                    btn.addEventListener("click", () => {
                        pagina = btn.dataset.pagina;
                        cargarLootboxes();
                        window.scrollTo({ top: 0, behavior: "smooth" });
                    });
                });

                // BOTONES LOOTBOX
                document.querySelectorAll(".btn-comprar-lootbox").forEach(btn => {

                    btn.addEventListener("click", async e => {

                        e.preventDefault();

                        if (cargando) return; // 🚀 evita spam
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

                            if(!res.ok){
                                alert(res.error || "Error inesperado");
                                return;
                            }

                            // actualizar puntos
                            puntosUsuario.textContent = res.nuevosPuntos;

                            // animación
                            abrirLootboxAnimacion(res.items, res.ganado, res);

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

            })
            .catch(err => {
                console.error("Error cargando lootboxes:", err);
            });
    }

    cargarLootboxes();

    /* ===== MODAL ===== */

    const modal = document.getElementById("modalLootbox");
    const cerrar = document.querySelector(".cerrarLootbox");

    const carrusel = document.getElementById("carruselLootbox");
    const itemGanadoDiv = document.getElementById("itemGanado");
    const nombreItemGanado = document.getElementById("nombreItemGanado");
    const imgItemGanado = document.getElementById("imgItemGanado");

    cerrar.onclick = () => modal.style.display = "none";

    window.onclick = e => {
        if(e.target === modal) modal.style.display = "none";
    };
function abrirLootboxAnimacion(items, ganador, res){
    carrusel.innerHTML = '';
    itemGanadoDiv.hidden = true;

    const track = document.createElement('div');
    track.classList.add('carrusel-track');

    const lista = [];

    // duplicamos items varias veces para recorrido largo
    const duplicaciones = 15;
    for(let i=0;i<duplicaciones;i++){
        items.forEach(it => lista.push(it));
    }

    // colocar el ganador en el centro de la lista
    const posicionGanador = Math.floor(lista.length / 2);
    lista[posicionGanador] = ganador;

    // pintar items
    lista.forEach(it => {
        const div = document.createElement('div');
        div.classList.add('carrusel-item', it.rareza || 'comun');
        div.innerHTML = `<img src='../../media/${it.imagen}'>`;
        track.appendChild(div);
    });

    carrusel.appendChild(track);
    modal.style.display = "flex";

    const itemWidth = 100;
    const gap = 15;
    const itemWidthTotal = itemWidth + gap;
    const centroCarrusel = carrusel.offsetWidth / 2 - itemWidth / 2;

    // destino final para centrar el ganador
    const destinoFinal = posicionGanador * itemWidthTotal - centroCarrusel;

    const duracion = 5000;
    const inicio = performance.now();

    function easeOutCubic(t){ return 1 - Math.pow(1 - t, 3); }

    function animar(now){
        let tiempo = (now - inicio) / duracion;
        if(tiempo > 1) tiempo = 1;

        // simulamos que recorre más items al inicio
        // usando un factor que disminuye con el tiempo
        const recorridoExtra = 5 * itemWidthTotal; // recorre 5 items extra al inicio
        const pos = destinoFinal * easeOutCubic(tiempo) + recorridoExtra * (1 - easeOutCubic(tiempo));

        track.style.transform = `translateX(-${pos}px)`;

        if(tiempo < 1){
            requestAnimationFrame(animar);
        } else {
            const itemsDOM = track.querySelectorAll('.carrusel-item');
            itemsDOM[posicionGanador].classList.add('ganador');

            nombreItemGanado.textContent = ganador.nombre;
            if(res && res.duplicado){
                nombreItemGanado.textContent += ` (Duplicado → +${res.valorDevuelto} pts)`;
            }
            imgItemGanado.src = `../../media/${ganador.imagen}`;
            itemGanadoDiv.hidden = false;
        }
    }

    requestAnimationFrame(animar);
}
});
</script>
</body>
</html>