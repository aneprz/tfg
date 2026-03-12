<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_GET['id'])) {
    header("Location: logros.php");
    exit();
}

$id = (int)$_GET['id'];

/* INFO DEL JUEGO */

$stmt = $conexion->prepare("
    SELECT titulo, portada 
    FROM Videojuego 
    WHERE id_videojuego = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$juego = $stmt->get_result()->fetch_assoc();

if (!$juego) {
    header("Location: logros.php");
    exit();
}

/* LOGROS */

$stmt = $conexion->prepare("
    SELECT nombre_logro, descripcion, puntos_logro, icono
    FROM Logros
    WHERE id_videojuego = ?
    ORDER BY puntos_logro DESC
");

$stmt->bind_param("i", $id);
$stmt->execute();
$logros = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>
        Logros - <?php echo htmlspecialchars($juego['titulo']); ?>
    </title>

    <link rel="stylesheet" href="../../estilos/estilos_index.css">
    <link rel="stylesheet" href="../../estilos/estilos_logros.css">

</head>

<body>

    <header>

        <a href="logros.php" class="botonVolver">
            Volver al catalogo
        </a>

    </header>

    <main>

        <h1>
            <?php echo htmlspecialchars($juego['titulo']); ?>
        </h1>

        <div class="logros-grid">

            <?php while ($logro = $logros->fetch_assoc()): ?>

                <div class="logro-card">

                    <div class="logro-icono">

                        <?php if ($logro['icono']): ?>

                            <img 
                                src="<?php echo htmlspecialchars($logro['icono']); ?>" 
                                width="40"
                            >

                        <?php else: ?>

                            🏆

                        <?php endif; ?>

                    </div>

                    <div class="logro-info">

                        <h3>
                            <?php echo htmlspecialchars($logro['nombre_logro']); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars($logro['descripcion']); ?>
                        </p>

                        <span class="puntos">
                            <?php echo $logro['puntos_logro']; ?> G
                        </span>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    </main>

</body>
</html>