<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

// Verificación de sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión no iniciada']);
    exit;
}

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = isset($_POST['id_conv']) ? (int)$_POST['id_conv'] : 0;
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

if ($id_conv <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de conversación no válido']);
    exit;
}

// 1. Obtener info del creador para validar permisos
$resC = mysqli_query($conexion, "SELECT id_usuario_creador FROM chat_conversacion WHERE id_conversacion = $id_conv");
$rowC = mysqli_fetch_assoc($resC);
if (!$rowC) {
    echo json_encode(['success' => false, 'error' => 'Grupo no encontrado']);
    exit;
}
$id_creador = (int)$rowC['id_usuario_creador'];

// --- ACCIÓN: QUITAR MIEMBRO (Solo Creador) ---
if ($accion === 'quitar') {
    $id_target = (int)$_POST['id_user'];
    
    // Solo el creador puede quitar a otros y no puede quitarse a sí mismo (para eso es abandonar)
    if ($id_yo === $id_creador && $id_target !== $id_creador) {
        $sql = "DELETE FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_target";
        if (mysqli_query($conexion, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar miembro']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No tienes permiso o acción inválida']);
    }
} 

// --- ACCIÓN: AÑADIR MIEMBRO ---
elseif ($accion === 'añadir') {
    $id_target = (int)$_POST['id_user'];
    
    // Verificar si ya está en el grupo
    $check = mysqli_query($conexion, "SELECT 1 FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_target");
    if (mysqli_num_rows($check) > 0) {
        echo json_encode(['success' => false, 'error' => 'El usuario ya es miembro']);
        exit;
    }

    $sql = "INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES ($id_conv, $id_target)";
    if (mysqli_query($conexion, $sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al añadir miembro']);
    }
}

// --- ACCIÓN: ABANDONAR GRUPO ---
elseif ($accion === 'abandonar') {
    // Si el creador abandona, podrías querer asignar a otro o borrar el grupo. 
    // Aquí simplemente lo eliminamos de la tabla de participantes.
    $sql = "DELETE FROM chat_participante WHERE id_conversacion = $id_conv AND id_usuario = $id_yo";
    
    if (mysqli_query($conexion, $sql)) {
        // Opcional: Si el grupo se queda sin nadie, borrar conversación
        $resCount = mysqli_query($conexion, "SELECT COUNT(*) as total FROM chat_participante WHERE id_conversacion = $id_conv");
        $rowCount = mysqli_fetch_assoc($resCount);
        if ((int)$rowCount['total'] === 0) {
            mysqli_query($conexion, "DELETE FROM chat_conversacion WHERE id_conversacion = $id_conv");
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al intentar salir']);
    }
} 

else {
    echo json_encode(['success' => false, 'error' => 'Acción no reconocida']);
}