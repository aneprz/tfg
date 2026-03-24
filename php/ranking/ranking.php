<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../index.php");
    exit();
}
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Ranking de Jugadores</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_ranking.css">
    <link rel="icon" href="../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../tienda/tienda.php">Tienda</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <li><a href="ranking.php" class="activo">Ranking</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
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
                <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
            </div>
        <?php endif; ?>
    </header>

    <div class="central">
        <h1>Ranking de la comunidad</h1>
        <p>Aqui podras ver el ranking de los jugadores más activos de la comunidad.</p><br>
        
        <div class="controles-ranking">
            <button id="btn-global" class="btn-filtro activo">Ranking Global</button>
            <button id="btn-amigos" class="btn-filtro">Ranking de Amigos</button>
            
            <select id="filtro-orden" class="select-orden">
                <option value="puntos">Ordenar por Puntos</option>
                <option value="juegos">Ordenar por Cant. Juegos</option>
            </select>
        </div>

        <main id="contenedor-ranking" class="user-grid">
            </main>
    </div>
        
    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

    <script src="../../js/notificaciones.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btnGlobal = document.getElementById('btn-global');
            const btnAmigos = document.getElementById('btn-amigos');
            const selectOrden = document.getElementById('filtro-orden');
            const contenedor = document.getElementById('contenedor-ranking');
            const idSesion = <?php echo $_SESSION['id_usuario'] ?? 0; ?>;

            let tipoActual = 'global';
            let ordenActual = 'puntos';

            function cargarRanking() {
                contenedor.innerHTML = '<div class="cargando">Cargando guerreros...</div>';
                
                fetch(`procesarRanking.php?tipo=${tipoActual}&orden=${ordenActual}`)
                    .then(res => res.json())
                    .then(data => {
                        contenedor.innerHTML = '';
                        
                        if (data.error) {
                            contenedor.innerHTML = `<p style="color:red">Error: ${data.error}</p>`;
                            return;
                        }
                        
                        if (data.length === 0) {
                            contenedor.innerHTML = '<p style="color:#9ab;">No hay jugadores que mostrar con estos filtros.</p>';
                            return;
                        }

                        data.forEach((user, index) => {
                            const posicion = index + 1;
                            let claseTop = '';
                            if (posicion === 1) claseTop = 'top-1';
                            else if (posicion === 2) claseTop = 'top-2';
                            else if (posicion === 3) claseTop = 'top-3';

                            const enlacePerfil = user.id_usuario == idSesion 
                                ? '../user/perfiles/perfilSesion.php' 
                                : `../user/amistades/perfilOtros.php?id=${user.id_usuario}`;

                            const textoValor = ordenActual === 'puntos' ? `${user.valor} Puntos` : `${user.valor} Juegos`;

                            const tarjeta = `
                                <a href="${enlacePerfil}" class="tarjeta-ranking ${claseTop}">
                                    <div class="posicion">#${posicion}</div>
                                    <img src="${user.avatar_url}" class="avatar-ranking" alt="Avatar">
                                    <div class="info-ranking">
                                        <h3>${user.gameTag}</h3>
                                        <p>${textoValor}</p>
                                    </div>
                                </a>
                            `;
                            contenedor.innerHTML += tarjeta;
                        });
                    })
                    .catch(err => console.error("Error cargando ranking:", err));
            }

            btnGlobal.addEventListener('click', () => {
                tipoActual = 'global';
                btnGlobal.classList.add('activo');
                btnAmigos.classList.remove('activo');
                cargarRanking();
            });

            btnAmigos.addEventListener('click', () => {
                tipoActual = 'amigos';
                btnAmigos.classList.add('activo');
                btnGlobal.classList.remove('activo');
                cargarRanking();
            });

            selectOrden.addEventListener('change', (e) => {
                ordenActual = e.target.value;
                cargarRanking();
            });

            cargarRanking();
        });
    </script>
</body>
</html>