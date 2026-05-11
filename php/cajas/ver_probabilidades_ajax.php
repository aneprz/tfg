<?php
// ver_probabilidades_ajax.php
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

// --- LÓGICA DE FIJAS (Legacy, tus 4 cajas manuales) ---
if ($id_caja >= 1 && $id_caja <= 4) {
    // Si tus cajas fijas NO están en las tablas lootbox_recompensas, 
    // usa la lógica de probabilidad fija que tuvieras aquí antes.
    // Ej: 
    /*
    if($id_caja == 1) { 
        $premios = [['tipo' => 'avatar', 'nombre' => 'Avatar Fijo 1', 'prob' => 10, ...], ...];
    }
    */
    // Si tus cajas fijas SÍ están en las tablas nuevas, borra este IF entero.
}


// --- LÓGICA DINÁMICA (Si no hemos encontrado premios en la lógica fija) ---
if (empty($premios)) {
    // Comprobar si es una caja de evento (dinámica) creada en el panel
    $stmtCheck = $conexion->prepare("SELECT id_item FROM Tienda_Items WHERE id_item = ? AND tipo = 'lootbox' AND activo = 1 LIMIT 1");
    $stmtCheck->bind_param("i", $id_caja);
    $stmtCheck->execute();
    $existeCaja = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if ($existeCaja) {
        // Consultar premios de la base de datos (tu nueva tabla)
        $sql = "
            SELECT ti.id_item, ti.nombre, ti.tipo, ti.imagen, ti.precio as valor_puntos, lr.probabilidad
            FROM lootbox_recompensas lr
            JOIN Tienda_Items ti ON lr.id_item = ti.id_item
            WHERE lr.id_lootbox = ?
            ORDER BY ti.tipo DESC, ti.nombre ASC
        ";
        
        $stmtPremios = $conexion->prepare($sql);
        $stmtPremios->bind_param("i", $id_caja);
        $stmtPremios->execute();
        $resPremios = $stmtPremios->get_result();

        while ($row = $resPremios->fetch_assoc()) {
            $premios[] = [
                'id_item' => $row['id_item'],
                'tipo_premio' => $row['tipo'],
                // EL ARREGLO: Si es puntos, mostramos el valor (guardado en precio), sino el nombre
                'nombre_premio' => ($row['tipo'] === 'puntos') ? $row['valor_puntos'] . " Puntos" : $row['nombre'],
                // Icono por defecto de puntos o la imagen del cosmético
                'imagen_premio' => ($row['tipo'] === 'puntos') ? 'logoPlatino.png' : $row['imagen'],
                'probabilidad' => $row['probabilidad']
            ];
        }
        $stmtPremios->close();
    }
}

// --- RESPUESTA FINAL ---
if (empty($premios)) {
    echo json_encode(['status' => 'error', 'mensaje' => 'caja no existente o sin premios']);
} else {
    echo json_encode(['status' => 'success', 'premios' => $premios]);
}
exit;
?>