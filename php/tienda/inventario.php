<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id = $_SESSION['id_usuario'];

$res = mysqli_query($conexion, "
SELECT ui.*, ti.nombre, ti.tipo, ti.imagen
FROM Usuario_Items ui
JOIN Tienda_Items ti ON ti.id_item = ui.id_item
WHERE ui.id_usuario = $id
");

$items = [];

while($row = mysqli_fetch_assoc($res)){
    $items[] = $row;
}
?>