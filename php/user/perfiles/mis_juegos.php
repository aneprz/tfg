<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../../index.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$resolverPortada = function ($portada): string {
    $portada = is_string($portada) ? trim($portada) : '';

    if ($portada === '') {
        return '../../../media/default_game.png';
    }

    if (preg_match('~^https?://~i', $portada) === 1 || strpos($portada, 'data:') === 0) {
        return $portada;
    }

    $portada = str_replace('\\', '/', ltrim($portada, '/'));

    if (preg_match('~(^|/)\\.\\.(?:/|$)~', $portada) === 1) {
        return '../../../media/default_game.png';
    }

    if (strpos($portada, '/') === false) {
        $portada = 'media/' . $portada;
    }

    $rutaWeb = '../../../' . $portada;
    $rutaFs = __DIR__ . '/../../../' . $portada;

    return is_file($rutaFs) ? $rutaWeb : '../../../media/default_game.png';
};

function estrellasDesdePuntuacion($puntuacion): string
{
    if ($puntuacion === null || $puntuacion === '') {
        return '☆☆☆☆☆';
    }

    $valor = $puntuacion / 2;

    $enteras = floor($valor);
    $media = ($valor - $enteras) >= 0.5 ? 1 : 0;
    $vacias = 5 - $enteras - $media;

    return str_repeat('★', $enteras)
         . ($media ? '⯪' : '') // media estrella
         . str_repeat('☆', $vacias);
}

$query = $conexion->prepare("
    SELECT v.id_videojuego, v.titulo, v.portada, b.estado, r.puntuacion
    FROM Biblioteca b
    JOIN Videojuego v ON b.id_videojuego = v.id_videojuego
    LEFT JOIN Resena r ON r.id_usuario = b.id_usuario AND r.id_videojuego = b.id_videojuego
    WHERE b.id_usuario = ?
");
$query->bind_param("i", $id_usuario);
$query->execute();
$resultado = $query->get_result();
$total_juegos = $resultado->num_rows;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Juegos - SalsaBox</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">

</head>
<body>
    <div class="container-lista" style="width: 100%; max-width: 900px; margin: 0 auto; padding: 20px;">
        <a href="perfilSesion.php" class="btn-volver" style="color: #9ab3bc; text-decoration: none;">← Volver al Perfil</a>
        
        <h1 style="color: #e0be00; border-bottom: 2px solid #e0be00; padding-bottom: 10px;">
            Mi Biblioteca (<?php echo $total_juegos; ?>)
        </h1>

        <?php if ($total_juegos > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Portada</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Puntuación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $resultado->fetch_assoc()): ?>
                    <?php $urlJuego = "../../videojuegos/juego.php?id=" . (int)$row['id_videojuego']; ?>
                    <tr data-href="<?php echo htmlspecialchars($urlJuego); ?>">
                        <td>
                            <a href="<?php echo htmlspecialchars($urlJuego); ?>" style="display:inline-block;">
                                <img src="<?php echo htmlspecialchars($resolverPortada($row['portada'])); ?>" class="portada" alt="Juego">
                            </a>
                        </td>
                        <td style="font-weight: bold; color: #e0be00;">
                            <a href="<?php echo htmlspecialchars($urlJuego); ?>" style="color: inherit; text-decoration: none;">
                                <?php echo htmlspecialchars($row['titulo']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="status-badge">
                                <?php echo htmlspecialchars($row['estado']); ?>
                            </span>
                        </td>
                        <td style="color: #9ab3bc;">
                            <?php
                                $p = (float)$row['puntuacion']; // 0..10
                                $relleno = ($p / 10) * 100;
                            ?>
                            <span class="estrellas">
                                <span class="relleno" style="width: <?php echo $relleno; ?>%"></span>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="item-card" style="border-left: 4px solid #ff4d4d; margin-top: 20px;">
                <p>Parece que tu biblioteca está vacía. ¡Añade algunos juegos para empezar a trackear tus horas!</p>
            </div>
        <?php endif; ?>
    </div>
    <script>
        document.querySelectorAll('tr[data-href]').forEach(function (row) {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function (e) {
                if (e.target && e.target.closest && e.target.closest('a, button, input, textarea, select, label')) {
                    return;
                }
                window.location.href = row.getAttribute('data-href');
            });
        });
    </script>
</body>
</html>
