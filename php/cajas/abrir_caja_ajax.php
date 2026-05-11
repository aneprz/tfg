<?php
session_start();
require_once '../../db/conexiones.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Debes iniciar sesión.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_caja'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'Petición inválida.']);
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];
$idCaja = (int) $_POST['id_caja'];

try {
    $conexion->begin_transaction();

    // 1. Bloqueamos al usuario y comprobamos saldo
    $stmtUsu = $conexion->prepare("SELECT puntos FROM Usuario WHERE id_usuario = ? FOR UPDATE");
    $stmtUsu->bind_param("i", $idUsuario);
    $stmtUsu->execute();
    $usuario = $stmtUsu->get_result()->fetch_assoc();
    
    // 2. BUSCAR LA CAJA (Sistema Híbrido)
    // Primero buscamos en el sistema nuevo (Tienda_Items)
    $stmtCaja = $conexion->prepare("SELECT precio, nombre FROM Tienda_Items WHERE id_item = ? AND tipo = 'lootbox' AND activo = 1");
    $stmtCaja->bind_param("i", $idCaja);
    $stmtCaja->execute();
    $caja = $stmtCaja->get_result()->fetch_assoc();
    $esSistemaNuevo = true;

    // Si no aparece en el sistema nuevo, buscamos en el antiguo por si acaso
    if (!$caja) {
        $stmtCajaOld = $conexion->prepare("SELECT precio, nombre FROM Caja WHERE id_caja = ?");
        $stmtCajaOld->bind_param("i", $idCaja);
        $stmtCajaOld->execute();
        $caja = $stmtCajaOld->get_result()->fetch_assoc();
        $esSistemaNuevo = false;
    }

    if (!$caja) {
        throw new Exception("Esta caja no existe.");
    }

    if ($usuario['puntos'] < $caja['precio']) {
        throw new Exception("No tienes puntos suficientes. Cuesta " . $caja['precio'] . " y tienes " . $usuario['puntos']);
    }

    // 3. COBRAMOS LA CAJA
    $saldoDespuesDeCobro = $usuario['puntos'] - $caja['precio'];
    $stmtCobro = $conexion->prepare("UPDATE Usuario SET puntos = ? WHERE id_usuario = ?");
    $stmtCobro->bind_param("ii", $saldoDespuesDeCobro, $idUsuario);
    $stmtCobro->execute();

    // 4. OBTENER RECOMPENSAS
    if ($esSistemaNuevo) {
        // Consulta para el sistema dinámico (unimos con Tienda_Items para saber qué es cada premio)
        $sql = "SELECT ti.id_item, ti.nombre, ti.tipo, ti.imagen, ti.precio as puntos_valor, lr.probabilidad 
                FROM lootbox_recompensas lr 
                JOIN Tienda_Items ti ON lr.id_item = ti.id_item 
                WHERE lr.id_lootbox = ?";
        $stmtPremios = $conexion->prepare($sql);
    } else {
        // Consulta para tu sistema antiguo
        $stmtPremios = $conexion->prepare("SELECT * FROM Recompensa_Caja WHERE id_caja = ?");
    }
    
    $stmtPremios->bind_param("i", $idCaja);
    $stmtPremios->execute();
    $premios = $stmtPremios->get_result()->fetch_all(MYSQLI_ASSOC);

    if (count($premios) === 0) {
        throw new Exception("La caja está vacía, contacta con el administrador.");
    }

    // 5. RNG (La Tirada)
    $tirada = mt_rand(0, 10000) / 100; 
    $acumulado = 0;
    $premioGanado = null;

    foreach ($premios as $p) {
        $acumulado += (float) $p['probabilidad'];
        if ($tirada <= $acumulado) {
            $premioGanado = $p;
            break;
        }
    }
    if (!$premioGanado) { $premioGanado = end($premios); }

    // 6. ENTREGAR EL PREMIO
    $saldoFinal = $saldoDespuesDeCobro;
    $mensajePremio = "";
    
    // Normalizamos los nombres de las columnas para que funcionen con ambos sistemas
    $tipo = $premioGanado['tipo'] ?? $premioGanado['tipo_premio'];
    $puntosValor = (int)($premioGanado['puntos_valor'] ?? $premioGanado['puntos_premio'] ?? 0);
    $idItem = $premioGanado['id_item'] ?? null;
    $nombrePremio = $premioGanado['nombre'] ?? $premioGanado['nombre_premio'] ?? ($puntosValor . ' Pts');
    $imagenPremio = $premioGanado['imagen'] ?? $premioGanado['imagen_premio'] ?? 'logoPlatino.png';

    if ($tipo === 'puntos') {
        $saldoFinal += $puntosValor;
        $stmtPuntos = $conexion->prepare("UPDATE Usuario SET puntos = ? WHERE id_usuario = ?");
        $stmtPuntos->bind_param("ii", $saldoFinal, $idUsuario);
        $stmtPuntos->execute();
        $mensajePremio = "¡Has ganado " . $puntosValor . " puntos!";

    } elseif ($tipo === 'avatar' || $tipo === 'marco') {
        $stmtItem = $conexion->prepare("INSERT IGNORE INTO usuario_items (id_usuario, id_item, equipado) VALUES (?, ?, 0)");
        $stmtItem->bind_param("ii", $idUsuario, $idItem);
        $stmtItem->execute();
        $mensajePremio = "¡Nuevo cosmético desbloqueado!";

    } elseif ($tipo === 'juego') {
        // Si tienes ID de juego en el sistema antiguo/nuevo
        $idJuego = $premioGanado['id_videojuego'] ?? $idItem;
        $stmtJuego = $conexion->prepare("INSERT IGNORE INTO biblioteca (id_usuario, id_videojuego) VALUES (?, ?)");
        $stmtJuego->bind_param("ii", $idUsuario, $idJuego);
        $stmtJuego->execute();
        $mensajePremio = "¡Juego añadido a tu biblioteca!";
    }

    $conexion->commit();

    echo json_encode([
        'status' => 'success',
        'nuevo_saldo' => $saldoFinal,
        'mensaje' => $mensajePremio,
        'tipo_premio' => $tipo,
        'puntos_premio' => $puntosValor,
        'nombre_premio' => $nombrePremio,
        'imagen_premio' => $imagenPremio
    ]);

} catch (Exception $e) {
    if ($conexion->in_transaction) { $conexion->rollback(); }
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}