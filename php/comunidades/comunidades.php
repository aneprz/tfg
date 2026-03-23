<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$comunidades = [];
$id_usuario_sesion = $_SESSION['id_usuario'] ?? 0;

if (isset($conexion) && $conexion) {
   $sqlComunidades = "
        SELECT 
            c.id_comunidad, 
            c.nombre, 
            c.banner_url,
            v.portada AS portada_juego,
            v.titulo AS juego_nombre,
            (SELECT COUNT(*) FROM Miembro_Comunidad WHERE id_comunidad = c.id_comunidad) AS total_miembros,
            (SELECT COUNT(*) FROM Miembro_Comunidad WHERE id_comunidad = c.id_comunidad AND id_usuario = $id_usuario_sesion) AS ya_es_miembro
        FROM Comunidad c
        LEFT JOIN Videojuego v ON c.id_videojuego_principal = v.id_videojuego
        GROUP BY c.id_comunidad, c.nombre, v.portada, v.titulo
        ORDER BY total_miembros DESC, c.nombre ASC";

    $res = mysqli_query($conexion, $sqlComunidades);
    if ($res) {
        while ($fila = mysqli_fetch_assoc($res)) {
            $comunidades[] = $fila;
        }
    }
}
$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidades - SalsaBox</title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_comunidades.css">
    <link rel="icon" href="../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../../index.php">Inicio</a></li>
                <li><a href="../videojuegos/juegos.php">Juegos</a></li>
                <li><a href="../../php/jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="comunidades.php" class="activo">Comunidades</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if (!isset($_SESSION['tag'])): ?>

        <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">
            Iniciar sesión
        </a>

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

    <div class="central">
        <h1>Comunidades</h1> 
        <a href="agregar_comunidad.php" class="btn-agregar">Añadir Comunidad</a>
        <br>
        <br>
        <p>Únete a grupos de tus juegos favoritos, comparte clips y conoce a otros jugadores.</p>
    </div>

    <main>
        <h2>Explorar Grupos</h2>
        <div class="grid-comunidades">
            <?php if (count($comunidades) > 0): ?>
                <?php foreach ($comunidades as $com): ?>
                    <article class="card-comunidad">
                        <div class="banner-comunidad" style="background-image: url('../../media/<?php echo !empty($com['banner_url']) ? htmlspecialchars($com['banner_url']) : htmlspecialchars($com['portada_juego']); ?>')">
                            <div class="overlay-miembros">
                                ● <?php echo number_format($com['total_miembros'], 0, ',', '.'); ?> miembros
                            </div>
                        </div>
                        <div class="cuerpo-comunidad">
                            <span class="juego-tag"><?php echo htmlspecialchars($com['juego_nombre']); ?></span>
                            <h3><?php echo htmlspecialchars($com['nombre']); ?></h3>
                            
                            <div class="acciones-com">
                                <a href="ver_comunidad.php?id=<?php echo $com['id_comunidad']; ?>" class="btn-entrar">Ver Muro</a>
                                
                                <?php if (!isset($_SESSION['id_usuario'])): ?>
                                    <a href="../sesiones/login/login.php" class="botonUnirse">Unirse</a>
                                <?php else: ?>
                                    <button 
                                        type="button"
                                        class="botonUnirse btn-accion-comunidad" 
                                        data-id="<?php echo $com['id_comunidad']; ?>" 
                                        data-accion="<?php echo ($com['ya_es_miembro'] > 0) ? 'salir' : 'unirse'; ?>"
                                        style="font-weight: bold; border-radius: 8px; padding: 8px 15px; cursor: pointer; <?php echo ($com['ya_es_miembro'] > 0) 
                                            ? 'background-color: #ff4d4d; color: #000; border: 2px solid #cc0000;' 
                                            : 'background-color: #f1c40f; color: #000; border: 2px solid #f1c40f;'; ?>">
                                        <?php echo ($com['ya_es_miembro'] > 0) ? 'Abandonar' : 'Unirse'; ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="aviso-vacio">No se encontraron comunidades creadas en la base de datos.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

    <script src="../../js/comunidades.js"></script>
</body>
</html>