<?php
session_start();
require '../../db/conexiones.php';

$id_usuario = $_SESSION['id_usuario'];
$id_item = (int) $_POST['id_item'];

// Tipo del item
$res = mysqli_query($conexion, "
SELECT tipo FROM Tienda_Items WHERE id_item = $id_item
");

$tipo = mysqli_fetch_assoc($res)['tipo'];

// Desequipar mismo tipo
mysqli_query($conexion, "
UPDATE Usuario_Items ui
JOIN Tienda_Items ti ON ti.id_item = ui.id_item
SET ui.equipado = 0
WHERE ui.id_usuario = $id_usuario AND ti.tipo = '$tipo'
");

// Equipar
mysqli_query($conexion, "
UPDATE Usuario_Items
SET equipado = 1
WHERE id_usuario = $id_usuario AND id_item = $id_item
");

header("Location: inventario.php");
exit;