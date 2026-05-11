<?php
session_start();
require_once '../../db/conexiones.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Inicia sesión primero.']);
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];
$idCaja = (int) $_POST['id_caja'];

try {
    $conexion->begin_transaction();

    // 1. Saldo del usuario
    $stmtUsu = $conexion->prepare("SELECT puntos FROM Usuario WHERE id_usuario = ? FOR UPDATE");
    $stmtUsu->bind_param("i", $idUsuario);
    $stmtUsu->execute();
    $usuario = $stmtUsu->get_result()->fetch_assoc();
    
    // 2. BUSCAR CAJA (Híbrido)
    // Intentamos buscar en el sistema de eventos (Tienda_Items)
    $stmtCaja = $conexion->prepare("SELECT precio FROM Tienda_Items WHERE id_item = ? AND tipo = 'lootbox'");
    $stmtCaja->bind_param("i", $idCaja);
    $stmtCaja->execute();
    $caja = $stmtCaja->get_result()->fetch_assoc();
    $esEvento = true;

    if (!$caja) {
        // Si no está, buscamos en el sistema viejo (Caja)
        $stmtCajaOld = $conexion->prepare("SELECT precio FROM Caja WHERE id_caja = ?");
        $stmtCajaOld->bind_param("i", $idCaja);
        $stmtCajaOld->execute();
        $caja = $stmtCajaOld->get_result()->fetch_assoc();
        $esEvento = false;
    }

    if (!$caja) throw new Exception("La caja no existe.");
    if ($usuario['puntos'] < $caja['precio']) throw new Exception("No tienes puntos suficientes.");

    // 3. COBRO
    $saldoTrasCobro = $usuario['puntos'] - $caja['precio'];
    $conexion->query("UPDATE Usuario SET puntos = $saldoTrasCobro WHERE id_usuario = $idUsuario");

    // 4. OBTENER RECOMPENSAS
    if ($esEvento) {
        $sql = "SELECT ti.id_item, ti.nombre, ti.tipo, ti.imagen, ti.precio as valor_puntos, lr.probabilidad 
                FROM lootbox_recompensas lr 
                JOIN Tienda_Items ti ON lr.id_item = ti.id_item 
                WHERE lr.id_lootbox = ?";
    } else {
        $sql = "SELECT id_item, nombre_premio as nombre, tipo_premio as tipo, imagen_premio as imagen, puntos_premio as valor_puntos, probabilidad 
                FROM Recompensa_Caja 
                WHERE id_caja = ?";
    }
    
    $stmtP = $conexion->prepare($sql);
    $stmtP->bind_param("i", $idCaja);
    $stmtP->execute();
    $premios = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);

    // 5. RNG (Ajustado a precisión de 2 decimales: 0.00 a 100.00)
    $tirada = mt_rand(0, 10000) / 100; 
    $acumulado = 0;
    $ganado = null;

    // IMPORTANTE: Los premios de evento vienen por probabilidad ASC, 
    // lo cual es peligroso. Vamos a asegurar el tiro.
    foreach ($premios as $p) {
        $acumulado += (float)$p['probabilidad'];
        if ($tirada <= $acumulado) {
            $ganado = $p;
            break;
        }
    }

    // Si por un error de redondeo no ha entrado en el bucle, le damos el primer premio (consuelo)
    if (!$ganado) $ganado = $premios[0];

    // =========================================================
    // 6. ENTREGA DEL PREMIO
    // =========================================================
    $saldoFinal = $saldoTrasCobro;
    $tipo = $ganado['tipo'];
    $puntosQueDa = (int)$ganado['valor_puntos'];
    $nombreFinal = $ganado['nombre'];
    
    // IMPORTANTE: Definimos la imagen por defecto SIEMPRE antes de entrar en los IF
    $imagenFinal = $ganado['imagen'] ?: 'logoPlatino.png';

    if ($tipo === 'puntos') {
        $saldoFinal += $puntosQueDa;
        $conexion->query("UPDATE Usuario SET puntos = $saldoFinal WHERE id_usuario = $idUsuario");
        $mensaje = "¡Ganaste $puntosQueDa puntos!";
    } else {
        // Es un cosmético (Avatar/Marco)
        $idItem = $ganado['id_item'];
        $conexion->query("INSERT IGNORE INTO usuario_items (id_usuario, id_item, equipado) VALUES ($idUsuario, $idItem, 0)");
        $mensaje = "¡Nuevo objeto desbloqueado!";
        
        // Parche de rutas para marcos si no la tiene ya
        if ($tipo === 'marco' && strpos($imagenFinal, 'marcos/') === false) {
            $imagenFinal = 'marcos/' . $imagenFinal;
        }
    }

    $conexion->commit();

    // 7. RESPUESTA AL JS (Limpia, sin Warnings)
    echo json_encode([
        'status' => 'success',
        'nuevo_saldo' => $saldoFinal,
        'mensaje' => $mensaje,
        'tipo_premio' => $tipo,
        'puntos_premio' => $puntosQueDa,
        'nombre_premio' => $nombreFinal,
        'imagen_premio' => $imagenFinal,
        'probabilidad_ganadora' => $ganado['probabilidad'], // <--- ESTO ES NUEVO
        'precio_caja_ref' => $caja['precio'],
        'premios_todos' => $premios 
    ]);

} catch (Exception $e) {
    if ($conexion && $conexion->in_transaction) $conexion->rollback();
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}