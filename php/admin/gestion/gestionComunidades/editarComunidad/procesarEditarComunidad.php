<?php
session_start();
require_once __DIR__ . '/../../../../../db/conexiones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin'])) {
    $id_comunidad = (int)$_POST['id_comunidad'];
    $nombre = $_POST['nombre'];
    $id_videojuego = (int)$_POST['id_videojuego'];

    $queryActual = $conexion->prepare("SELECT banner_url FROM comunidad WHERE id_comunidad = ?");
    $queryActual->bind_param("i", $id_comunidad);
    $queryActual->execute();
    $actual = $queryActual->get_result()->fetch_assoc();
    $bannerViejo = $actual['banner_url'];

    $nombreArchivo = $bannerViejo;

    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
        $rutaMedia = __DIR__ . '/../../../../../media/';
        $extension = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
        
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {

            if (!empty($bannerViejo) && file_exists($rutaMedia . $bannerViejo)) {
                unlink($rutaMedia . $bannerViejo);
            }
            
            $nombreArchivo = "banner_com_" . time() . "_" . uniqid() . "." . $extension;
            move_uploaded_file($_FILES['banner']['tmp_name'], $rutaMedia . $nombreArchivo);
        }
    }

    $stmt = $conexion->prepare("UPDATE Comunidad SET nombre = ?, id_videojuego_principal = ?, banner_url = ? WHERE id_comunidad = ?");
    $stmt->bind_param("sisi", $nombre, $id_videojuego, $nombreArchivo, $id_comunidad);

    if ($stmt->execute()) {
        header("Location: ../gestionComunidades.php?status=editada");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>