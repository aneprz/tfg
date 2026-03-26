<?php
// 1. Iniciamos un búfer para atrapar cualquier Warning que quiera salir como texto
ob_start();
session_start();
header('Content-Type: application/json');

// Reporte de errores pero sin mostrarlos como HTML
error_reporting(E_ALL);
ini_set('display_errors', 0); 

require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = $_SESSION['id_usuario'] ?? 0;
// Recogemos el nombre
$nombre = isset($_POST['nombre']) ? mysqli_real_escape_string($conexion, $_POST['nombre']) : '';
// Recogemos los usuarios (vienen como JSON string desde el JS)
$usuarios_json = $_POST['usuarios'] ?? '[]';
$usuarios_ids = json_decode($usuarios_json, true);

$res = ['success' => false, 'error' => ''];

if (!$id_yo || empty($nombre)) {
    $res['error'] = 'Faltan datos o sesión expirada';
} else {
    // IMPORTANTE: He cambiado 'nombre' por 'nombre_grupo' que es como parece que lo tienes
    $sql = "INSERT INTO chat_conversacion (tipo, nombre_grupo, id_usuario_creador) 
            VALUES ('grupal', '$nombre', $id_yo)";

    if (mysqli_query($conexion, $sql)) {
        $id_conv = mysqli_insert_id($conexion);

        // 2. INSERTAR AL CREADOR (TÚ)
        mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES ($id_conv, $id_yo)");

        // 3. INSERTAR A LOS INVITADOS
        if (is_array($usuarios_ids)) {
            foreach ($usuarios_ids as $id_invitado) {
                $id_invitado = (int)$id_invitado;
                if($id_invitado > 0) {
                    mysqli_query($conexion, "INSERT INTO chat_participante (id_conversacion, id_usuario) VALUES ($id_conv, $id_invitado)");
                }
            }
        }
        $res['success'] = true;
    } else {
        $res['error'] = "Error SQL: " . mysqli_error($conexion);
    }
}

// 4. Limpiamos el búfer por si hubo algún "Notice" o "Warning" de PHP
ob_end_clean();

// 5. Enviamos SOLO el JSON
echo json_encode($res);
exit;