<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: jugadores.php");
    exit();
}

$id_sesion = $_SESSION['id_usuario'];
$id_objetivo = (int)$_GET['id'];

if ($id_sesion == $id_objetivo) {
    header("Location: ../perfiles/perfilSesion.php");
    exit();
}

$query = $conexion->prepare("SELECT gameTag, biografia, avatar, puntos_actuales FROM Usuario WHERE id_usuario = ?");
$query->bind_param("i", $id_objetivo);
$query->execute();
$Usuario = $query->get_result()->fetch_assoc();
$query->close();

if (!$Usuario) { die("Usuario no encontrado."); }

$q_amigos = $conexion->prepare("
    SELECT COUNT(*) as total 
    FROM Amigos a
    INNER JOIN Usuario u1 ON a.id_usuario = u1.id_usuario
    INNER JOIN Usuario u2 ON a.id_amigo = u2.id_usuario
    WHERE (a.id_usuario = ? OR a.id_amigo = ?) 
    AND a.estado = 'aceptada'
");
$q_amigos->bind_param("ii", $id_objetivo, $id_objetivo);
$q_amigos->execute();
$total_amigos = (int)$q_amigos->get_result()->fetch_assoc()['total'];
$q_amigos->close();

$q_juegos = $conexion->prepare("
    SELECT COUNT(b.id_videojuego) as total 
    FROM Biblioteca b
    INNER JOIN Videojuego v ON b.id_videojuego = v.id_videojuego
    WHERE b.id_usuario = ?
");
$q_juegos->bind_param("i", $id_objetivo);
$q_juegos->execute();
$total_juegos = (int)$q_juegos->get_result()->fetch_assoc()['total'];
$q_juegos->close();

$total_puntos = (int) ($Usuario['puntos_actuales'] ?? 0);

$q_relacion = $conexion->prepare("SELECT id_usuario, estado FROM Amigos WHERE (id_usuario = ? AND id_amigo = ?) OR (id_usuario = ? AND id_amigo = ?)");
$q_relacion->bind_param("iiii", $id_sesion, $id_objetivo, $id_objetivo, $id_sesion);
$q_relacion->execute();
$relacion = $q_relacion->get_result()->fetch_assoc();
$q_relacion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo htmlspecialchars($Usuario['gameTag']); ?> - SalsaBox</title>
    <link rel="icon" href="../../../media/logoPlatino.png">
    <link rel="stylesheet" href="../../../estilos/estilos_perfilOtros.css">
</head>
<body>
    <div class="perfil-container">
        <div class="perfil-card">
            <section class="perfil-header">
                <?php 
                    $avatar_db = trim((string)$Usuario['avatar']);
                    $img = (empty($avatar_db)) ? "../../../media/perfil_default.jpg" : "../../../media/" . $avatar_db;
                ?>
                <img src="<?php echo htmlspecialchars($img); ?>" class="avatar-grande" style="object-fit: cover;">
                <h1><?php echo htmlspecialchars($Usuario['gameTag']); ?></h1>
                <div class="status">Jugador de SalsaBox</div>
                <form action="gestionarAmistades.php" method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="id_objetivo" value="<?php echo $id_objetivo; ?>">
                    <?php if (!$relacion): ?>
                        <button type="submit" name="accion" value="enviar" class="btn-accion btn-add">Añadir Amigo</button>
                    <?php elseif ($relacion['estado'] == 'pendiente'): ?>
                        <?php if ($relacion['id_usuario'] == $id_sesion): ?>
                            <button type="button" class="btn-accion" style="background: #444; cursor: default; color: #fff;">Solicitud Enviada</button>
                            <button type="submit" name="accion" value="eliminar" class="btn-logout">Cancelar solicitud</button>
                        <?php else: ?>
                            <button type="submit" name="accion" value="aceptar" class="btn-accion btn-add">Aceptar Solicitud</button>
                            <button type="submit" name="accion" value="eliminar" class="btn-logout">Rechazar</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button type="button" class="btn-accion" style="border: 1px solid #e0be00; background: none; color: #e0be00; cursor: default;">✓ Amigos</button>
                        <button type="submit" name="accion" value="eliminar" class="btn-logout" onclick="return confirm('¿Eliminar de amigos?')">Eliminar amigo</button>
                    <?php endif; ?>
                </form>
            </section>
            <section class="perfil-stats">
                <a href="juegosOtros.php?id=<?php echo $id_objetivo; ?>" class="stat-link">
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $total_juegos; ?></span>
                        <span class="stat-label">Juegos</span>
                    </div>
                </a>
                <a href="logrosOtros.php?id=<?php echo $id_objetivo; ?>" class="stat-link">
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $total_puntos; ?></span>
                        <span class="stat-label">Puntos</span>
                    </div>
                </a>
                <a href="amigosOtros.php?id=<?php echo $id_objetivo; ?>" class="stat-link">
                    <div class="stat-item">
                        <span class="stat-num"><?php echo $total_amigos; ?></span>
                        <span class="stat-label">Amigos</span>
                    </div>
                </a>
            </section>
            <div class="perfil-body">
                <h3>Sobre este gamer</h3>
                <p class="bio-text">
                    <?php 
                    $bio = trim((string)$Usuario['biografia']);
                    echo $bio !== "" ? nl2br(htmlspecialchars($bio)) : "Este gamer prefiere mantener el misterio.";
                    ?>
                </p>
            </div>
            <div class="perfil-footer">
                <a href="../../../php/jugadores/jugadores.php" class="btn-volver">← Volver a Explorar</a>
            </div>
        </div>
    </div>
</body>
</html>
