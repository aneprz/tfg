<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../db/conexiones.php';

$id_propio = $_SESSION['id_usuario'] ?? 0;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$es_ajax = isset($_GET['ajax']);

if (!empty($busqueda)) {
    $sql = "SELECT id_usuario, gameTag, avatar, biografia 
            FROM Usuario 
            WHERE gameTag LIKE ? AND id_usuario != ? 
            ORDER BY gameTag ASC";
    $stmt = $conexion->prepare($sql);
    $termino = "%$busqueda%";
    $stmt->bind_param("si", $termino, $id_propio);
} else {
    $sql = "SELECT id_usuario, gameTag, avatar, biografia 
            FROM Usuario 
            WHERE id_usuario != ? 
            ORDER BY fecha_registro DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_propio);
}

$stmt->execute();
$resultado = $stmt->get_result();

if ($es_ajax) {
    if ($resultado->num_rows > 0) {
        while($user = $resultado->fetch_assoc()) {
            $img = !empty($user['avatar']) ? "../../".$user['avatar'] : "../../media/defaultAvatar.png";
            $bio = $user['biografia'] ?? 'Sin biografía disponible.';
            $bio_corta = strlen($bio) > 60 ? substr($bio, 0, 60) . "..." : $bio;
            
            echo '<div class="player-card">
                    <div class="player-avatar-wrapper">
                        <img src="'.htmlspecialchars($img).'" alt="Avatar">
                    </div>
                    <div class="player-info">
                        <h3>'.htmlspecialchars($user['gameTag']).'</h3>
                        <p class="player-bio">'.htmlspecialchars($bio_corta).'</p>
                        <a href="../user/amistades/perfilOtros.php?id='.$user['id_usuario'].'" class="btn-ver-perfil">Ver Perfil</a>
                    </div>
                </div>';
        }
    } else {
        echo '<div class="no-results"><p>No se encontraron jugadores que coincidan.</p></div>';
    }
    exit;
}