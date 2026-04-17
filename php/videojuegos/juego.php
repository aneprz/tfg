<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../../db/conexiones.php';

if (!isset($conexion) || !$conexion) {
    die('Error: No se pudo establecer la conexión a la base de datos.');
}

// --- FUNCIONES DE RESOLUCIÓN DE RUTAS ---
function resolverAvatar($avatar) {
    $avatar = is_string($avatar) ? trim($avatar) : '';
    if ($avatar === '') return '../../media/perfil_default.jpg';
    if (preg_match('~^https?://~i', $avatar) === 1 || strpos($avatar, 'data:') === 0 || strpos($avatar, '/') === 0) return $avatar;
    $avatar = str_replace('\\', '/', $avatar);
    if (preg_match('~^(?:\\.\\./)+media/(.+)$~', $avatar, $m) === 1) $avatar = 'media/' . $m[1];
    $avatar = ltrim($avatar, '/');
    if (preg_match('~(^|/)\\.\\.(?:/|$)~', $avatar) === 1) return '../../media/perfil_default.jpg';
    if (strpos($avatar, '/') === false) $avatar = 'media/' . $avatar;
    $rutaWeb = '../../' . $avatar;
    $rutaFs = __DIR__ . '/../../' . $avatar;
    return is_file($rutaFs) ? $rutaWeb : '../../media/perfil_default.jpg';
}

function resolverPortada($portada) {
    $portada = is_string($portada) ? trim($portada) : '';
    if ($portada === '') return '../../media/logoPlatino.png';
    if (preg_match('~^https?://~i', $portada) === 1 || strpos($portada, 'data:') === 0) return $portada;
    $portada = str_replace('\\', '/', ltrim($portada, '/'));
    if (preg_match('~(^|/)\\.\\.(?:/|$)~', $portada) === 1) return '../../media/logoPlatino.png';
    if (strpos($portada, '/') === false) $portada = 'media/' . $portada;
    $rutaWeb = '../../' . $portada;
    $rutaFs = __DIR__ . '/../../' . $portada;
    return is_file($rutaFs) ? $rutaWeb : '../../media/logoPlatino.png';
}

$idJuego = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$idUsuario = $_SESSION['id_usuario'] ?? null;
$admin = ($_SESSION['admin'] ?? false) === true;

// --- ELIMINAR RESEÑA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_resena']) && $idUsuario && $idJuego > 0) {
    $idResena = isset($_POST['id_resena']) ? (int) $_POST['id_resena'] : 0;
    if ($idResena > 0) {
        if ($admin) {
            $sqlDel = "DELETE FROM Resena WHERE id_resena = ? AND id_videojuego = ?";
            $stmtDel = mysqli_prepare($conexion, $sqlDel);
            mysqli_stmt_bind_param($stmtDel, "ii", $idResena, $idJuego);
        } else {
            $sqlDel = "DELETE FROM Resena WHERE id_resena = ? AND id_usuario = ? AND id_videojuego = ?";
            $stmtDel = mysqli_prepare($conexion, $sqlDel);
            mysqli_stmt_bind_param($stmtDel, "iii", $idResena, $idUsuario, $idJuego);
        }
        mysqli_stmt_execute($stmtDel);
        mysqli_stmt_close($stmtDel);
    }
    header("Location: juego.php?id=" . $idJuego);
    exit;
}

