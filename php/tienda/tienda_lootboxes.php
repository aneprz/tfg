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
/* Modal y animación de lootbox */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; 
         background: rgba(0,0,0,0.7); justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:#fff; padding:20px; border-radius:10px; text-align:center; position:relative; width:80%; max-width:500px; }
.cerrarLootbox { position:absolute; top:10px; right:10px; cursor:pointer; font-size:20px; }
.carruselLootbox { display:flex; overflow:hidden; margin:20px 0; justify-content:center; }
.carruselLootbox img { width:80px; margin:0 5px; border:2px solid transparent; border-radius:5px; }
.carruselLootbox img.seleccionado { border-color:gold; transform: scale(1.2); transition: all 0.3s; }
.itemGanado img { width:100px; margin-top:10px; }
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
                            abrirLootboxAnimacion(res.items, res.ganado);

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

    function abrirLootboxAnimacion(items, ganador){

        if(!items || items.length === 0){
            alert("Error: lootbox sin items");
            return;
        }

        carrusel.innerHTML = '';
        itemGanadoDiv.hidden = true;

        const carruselItems = [];

        // generar carrusel
        for(let i=0; i<15; i++){
            items.forEach(it=>{
                const img = document.createElement('img');
                img.src = `../../media/${it.imagen}`;
                img.dataset.id = it.id_item;

                carrusel.appendChild(img);
                carruselItems.push(img);
            });
        }

        modal.style.display = "flex";

        let index = 0;

        const interval = setInterval(() => {

            carruselItems.forEach(img => img.classList.remove('seleccionado'));

            carruselItems[index].classList.add('seleccionado');

            index = (index + 1) % carruselItems.length;

        }, 100);

        setTimeout(() => {

            clearInterval(interval);

            carruselItems.forEach(img => img.classList.remove('seleccionado'));

            const ganadorImg = carruselItems.find(img => img.dataset.id == ganador.id_item);

            if(ganadorImg){
                ganadorImg.classList.add('seleccionado');
            }

            nombreItemGanado.textContent = ganador.nombre;
            imgItemGanado.src = `../../media/${ganador.imagen}`;

            itemGanadoDiv.hidden = false;

        }, 3000);
    }

});
</script>
</body>
</html>