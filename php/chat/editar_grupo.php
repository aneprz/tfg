<?php
session_start();
require_once __DIR__ . '/../../db/conexiones.php';

$id_yo = (int)$_SESSION['id_usuario'];
$id_conv = (int)$_POST['id_conv'];
$nuevo_nombre = mysqli_real_escape_string($conexion, $_POST['nuevo_nombre']);

// 1. Verificar que soy el creador (opcional, por seguridad)
// 2. Actualizar nombre
if (!empty($nuevo_nombre)) {
    mysqli_query($conexion, "UPDATE chat_conversacion SET nombre_grupo = '$nuevo_nombre' WHERE id_conversacion = $id_conv");
}

// 3. Procesar Foto si se subió una
if (isset($_FILES['foto_grupo']) && $_FILES['foto_grupo']['error'] == 0) {
    // Aquí iría tu lógica de subir imagen (mover el archivo y guardar el nombre en la DB)
    // De momento, con el nombre ya debería funcionarte la edición básica
}

echo json_encode(['success' => true]);