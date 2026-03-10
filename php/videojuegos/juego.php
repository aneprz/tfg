<?php
session_start();
require '../../db/conexiones.php';

function estrellasDesdeRating($rating) {
    if ($rating === null || $rating === '') {
        return '☆☆☆☆☆';
    }

    $valor = (float) $rating;
    $llenas = (int) round($valor);
    $llenas = max(0, min(5, $llenas));

    return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}

function resolverAvatar($avatar) {
    $avatar = is_string($avatar) ? trim($avatar) : '';

    if ($avatar === '') {
        return '../../media/perfil_default.jpg';
    }

    if (preg_match('~^https?://~i', $avatar) === 1 || strpos($avatar, 'data:') === 0 || strpos($avatar, '/') === 0) {
        return $avatar;
    }

    $avatar = str_replace('\\', '/', $avatar);

    // Normalizamos algunos formatos históricos tipo ../../../media/...
    if (preg_match('~^(?:\\.\\./)+media/(.+)$~', $avatar, $m) === 1) {
        $avatar = 'media/' . $m[1];
    }

    $avatar = ltrim($avatar, '/');

    // Evitamos rutas con traversal.
    if (preg_match('~(^|/)\\.\\.(?:/|$)~', $avatar) === 1) {
        return '../../media/perfil_default.jpg';
    }

    if (strpos($avatar, '/') === false) {
        $avatar = 'media/' . $avatar;
    }

    $rutaWeb = '../../' . $avatar;
    $rutaFs = __DIR__ . '/../../' . $avatar;

    return is_file($rutaFs) ? $rutaWeb : '../../media/perfil_default.jpg';
}

function resolverPortada($portada) {
    $portada = is_string($portada) ? trim($portada) : '';

    if ($portada === '') {
        return '../../media/logoPlatino.png';
    }

    if (preg_match('~^https?://~i', $portada) === 1 || strpos($portada, 'data:') === 0) {
        return $portada;
    }

    // Normalizamos a ruta relativa al root del proyecto (este archivo está en /php/videojuegos/).
    $portada = str_replace('\\', '/', ltrim($portada, '/'));

    // Evitamos rutas con traversal.
    if (preg_match('~(^|/)\\.\\.(?:/|$)~', $portada) === 1) {
        return '../../media/logoPlatino.png';
    }

    // Si en BBDD viene solo el nombre del fichero, asumimos que está en /media/.
    if (strpos($portada, '/') === false) {
        $portada = 'media/' . $portada;
    }

    $rutaWeb = '../../' . $portada;
    $rutaFs = __DIR__ . '/../../' . $portada;

    return is_file($rutaFs) ? $rutaWeb : '../../media/logoPlatino.png';
}

$idJuego = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$juego = null;
$idUsuario = $_SESSION['id_usuario'] ?? null;

