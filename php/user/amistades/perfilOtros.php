<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['id_usuario']) || !isset($_GET['id'])) {
    header("Location: jugadores.php");
    exit();
}

$id_sesion = $_SESSION['id_usuario'];
$id_objetivo = $_GET['id'];

if ($id_sesion == $id_objetivo) {
    header("Location: perfilSesion.php");
    exit();
}

$query = $conexion->prepare("SELECT gameTag, biografia, avatar FROM Usuario WHERE id_usuario = ?");
$query->bind_param("i", $id_objetivo);
$query->execute();
$usuario = $query->get_result()->fetch_assoc();

if (!$usuario) { die("Usuario no encontrado."); }

$q_amigos = $conexion->prepare("SELECT COUNT(*) as total FROM Amigos WHERE (id_usuario = ? OR id_amigo = ?) AND estado = 'aceptada'");
$q_amigos->bind_param("ii", $id_objetivo, $id_objetivo);
$q_amigos->execute();
$total_amigos = $q_amigos->get_result()->fetch_assoc()['total'];

$q_juegos = $conexion->prepare("SELECT COUNT(*) as total FROM Biblioteca WHERE id_usuario = ?");
$q_juegos->bind_param("i", $id_objetivo);
$q_juegos->execute();
$total_juegos = $q_juegos->get_result()->fetch_assoc()['total'];

$q_puntos = $conexion->prepare("
    SELECT SUM(l.puntos_logro) as total 
    FROM Logros_Usuario lu 
    JOIN Logros l ON lu.id_logro = l.id_logro 
    WHERE lu.id_usuario = ?");
$q_puntos->bind_param("i", $id_objetivo);
$q_puntos->execute();
$total_puntos = $q_puntos->get_result()->fetch_assoc()['total'] ?? 0;

$q_relacion = $conexion->prepare("SELECT id_usuario, estado FROM Amigos WHERE (id_usuario = ? AND id_amigo = ?) OR (id_usuario = ? AND id_amigo = ?)");
$q_relacion->bind_param("iiii", $id_sesion, $id_objetivo, $id_objetivo, $id_sesion);
$q_relacion->execute();
$relacion = $q_relacion->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo $usuario['gameTag']; ?> - SalsaBox</title>
    <link rel="icon" href="../../../media/logoPlatino.png">
    <link rel="stylesheet" href="../../../estilos/estilos_perfilOtros.css">
    <style>
        /* Estilo para que los enlaces de stats no parezcan enlaces azules feos */
        .stat-link {
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s;
        }
        .stat-link:hover .stat-item {
            transform: translateY(-5px);
            background: rgba(224, 190, 0, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="perfil-container">
        <div class="perfil-card">
            <section class="perfil-header">
                <?php $img = !empty($usuario['avatar']) ? "../../../".$usuario['avatar'] : "../../../media/perfil_default.jpg"; ?>
                <img src="<?php echo $img; ?>" class="avatar-grande" style="object-fit: cover;">
                
                <h1><?php echo htmlspecialchars($usuario['gameTag']); ?></h1>
                <div class="status">Jugador de SalsaBox</div>

                <form action="gestionarAmistades.php" method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="id_objetivo" value="<?php echo $id_objetivo; ?>">
                    
                    <?php if (!$relacion): ?>
                        <button type="submit" name="accion" value="enviar" class="btn-accion btn-add">Añadir Amigo</button>
                    
                    <?php elseif ($relacion['estado'] == 'pendiente'): ?>
                        <?php if ($relacion['id_usuario'] == $id_sesion): ?>
                            <button type="button" class="btn-accion" style="background: #444; cursor: default;">Solicitud Enviada</button>
                            <button type="submit" name="accion" value="eliminar" class="btn-logout" style="display:block; margin: 10px auto; color: #ff4444; background: none; border: none; cursor:pointer;">Cancelar solicitud</button>
                        <?php else: ?>
                            <button type="submit" name="accion" value="aceptar" class="btn-accion btn-add">Aceptar Solicitud</button>
                            <button type="submit" name="accion" value="eliminar" class="btn-logout" style="display:block; margin: 10px auto; color: #ff4444; background: none; border: none; cursor:pointer;">Rechazar</button>
                        <?php endif; ?>
                    
                    <?php else: ?>
                        <button type="button" class="btn-accion" style="border: 1px solid #e0be00; background: none; color: #e0be00; cursor: default;">✓ Amigos</button>
                        <button type="submit" name="accion" value="eliminar" class="btn-logout" style="display:block; margin: 10px auto; color: #ff4444; background: none; border: none; cursor:pointer;">Eliminar amigo</button>
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
                <p><?php echo htmlspecialchars($usuario['biografia'] ?: "Este gamer prefiere mantener el misterio."); ?></p>
            </div>

            <div class="perfil-footer">
                <a href="../../../php/jugadores/jugadores.php" class="btn-volver">← Volver a Explorar</a>
            </div>
        </div>
    </div>
</body>
</html>