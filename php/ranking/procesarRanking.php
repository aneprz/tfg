<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../db/conexiones.php';

header('Content-Type: application/json');

$id_propio = $_SESSION['id_usuario'] ?? 0;
$tipo = $_GET['tipo'] ?? 'global';
$orden = $_GET['orden'] ?? 'puntos';

$datos = [];

if ($orden === 'juegos') {
    $select_extra = ", COUNT(b.id_videojuego) AS valor";
    $join_extra = "LEFT JOIN Biblioteca b ON u.id_usuario = b.id_usuario";
    $group_by = "GROUP BY u.id_usuario";
    $order_by = "ORDER BY valor DESC";
} else {
    $select_extra = ", u.puntos_actuales AS valor";
    $join_extra = "";
    $group_by = "";
    $order_by = "ORDER BY u.puntos_actuales DESC";
}

if ($tipo === 'amigos') {
    $join_amigos = "JOIN (
        SELECT id_amigo AS relevant_id FROM Amigos WHERE id_usuario = $id_propio AND estado = 'aceptada'
        UNION
        SELECT id_usuario AS relevant_id FROM Amigos WHERE id_amigo = $id_propio AND estado = 'aceptada'
        UNION
        SELECT $id_propio AS relevant_id
    ) r ON u.id_usuario = r.relevant_id";
    $where_amigos = "";
} else {
    $join_amigos = "";
    $where_amigos = "";
}

$sql = "SELECT u.id_usuario, u.gameTag, u.avatar, u.biografia $select_extra 
        FROM Usuario u 
        $join_amigos 
        $join_extra 
        $where_amigos 
        $group_by 
        $order_by 
        LIMIT 50";

$resultado = mysqli_query($conexion, $sql);

if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $avatar_db = trim($fila['avatar'] ?? '');
        $fila['avatar_url'] = empty($avatar_db) ? "../../media/perfil_default.jpg" : "../../media/" . $avatar_db;
        $datos[] = $fila;
    }
} else {
    echo json_encode(['error' => mysqli_error($conexion)]);
    exit;
}

echo json_encode($datos);
?>