// --- PROCESAR FORMULARIO DE USUARIO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_interaccion']) && $idUsuario && $idJuego > 0) {
    $estado = $_POST['estado'] ?? null;
    
    // Verificamos si debemos procesar horas, puntuación y reseña
    $mostrarExtras = in_array($estado, ['Completado', 'Abandonado']);
    
    $horas = $mostrarExtras && isset($_POST['horas']) ? (float) $_POST['horas'] : 0;
    $puntuacion = $mostrarExtras && isset($_POST['puntuacion']) ? (int) $_POST['puntuacion'] : null;
    $texto_resena = $mostrarExtras && isset($_POST['texto_resena']) ? trim($_POST['texto_resena']) : null;

    // 1. Guardar/Actualizar en la tabla Biblioteca (Estado y Horas)
    $sqlBiblioteca = "INSERT INTO Biblioteca (id_usuario, id_videojuego, estado, horas_totales) 
                      VALUES (?, ?, ?, ?) 
                      ON DUPLICATE KEY UPDATE estado = VALUES(estado), horas_totales = VALUES(horas_totales)";
    $stmtBib = mysqli_prepare($conexion, $sqlBiblioteca);
    mysqli_stmt_bind_param($stmtBib, "iisd", $idUsuario, $idJuego, $estado, $horas);
    mysqli_stmt_execute($stmtBib);
    mysqli_stmt_close($stmtBib);

    // 2. Guardar/Actualizar en la tabla Resena (Solo si el estado es Completado/Abandonado)
    if ($mostrarExtras && ($puntuacion !== null || !empty($texto_resena))) {
        // Primero comprobamos si ya existe una reseña de este usuario para este juego
        $sqlCheck = "SELECT id_resena FROM Resena WHERE id_usuario = ? AND id_videojuego = ?";
        $stmtCheck = mysqli_prepare($conexion, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, "ii", $idUsuario, $idJuego);
        mysqli_stmt_execute($stmtCheck);
        $resCheck = mysqli_stmt_get_result($stmtCheck);
        $rowCheck = mysqli_fetch_assoc($resCheck);
        mysqli_stmt_close($stmtCheck);

        if ($rowCheck) {
            // Actualizamos la reseña existente
            $idResena = $rowCheck['id_resena'];
            $sqlUpdate = "UPDATE Resena SET puntuacion = ?, texto_resena = ?, fecha_publicacion = CURRENT_TIMESTAMP WHERE id_resena = ?";
            $stmtUpdate = mysqli_prepare($conexion, $sqlUpdate);
            mysqli_stmt_bind_param($stmtUpdate, "isi", $puntuacion, $texto_resena, $idResena);
            mysqli_stmt_execute($stmtUpdate);
            mysqli_stmt_close($stmtUpdate);
        } else {
            // Insertamos una nueva reseña
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
if (isset($conexion) && $conexion && $idJuego > 0) {
    $sql = "SELECT v.id_videojuego, v.titulo, v.descripcion, v.fecha_lanzamiento, v.developer, v.rating_medio, v.portada, v.genero AS generos
            FROM Videojuego v WHERE v.id_videojuego = ? LIMIT 1";
    try {
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $idJuego);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        if ($resultado) {
            $juego = mysqli_fetch_assoc($resultado) ?: null;
            mysqli_free_result($resultado);
        }
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        $juego = null;
        error_log('Error al cargar juego.php: ' . $e->getMessage());
    }
}

// --- CARGAR DATOS DEL USUARIO ---
$miEstado = '';
$misHoras = 0;
$miPuntuacion = 0;
$miTextoResena = '';

if ($juego && $idUsuario) {
    // Biblioteca
    $sqlMiBib = "SELECT estado, horas_totales FROM Biblioteca WHERE id_usuario = ? AND id_videojuego = ?";
    $stmtMiBib = mysqli_prepare($conexion, $sqlMiBib);
    mysqli_stmt_bind_param($stmtMiBib, "ii", $idUsuario, $idJuego);
    mysqli_stmt_execute($stmtMiBib);
    $resMiBib = mysqli_stmt_get_result($stmtMiBib);
    if ($row = mysqli_fetch_assoc($resMiBib)) {
        $miEstado = $row['estado'];
        $misHoras = $row['horas_totales'];
    }
    mysqli_stmt_close($stmtMiBib);

    // Resena
    $sqlMiRes = "SELECT puntuacion, texto_resena FROM Resena WHERE id_usuario = ? AND id_videojuego = ?";
    $stmtMiRes = mysqli_prepare($conexion, $sqlMiRes);
    mysqli_stmt_bind_param($stmtMiRes, "ii", $idUsuario, $idJuego);
    mysqli_stmt_execute($stmtMiRes);
    $resMiRes = mysqli_stmt_get_result($stmtMiRes);
    if ($row = mysqli_fetch_assoc($resMiRes)) {
        $miPuntuacion = (int) $row['puntuacion'];
        $miTextoResena = $row['texto_resena'] ?? '';
    }
    mysqli_stmt_close($stmtMiRes);
}

$resenas = [];
if ($juego && isset($conexion) && $conexion) {
    $sqlResenas = "
        SELECT
            r.id_usuario,
            u.gameTag,
            u.avatar,
            r.puntuacion,
            r.texto_resena,
            r.fecha_publicacion,
            b.estado,
            b.horas_totales
        FROM Resena r
        JOIN Usuario u ON u.id_usuario = r.id_usuario
        LEFT JOIN Biblioteca b ON b.id_usuario = r.id_usuario AND b.id_videojuego = r.id_videojuego
        WHERE r.id_videojuego = ?
          AND ((r.texto_resena IS NOT NULL AND r.texto_resena <> '') OR r.puntuacion IS NOT NULL)
        ORDER BY r.fecha_publicacion DESC
        LIMIT 50
    ";
    $stmtResenas = mysqli_prepare($conexion, $sqlResenas);
    if ($stmtResenas) {
        mysqli_stmt_bind_param($stmtResenas, "i", $idJuego);
        mysqli_stmt_execute($stmtResenas);
        $res = mysqli_stmt_get_result($stmtResenas);
        if ($res) {
            while ($fila = mysqli_fetch_assoc($res)) {
                $resenas[] = $fila;
            }
            mysqli_free_result($res);
        }
        mysqli_stmt_close($stmtResenas);
    }
}

$admin = ($_SESSION['admin'] ?? false) === true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Ficha de Juego</title>
    <link rel="stylesheet" href="../../estilos/estilos_juego.css">
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
                <li><a href="../../php/jugadores/jugadores.php">Jugadores</a></li>
                <li><a href="../comunidades/comunidades.php">Comunidades</a></li>
                <li><a href="../logros/logros.php">Logros</a></li>
                <?php if ($admin): ?>
                    <li><a href="../admin/indexAdmin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php if(!isset($_SESSION['tag'])) : ?>
            <a href="../../php/sesiones/login/login.php" class="botonCrearCuenta">Iniciar sesion</a>
        <?php else: ?>
            <a class="tag" href="../../php/user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>

    <main>
        <?php if($juego): ?>
            <section class="fichaJuego">
                <div class="columnaIzquierda">
                    <div class="imagenJuego">
                        <img src="<?php echo htmlspecialchars(resolverPortada($juego['portada'])); ?>" alt="Portada de <?php echo htmlspecialchars($juego['titulo']); ?>">
                    </div>

                    <?php if ($idUsuario): ?>
                        <div class="panelInteraccion">
                            <h3>Añadir a mi lista</h3>
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
                                        <label for="horas">Horas jugadas:</label>
                                        <input type="number" step="0.1" min="0" name="horas" id="horas" value="<?php echo htmlspecialchars((string)$misHoras); ?>">
                                    </div>

                                    <div class="grupoFormulario">
                                        <label>Tu puntuación:</label>
                                        <div class="clasificacionEstrellas">
                                            <?php for($i = 5; $i >= 1; $i--): ?>
                                                <input type="radio" id="star<?php echo $i; ?>" name="puntuacion" value="<?php echo $i; ?>" <?php echo $miPuntuacion === $i ? 'checked' : ''; ?> />
                                                <label for="star<?php echo $i; ?>">★</label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <div class="grupoFormulario">
                                        <label for="texto_resena">Reseña:</label>
                                        <textarea name="texto_resena" id="texto_resena" rows="3" placeholder="¿Qué te pareció el juego?"><?php echo htmlspecialchars($miTextoResena); ?></textarea>
                                    </div>
                                </div>

                                <button type="submit" class="botonGuardar">Guardar cambios</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="panelInteraccion avisoLogin">
                            <p><a href="../../php/sesiones/login/login.php">Inicia sesión</a> para puntuar y guardar este juego en tu biblioteca.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="contenidoJuego">
                    <h1><?php echo htmlspecialchars($juego['titulo']); ?></h1>
                    <p class="meta">
                        <?php
                            $partesMeta = [];
                            if (!empty($juego['generos'])) $partesMeta[] = $juego['generos'];
                            if (!empty($juego['fecha_lanzamiento'])) $partesMeta[] = date('Y', strtotime($juego['fecha_lanzamiento']));
                            if (!empty($juego['developer'])) $partesMeta[] = $juego['developer'];

                            $rating = $juego['rating_medio'];
                            $textoRating = $rating !== null ? estrellasDesdeRating($rating) . ' ' . number_format((float) $rating, 1) : 'Sin nota global';
                            $partesMeta[] = $textoRating;

                            echo htmlspecialchars(implode(' • ', $partesMeta));
                        ?>
                    </p>
                    <p class="descripcion"><?php echo htmlspecialchars($juego['descripcion'] ?: 'Este juego aun no tiene descripcion.'); ?></p>

                    <div class="bloqueProximamente">
                        <h2>Detalles del juego</h2>
                        <p><strong>Fecha de lanzamiento:</strong> <?php echo !empty($juego['fecha_lanzamiento']) ? htmlspecialchars($juego['fecha_lanzamiento']) : 'Sin fecha'; ?></p>
                        <p><strong>Desarrollador:</strong> <?php echo !empty($juego['developer']) ? htmlspecialchars($juego['developer']) : 'Sin developer'; ?></p>
                    </div>

                    <section class="seccionResenas" aria-label="Reseñas del juego">
                        <h2>Reseñas de usuarios</h2>
                        <?php if (count($resenas) === 0): ?>
                            <p class="resenasVacias">Todavía no hay reseñas para este juego.</p>
                        <?php else: ?>
                            <div class="listaResenas">
                                <?php foreach ($resenas as $resena): ?>
                                    <?php
                                        $fechaRaw = (string)($resena['fecha_publicacion'] ?? '');
                                        $fechaBonita = $fechaRaw !== '' ? date('d/m/Y H:i', strtotime($fechaRaw)) : '';
                                    ?>
                                    <article class="resena">
                                        <div class="resenaHeader">
                                            <div class="resenaUsuario">
                                                <img class="resenaAvatar" src="<?php echo htmlspecialchars(resolverAvatar($resena['avatar'] ?? '')); ?>" alt="Avatar de <?php echo htmlspecialchars($resena['gameTag']); ?>">
                                                <div class="resenaMeta">
                                                    <div class="resenaTag"><?php echo htmlspecialchars($resena['gameTag']); ?></div>
                                                    <div class="resenaSubmeta">
                                                        <?php if ($fechaBonita !== ''): ?>
                                                            <span class="resenaFecha"><?php echo htmlspecialchars($fechaBonita); ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($resena['estado'])): ?>
                                                            <span class="resenaPunto">•</span>
                                                            <span class="resenaEstado"><?php echo htmlspecialchars($resena['estado']); ?></span>
                                                        <?php endif; ?>
                                                        <?php if (isset($resena['horas_totales']) && (float)$resena['horas_totales'] > 0): ?>
                                                            <span class="resenaPunto">•</span>
                                                            <span class="resenaHoras"><?php echo htmlspecialchars(number_format((float)$resena['horas_totales'], 1)); ?> h</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="resenaRating" aria-label="Puntuación">
                                                <?php echo htmlspecialchars(estrellasDesdeRating($resena['puntuacion'])); ?>
                                            </div>
                                        </div>

                                        <?php if (!empty($resena['texto_resena'])): ?>
                                            <p class="resenaTexto"><?php echo nl2br(htmlspecialchars($resena['texto_resena'])); ?></p>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <a href="juegos.php" class="botonVolver">Volver al catalogo</a>
                </div>
            </section>
        <?php else: ?>
            <section class="fichaJuego vacio">
                <h1>Juego no encontrado</h1>
                <p>La ficha solicitada no existe en la base de datos actual.</p>
                <a href="juegos.php" class="botonVolver">Ir a juegos</a>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2026 SalsaBox. Creado para los gamers.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectEstado = document.getElementById('estado');
            const camposExtra = document.getElementById('camposExtra');

            function toggleCampos() {
                if (!selectEstado) return;
                
                if (selectEstado.value === 'Completado' || selectEstado.value === 'Abandonado') {
                    camposExtra.style.display = 'block';
                } else {
                    camposExtra.style.display = 'none';
                }
            }

            if (selectEstado) {
                // Escuchar cambios en el select
                selectEstado.addEventListener('change', toggleCampos);
                // Ejecutar una vez al cargar la página por si ya tenía guardado "Completado"
                toggleCampos();
            }
        });
    </script>
</body>
</html>
