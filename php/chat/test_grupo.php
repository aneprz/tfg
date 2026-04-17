<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;
$id_conv = isset($_GET['id_conv']) ? (int)$_GET['id_conv'] : 0;

echo "ID Usuario: $id_yo<br>";
echo "ID Conversación: $id_conv<br><br>";

// Probar conexión
if ($conexion) {
    echo "Conexión a BD: OK<br><br>";
} else {
    echo "Error de conexión<br>";
}

// Probar obtener grupo
$sql = "SELECT * FROM chat_conversacion WHERE id_conversacion = $id_conv";
$res = mysqli_query($conexion, $sql);
if ($res) {
    $row = mysqli_fetch_assoc($res);
    echo "Grupo encontrado:<br>";
    print_r($row);
} else {
    echo "Error SQL: " . mysqli_error($conexion);
}
?>