// --- PROCESAR FORMULARIO DE USUARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_interaccion']) && $idUsuario && $idJuego > 0) {
    $estado = $_POST['estado'] ?? null;
    $mostrarExtras = in_array($estado, ['Completado', 'Abandonado']);
    $horas = $mostrarExtras && isset($_POST['horas']) ? (float) $_POST['horas'] : 0;
    $puntuacion = (float) $_POST['puntuacion'] * 2;
    $texto_resena = $mostrarExtras && isset($_POST['texto_resena']) ? trim($_POST['texto_resena']) : null;

    $sqlBiblioteca = "INSERT INTO Biblioteca (id_usuario, id_videojuego, estado, horas_totales) 
                      VALUES (?, ?, ?, ?) 
                      ON DUPLICATE KEY UPDATE estado = VALUES(estado), horas_totales = VALUES(horas_totales)";
    $stmtBib = mysqli_prepare($conexion, $sqlBiblioteca);
    mysqli_stmt_bind_param($stmtBib, "iisd", $idUsuario, $idJuego, $estado, $horas);
    mysqli_stmt_execute($stmtBib);
    mysqli_stmt_close($stmtBib);

    if ($mostrarExtras && ($puntuacion !== null || !empty($texto_resena))) {
        $sqlCheck = "SELECT id_resena FROM Resena WHERE id_usuario = ? AND id_videojuego = ?";
        $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, "ii", $idUsuario, $idJuego);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $rowCheck = mysqli_fetch_assoc($resCheck);
        mysqli_stmt_close($stmtCheck);

        if ($rowCheck) {
            $idResena = $rowCheck['id_resena'];
            $sqlUpdate = "UPDATE Resena SET puntuacion = ?, texto_resena = ?, fecha_publicacion = CURRENT_TIMESTAMP WHERE id_resena = ?";
            $stmtUpdate = mysqli_prepare($conexion, $sqlUpdate);
            mysqli_stmt_bind_param($stmtUpdate, "isi", $puntuacion, $texto_resena, $idResena);
            mysqli_stmt_execute($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);
        } else {
            $sqlInsert = "INSERT INTO Resena (id_usuario, id_videojuego, puntuacion, texto_resena) VALUES (?, ?, ?, ?)";
            $stmtInsert = mysqli_prepare($conexion, $sqlInsert);
            mysqli_stmt_bind_param($stmtInsert, "iiis", $idUsuario, $idJuego, $puntuacion, $texto_resena);
            mysqli_stmt_execute($stmtInsert);
            mysqli_stmt_close($stmtInsert);
        }
    }
    header("Location: juego.php?id=" . $idJuego);
    exit;
}

// --- CARGAR DATOS DEL JUEGO ---
$juego = null;
if ($idJuego > 0) {
    $sql = "SELECT v.* FROM Videojuego v WHERE v.id_videojuego = ? LIMIT 1";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idJuego);
    mysqli_stmt_execute($stmt);
    $juego = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

// --- CARGAR DATOS DEL USUARIO ---
$miEstado = ''; $misHoras = 0; $miPuntuacion = 0; $miTextoResena = '';
if ($juego && $idUsuario) {
    $sqlMiBib = "SELECT estado, horas_totales FROM Biblioteca WHERE id_usuario = ? AND id_videojuego = ?";
    $stmtMiBib = mysqli_prepare($conexion, $sqlMiBib);
    mysqli_stmt_bind_param($stmtMiBib, "ii", $idUsuario, $idJuego);
    mysqli_stmt_execute($stmtMiBib);
    if ($row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMiBib))) {
        $miEstado = $row['estado']; $misHoras = $row['horas_totales'];
    }
    mysqli_stmt_close($stmtMiBib);

    $sqlMiRes = "SELECT puntuacion, texto_resena FROM Resena WHERE id_usuario = ? AND id_videojuego = ?";
    $stmtMiRes = mysqli_prepare($conexion, $sqlMiRes);
    mysqli_stmt_bind_param($stmtMiRes, "ii", $idUsuario, $idJuego);
    mysqli_stmt_execute($stmtMiRes);
    if ($row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtMiRes))) {
        $miPuntuacion = (float) $row['puntuacion']; $miTextoResena = $row['texto_resena'] ?? '';
    }
    mysqli_stmt_close($stmtMiRes);
}

