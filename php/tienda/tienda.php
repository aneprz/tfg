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

    <title>SalsaBox - Tienda</title>

    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_juegos.css">
    <link rel="stylesheet" href="../../estilos/estilos_tienda.css">

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

        <script src="../../js/notificaciones.js"></script>

</header>


<!-- SUBMENU -->
<div class="subnav">

    <div class="subnav-container">

        <a href="tienda.php" class="subnav-link activo">Tienda</a>

        <a href="inventario.php" class="subnav-link">Inventario</a>

        <a href="tienda_lootboxes.php" class="subnav-link">Cajas</a>

    </div>

</div>


<div class="central">

    <h1>Gasta tus puntos</h1>

    <p>
        Personaliza tu perfil y desbloquea contenido exclusivo.
    </p>

    <div class="puntosUsuario">
        Tus puntos: <strong><?php echo $puntos; ?></strong>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <br>

    <div class="buscadorContainer">

        <input
            type="text"
            id="buscadorTienda"
            placeholder="Buscar item..."
        >

    </div>

    <br>

    <div class="filtrosContainer">

        <select id="ordenTienda">

            <option value="precio_asc">Precio ↑</option>
            <option value="precio_desc">Precio ↓</option>

            <option value="nombre_asc">Nombre A → Z</option>
            <option value="nombre_desc">Nombre Z → A</option>

            <option value="rareza_desc">Más raros</option>

        </select>

    </div>

</div>


<main>

    <h2>Todos los items</h2>

    <!-- 👇 MISMA CLASE QUE JUEGOS -->
    <div class="juegos" id="gridTienda"></div>

    <p id="sinResultados" class="sinResultados" hidden>
        No se encontraron items.
    </p>

    <div class="paginacion" id="paginacion"></div>

</main>


<footer>
    <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
</footer>

<div id="modalPreview" class="modal">
    <div class="modal-content">

        <span class="cerrar">&times;</span>

        <div class="perfil-preview" id="previewPerfil">

            <div class="avatar-preview" id="previewAvatar">
                <img id="previewMarco" class="preview-marco">
                <img id="previewAvatarImg" src="<?php echo htmlspecialchars($avatar_usuario); ?>">
            </div>

            <h2><?php echo htmlspecialchars($_SESSION['tag'] ?? 'Usuario'); ?></h2>

        </div>

    </div>
</div>
<script>

document.addEventListener("DOMContentLoaded", () => {

    const buscador = document.getElementById("buscadorTienda");
    const orden = document.getElementById("ordenTienda");

    const grid = document.getElementById("gridTienda");
    const paginacion = document.getElementById("paginacion");
    const sinResultados = document.getElementById("sinResultados");

    let pagina = 1;

    function cargarTienda(){

        const texto = buscador.value;
        const ordenValor = orden.value;

        fetch(`procesarTienda.php?buscar=${encodeURIComponent(texto)}&orden=${ordenValor}&pagina=${pagina}`)

            .then(res => res.json())

            .then(data => {

                grid.innerHTML = data.html;
                paginacion.innerHTML = data.paginacion;

                sinResultados.hidden = data.total > 0;

                document.querySelectorAll(".pag-btn").forEach(btn => {

                    btn.addEventListener("click", () => {

                        pagina = btn.dataset.pagina;
                        cargarTienda();

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
        cargarTienda();
    });

    orden.addEventListener("change", () => {
        pagina = 1;
        cargarTienda();
    });

    cargarTienda();

});

// MODAL
const modal = document.getElementById("modalPreview");
const cerrar = document.querySelector(".cerrar");

document.addEventListener("click", e => {

    const item = e.target.closest(".item-preview");
    if (!item) return;

    const tipo = item.dataset.tipo;
    const imagen = item.dataset.imagen;

    const preview = document.getElementById("previewPerfil");
    const marco = document.getElementById("previewMarco");
    const avatar = document.getElementById("previewAvatarImg");

    // Reset SIEMPRE
    preview.style.backgroundImage = "";
    marco.src = "";
    avatar.src = "<?php echo htmlspecialchars($avatar_usuario); ?>";

    if (tipo === "fondo") {
        preview.style.backgroundImage = `url('../../media/${imagen}')`;
    }

    if (tipo === "marco") {
        marco.src = `../../media/${imagen}`;
    }

    if (tipo === "avatar") {
        avatar.src = `../../media/${imagen}`; 
    }

    modal.style.display = "flex";
});

// cerrar
cerrar.onclick = () => modal.style.display = "none";
window.onclick = e => {
    if (e.target === modal) modal.style.display = "none";
};

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
</body>
</html>