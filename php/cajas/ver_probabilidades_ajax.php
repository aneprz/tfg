<?php
session_start();
header('Content-Type: application/json');

require_once '../../db/conexiones.php';

// Validar que viene la ID de la caja
$id_caja = isset($_GET['id_caja']) ? (int)$_GET['id_caja'] : 0;

if ($id_caja <= 0) {
    echo json_encode(['status' => 'error', 'mensaje' => 'ID de caja no válida']);
    exit;
}

$premios = [];

// =========================================================
// 1. BUSCAMOS EN EL SISTEMA ANTIGUO (Tus 4 cajas fijas)
// =========================================================
$stmtOld = $conexion->prepare("SELECT * FROM Recompensa_Caja WHERE id_caja = ?");
$stmtOld->bind_param("i", $id_caja);
$stmtOld->execute();
$resOld = $stmtOld->get_result();

while ($row = $resOld->fetch_assoc()) {
    $premios[] = [
        'id_item'       => $row['id_item'] ?? 0,
        'tipo_premio'   => $row['tipo_premio'],
        'puntos_premio' => $row['puntos_premio'] ?? 0,
        'nombre_premio' => $row['nombre_premio'] ?? ($row['puntos_premio'] . ' Puntos'),
        'imagen_premio' => $row['imagen_premio'] ?? 'logoPlatino.png',
        'probabilidad'  => $row['probabilidad']
    ];
}
$stmtOld->close();


// =========================================================
// 2. SI NO HAY NADA, BUSCAMOS EN EL SISTEMA NUEVO (Eventos)
// =========================================================
// =========================================================
// 2. SI NO HAY NADA, BUSCAMOS EN EL SISTEMA NUEVO (Eventos)
// =========================================================
if (empty($premios)) {
    $sqlNew = "
        SELECT ti.id_item, ti.nombre, ti.tipo, ti.imagen, ti.precio as valor_puntos, lr.probabilidad
        FROM lootbox_recompensas lr
        JOIN Tienda_Items ti ON lr.id_item = ti.id_item
        WHERE lr.id_lootbox = ?
        ORDER BY lr.probabilidad ASC
    ";
    
    $stmtNew = $conexion->prepare($sqlNew);
    $stmtNew->bind_param("i", $id_caja);
    $stmtNew->execute();
    $resNew = $stmtNew->get_result();

    while ($row = $resNew->fetch_assoc()) {
        // --- ARREGLO DE RUTAS DE IMAGEN ---
        $rutaImagen = $row['imagen'];
        
        // Si el ítem es un marco y la imagen no tiene ya la carpeta 'marcos/' escrita
        if ($row['tipo'] === 'marco' && strpos($rutaImagen, 'marcos/') === false) {
            $rutaImagen = 'marcos/' . $rutaImagen;
        } 
        // Si es un avatar y no tiene la carpeta 'avatares/'
        else if ($row['tipo'] === 'avatar' && strpos($rutaImagen, 'avatares/') === false) {
             // Solo si usas carpeta avatares, si no, déjalo igual
             // $rutaImagen = 'avatares/' . $rutaImagen; 
        }

        $premios[] = [
            'id_item'       => $row['id_item'],
            'tipo_premio'   => $row['tipo'],
            'puntos_premio' => $row['valor_puntos'],
            'nombre_premio' => $row['nombre'],
            'imagen_premio' => ($row['tipo'] === 'puntos') ? 'logoPlatino.png' : $rutaImagen,
            'probabilidad'  => $row['probabilidad']
        ];
    }
    $stmtNew->close();
}

// =========================================================
// 3. RESPUESTA FINAL
// =========================================================
if (empty($premios)) {
    echo json_encode(['status' => 'error', 'mensaje' => 'caja no existente o sin premios']);
} else {
    echo json_encode(['status' => 'success', 'premios' => $premios]);
}
exit;
?>