$resenas = [];
if ($juego) {
    $sqlResenas = "SELECT r.*, u.gameTag, u.avatar, b.estado, b.horas_totales FROM Resena r 
                   JOIN Usuario u ON u.id_usuario = r.id_usuario 
                   LEFT JOIN Biblioteca b ON b.id_usuario = r.id_usuario AND b.id_videojuego = r.id_videojuego 
                   WHERE r.id_videojuego = ? ORDER BY r.fecha_publicacion DESC LIMIT 50";
    $stmtRes = mysqli_prepare($conexion, $sqlResenas);
    mysqli_stmt_bind_param($stmtRes, "i", $idJuego);
    mysqli_stmt_execute($stmtRes);
    $res = mysqli_stmt_get_result($stmtRes);
    while ($f = mysqli_fetch_assoc($res)) $resenas[] = $f;
    mysqli_stmt_close($stmtRes);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - <?php echo $juego ? htmlspecialchars($juego['titulo']) : 'Ficha'; ?></title>
    <link rel="stylesheet" href="../../estilos/estilos_index.css"> <link rel="stylesheet" href="../../estilos/estilos_juego.css">
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
                <li><a href="juegos.php" class="activo">Juegos</a></li>
                <li><a href="../jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../tienda/tienda.php">Tienda</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <li><a href="../ranking/ranking.php">Ranking</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesión</a>
        <?php else: ?>
            <div class="user-actions">
                <div class="chat-wrapper" style="margin-right: 15px; display: inline-block; vertical-align: middle;">
                    <a href="../chat/bandeja.php" id="chat-icon" style="color: inherit; text-decoration: none; position: relative; display: flex; align-items: center;">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="26" height="26">
                            <path d="M12 2C6.477 2 2 6.14 2 11.25c0 2.457 1.047 4.675 2.75 6.275L4 21l3.75-1.5c1.33.4 2.76.625 4.25.625 5.523 0 10-4.14 10-9.25S17.523 2 12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span id="chat-badge" style="position: absolute; top: -5px; right: -5px; background: #ff4444; color: white; font-size: 10px; padding: 2px 5px; border-radius: 10px; display: none;">0</span>
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
    </header>

    <main>
        <?php if($juego): ?>
            <section class="fichaJuego">
                <div class="columnaIzquierda">
                    <div class="imagenJuego">
                        <img src="<?php echo htmlspecialchars(resolverPortada($juego['portada'])); ?>" alt="Portada">
                    </div>

                    <?php if ($idUsuario): ?>
                        <div class="panelInteraccion" id="mi-resena">
                            <h3>Mi Lista</h3>
                            <form action="" method="POST">
                                <input type="hidden" name="guardar_interaccion" value="1">
                                <div class="grupoFormulario">
                                    <label for="estado">Estado:</label>
                                    <select name="estado" id="estado">
                                        <option value="" <?php echo $miEstado === '' ? 'selected' : ''; ?>>Seleccionar...</option>
                                        <option value="Pendiente" <?php echo $miEstado === 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="Jugando" <?php echo $miEstado === 'Jugando' ? 'selected' : ''; ?>>Jugando</option>
                                        <option value="Completado" <?php echo $miEstado === 'Completado' ? 'selected' : ''; ?>>Completado</option>
                                        <option value="Abandonado" <?php echo $miEstado === 'Abandonado' ? 'selected' : ''; ?>>Abandonado</option>
                                    </select>
                                </div>
                                <div id="camposExtra" style="display: none;">
                                    <div class="grupoFormulario">
                                        <label for="horas">Horas:</label>
                                        <input type="number" step="0.1" name="horas" id="horas" value="<?php echo $misHoras; ?>">
                                    </div>
                                    <div class="clasificacionEstrellas" id="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <div class="estrella" data-value="<?php echo $i; ?>">★</div>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="puntuacion" id="puntuacion" value="<?php echo $miPuntuacion/2; ?>">
                                    <textarea name="texto_resena" placeholder="Reseña..."><?php echo htmlspecialchars($miTextoResena); ?></textarea>
                                </div>
                                <button type="submit" class="botonGuardar">Guardar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="contenidoJuego">
                    <h1><?php echo htmlspecialchars($juego['titulo']); ?></h1>
                    <p class="meta">
                        <?php 
                        $rating = $juego['rating_medio'];
                        if ($rating !== null) {
                            echo '<span class="estrellas"><span class="relleno" style="width:'.(($rating/10)*100).'%"></span></span> ' . number_format($rating, 1);
                        } else { echo 'Sin nota'; }
                        ?>
                    </p>
                    <p class="descripcion"><?php echo htmlspecialchars($juego['descripcion'] ?: 'Sin descripción.'); ?></p>
                    <a href="juegos.php" class="botonVolver">Volver</a>

                    <section class="seccionResenas">
                        <h2>Reseñas</h2>
                        <?php if (empty($resenas)): ?>
                            <p class="resenasVacias">Aún no hay reseñas.</p>
                        <?php else: ?>
                            <div class="listaResenas">
                                <?php foreach ($resenas as $r): ?>
                                    <?php
                                        $puntuacion10 = isset($r['puntuacion']) ? (float) $r['puntuacion'] : null; // 0..10
                                        $puntuacion5 = $puntuacion10 !== null ? ($puntuacion10 / 2) : null; // 0..5
                                        $relleno = $puntuacion10 !== null ? max(0, min(100, ($puntuacion10 / 10) * 100)) : 0;

                                        $estadoResena = isset($r['estado']) ? trim((string) $r['estado']) : '';
                                        $horasResena = isset($r['horas_totales']) ? (float) $r['horas_totales'] : 0;
                                        $fechaResena = isset($r['fecha_publicacion']) ? trim((string) $r['fecha_publicacion']) : '';
                                        $fechaResenaFmt = '';
                                        if ($fechaResena !== '') {
                                            $ts = strtotime($fechaResena);
                                            if ($ts !== false) $fechaResenaFmt = date('d/m/Y', $ts);
                                        }

                                        $textoResena = isset($r['texto_resena']) ? trim((string) $r['texto_resena']) : '';
                                    ?>
                                    <article class="resena">
                                        <div class="resenaHeader">
                                            <a href="../user/amistades/perfilOtros.php?id=<?php echo (int) $r['id_usuario']; ?>" class="resenaUsuario resenaUsuarioLink">
                                                <img src="<?php echo htmlspecialchars(resolverAvatar($r['avatar'])); ?>" class="resenaAvatar" alt="Avatar de <?php echo htmlspecialchars($r['gameTag']); ?>">
                                                <div class="resenaMeta">
                                                    <div class="resenaTag"><?php echo htmlspecialchars($r['gameTag']); ?></div>
                                                    <div class="resenaSubmeta">
                                                        <?php if ($estadoResena !== ''): ?>
                                                            <span><?php echo htmlspecialchars($estadoResena); ?></span>
                                                        <?php endif; ?>

                                                        <?php if ($estadoResena !== '' && $horasResena > 0): ?>
                                                            <span class="resenaPunto">•</span>
                                                        <?php endif; ?>
                                                        <?php if ($horasResena > 0): ?>
                                                            <span><?php echo rtrim(rtrim(number_format($horasResena, 1, '.', ''), '0'), '.'); ?> h</span>
                                                        <?php endif; ?>

                                                        <?php if (($estadoResena !== '' || $horasResena > 0) && $fechaResenaFmt !== ''): ?>
                                                            <span class="resenaPunto">•</span>
                                                        <?php endif; ?>
                                                        <?php if ($fechaResenaFmt !== ''): ?>
                                                            <span><?php echo htmlspecialchars($fechaResenaFmt); ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </a>

                                            <div class="resenaDerecha">
                                                <div class="resenaRating">
                                                    <?php if ($puntuacion10 !== null): ?>
                                                        <span class="estrellas" aria-label="<?php echo htmlspecialchars(number_format($puntuacion5, 1)); ?> de 5">
                                                            <span class="relleno" style="width:<?php echo $relleno; ?>%"></span>
                                                        </span>
                                                        <span><?php echo htmlspecialchars(number_format($puntuacion5, 1)); ?></span>
                                                    <?php else: ?>
                                                        <span class="resenaSubmeta">Sin nota</span>
                                                    <?php endif; ?>
                                                </div>

                                                <?php
                                                    $puedeGestionar = $idUsuario && (((int) $r['id_usuario']) === ((int) $idUsuario) || $admin);
                                                ?>
                                                <?php if ($puedeGestionar): ?>
                                                    <div class="resenaAcciones">
                                                        <?php if (((int) $r['id_usuario']) === ((int) $idUsuario)): ?>
                                                            <a class="resenaAccion resenaAccionEditar" href="#mi-resena">Editar</a>
                                                        <?php endif; ?>
                                                        <form method="POST" class="resenaAccionForm" onsubmit="return confirm('¿Eliminar esta reseña?');">
                                                            <input type="hidden" name="eliminar_resena" value="1">
                                                            <input type="hidden" name="id_resena" value="<?php echo (int) ($r['id_resena'] ?? 0); ?>">
                                                            <button type="submit" class="resenaAccion resenaAccionEliminar">Eliminar</button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if ($textoResena !== ''): ?>
                                            <div class="resenaTexto"><?php echo htmlspecialchars($textoResena); ?></div>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <script src="../../js/notificaciones.js"></script>
    <script src="../../js/social.js"></script> <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectEstado = document.getElementById('estado');
            const camposExtra = document.getElementById('camposExtra');
            if (selectEstado) {
                const update = () => camposExtra.style.display = (['Completado', 'Abandonado'].includes(selectEstado.value)) ? 'block' : 'none';
                selectEstado.onchange = update; update();
            }

            const estrellas = document.querySelectorAll('.estrella');
            const inputVal = document.getElementById('puntuacion');
            const pintar = (v) => estrellas.forEach((s, i) => s.classList.toggle('activa', (i + 1) <= v));
            estrellas.forEach((s, i) => s.onclick = () => { inputVal.value = i + 1; pintar(i + 1); });
            pintar(parseFloat(inputVal.value));
        });
    </script>
</body>
</html>
