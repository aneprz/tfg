<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../db/conexiones.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = isset($_POST['id_conv']) ? (int)$_POST['id_conv'] : 0;
$accion = $_POST['accion'] ?? '';

if ($id_conv <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit;
}

// Obtener creador del grupo
$resC = mysqli_query($conexion, "SELECT id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv");
$rowC = mysqli_fetch_assoc($resC);
$id_creador = (int)$rowC['id_usuario_creador'];

// QUITAR miembro (solo creador)
if ($accion === 'quitar') {
    $id_target = (int)$_POST['id_user'];
    if ($id_yo === $id_creador && $id_target !== $id_creador) {
        $sql = "DELETE FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_target";
        echo json_encode(['success' => mysqli_query($conexion, $sql)]);
    } else {
        echo json_encode(['success' => false]);
    }
}
// AÑADIR miembro
elseif ($accion === 'añadir') {
    $id_target = (int)$_POST['id_user'];
    $check = mysqli_query($conexion, "SELECT 1 FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_target");
    if (mysqli_num_rows($check) == 0) {
        $sql = "INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES ($id_conv, $id_target)";
        echo json_encode(['success' => mysqli_query($conexion, $sql)]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Ya es miembro']);
    }
}
// ABANDONAR grupo
elseif ($accion === 'abandonar') {
    $sql = "DELETE FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_yo";
    if (mysqli_query($conexion, $sql)) {
        // Si el grupo se queda vacío, lo borramos
        $resCount = mysqli_query($conexion, "SELECT COUNT(*) as total FROM chat_participante WHERE id_conversacion = $id_conv");
        $count = mysqli_fetch_assoc($resCount);
        if ((int)$count['total'] === 0) {
            mysqli_query($conexion, "DELETE FROM chat_conversacion WHERE id_conversacion = $id_conv");
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
}
?>