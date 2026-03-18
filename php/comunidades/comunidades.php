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
        <div class="user-actions" style="display: flex; align-items: center; gap: 15px;">
            <div class="notif-wrapper">
                <svg id="bell-icon" viewBox="0 0 24 24" style="width:24px; cursor:pointer; fill:#ffcc00;"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                <div id="notif-badge" style="display:none; position:absolute; top:-5px; right:-8px; background:red; color:white; border-radius:50%; padding:2px 5px; font-size:10px;">0</div>
                <div id="notif-dropdown" style="display:none; position:absolute; right:0; top:40px; background:#1a1a1a; border:1px solid #333; width:280px; z-index:999; border-radius:8px;">
                    <div class="notif-header" style="padding:10px; border-bottom:1px solid #333; color:#ffcc00; display:flex; justify-content:space-between;">
                        <span>Notificaciones</span>
                        <button onclick="marcarLeidas()" style="background:none; border:none; color:#888; cursor:pointer; font-size:11px;">Limpiar</button>
                    </div>
                    <ul id="notif-list" style="list-style:none; margin:0; padding:0; max-height:250px; overflow-y:auto;"></ul>
                </div>
            </div>
            <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
        </div>

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