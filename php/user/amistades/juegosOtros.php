<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: ../../../index.php");
    exit();    
}

$id_objetivo = $_GET['id'];

$queryUser = $conexion->prepare("SELECT gameTag FROM Usuario WHERE id_usuario = ?");
$queryUser->bind_param("i", $id_objetivo);
$queryUser->execute();
$Usuario = $queryUser->get_result()->fetch_assoc();

if (!$Usuario) die("Usuario no encontrado");

$sql = "SELECT v.titulo, v.portada, b.horas_totales 
        FROM Biblioteca b 
        JOIN Videojuego v ON b.id_videojuego = v.id_videojuego 
        WHERE b.id_usuario = ?";
$query = $conexion->prepare($sql);
$query->bind_param("i", $id_objetivo);
$query->execute();
$resultado = $query->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca de <?php echo htmlspecialchars($Usuario['gameTag']); ?> - SalsaBox</title>
    <link rel="stylesheet" href="../../../estilos/estilos_statsPerfil.css">
    <link rel="icon" href="../../../media/logoPlatino.png">
    
    <style>
        /* =========================================================
           DISEÑO LIMPIO (Sin estilos inline)
           ========================================================= */
        body {
            background-color: #14181c; /* Color de fondo oscuro habitual de tu web */
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container-lista {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .btn-volver {
            color: #9ab3bc;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
            transition: color 0.2s;
        }

        .btn-volver:hover {
            color: #fff;
        }

        .section-title {
            color: #e0be00;
            border-bottom: 2px solid #e0be00;
            padding-bottom: 10px;
            margin-top: 0;
            font-size: 2rem;
        }

        .grid-juegos {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px; /* Espacio entre los juegos */
        }

        .item-card {
            display: flex;
            align-items: center;
            background: #1b2129;
            padding: 15px;
            border-radius: 12px;
            border: 1px solid #2c3440;
            transition: transform 0.2s, border-color 0.2s;
        }

        .item-card:hover {
            transform: translateX(5px);
            border-color: #e0be00;
        }

        .item-portada {
            width: 100px;
            height: 140px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 20px;
            border: 1px solid #444;
            flex-shrink: 0; /* Impide que la imagen se aplaste si el título es muy largo */
        }

        .item-content {
            flex: 1;
        }

        .item-title {
            margin: 0;
            color: #e0be00;
            font-size: 1.4rem;
        }

        .item-desc {
            margin: 10px 0 0;
            color: #9ab3bc;
            font-size: 1rem;
        }

        .empty-msg {
            color: #9ab3bc;
            font-style: italic;
            text-align: center;
            margin-top: 50px;
        }

        /* =========================================================
           📱 RESPONSIVE: MÓVILES (Pantallas < 600px)
           ========================================================= */
        @media (max-width: 600px) {
            .container-lista {
                padding: 15px;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .item-card {
                padding: 10px; /* Reducimos el aire interior */
            }

            .item-portada {
                width: 70px; /* Portada más pequeña */
                height: 100px;
                margin-right: 15px;
            }

            .item-title {
                font-size: 1.1rem; /* Letra más pequeña */
            }

            .item-desc {
                font-size: 0.85rem;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container-lista">
        <a href="perfilOtros.php?id=<?php echo $id_objetivo; ?>" class="btn-volver">← Volver al Perfil</a>
        
        <h1 class="section-title">
            Biblioteca de <?php echo htmlspecialchars($Usuario['gameTag']); ?>
        </h1>

        <div class="grid-juegos">
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($row = $resultado->fetch_assoc()): ?>
                    <div class="item-card">
                        <img src="../../../media/<?php echo !empty($row['portada']) ? htmlspecialchars($row['portada']) : 'logoPlatino.png'; ?>" class="item-portada" alt="Portada de <?php echo htmlspecialchars($row['titulo']); ?>">
                        
                        <div class="item-content">
                            <h3 class="item-title">
                                <?php echo htmlspecialchars($row['titulo']); ?>
                            </h3>
                            <p class="item-desc">
                                <strong>Tiempo:</strong> <?php echo number_format($row['horas_totales'], 1); ?> horas
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="empty-msg">
                    Este Usuario aún no ha añadido juegos a su biblioteca.
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>