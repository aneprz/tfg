<?php
session_start();
require_once '../../db/conexiones.php';

// Le decimos al navegador que vamos a escupir JSON, no HTML
header('Content-Type: application/json');

// 1. Validaciones básicas de seguridad
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
    // 2. INICIAMOS TRANSACCIÓN (Súper importante para que no haya bugs de dinero infinito)
    $conexion->begin_transaction();

    // 3. Bloqueamos al usuario y comprobamos saldo (FOR UPDATE evita compras dobles simultáneas)
    $stmtUsu = $conexion->prepare("SELECT puntos FROM Usuario WHERE id_usuario = ? FOR UPDATE");
    $stmtUsu->bind_param("i", $idUsuario);
    $stmtUsu->execute();
    $usuario = $stmtUsu->get_result()->fetch_assoc();
    
    // Obtenemos el precio de la caja
    $stmtCaja = $conexion->prepare("SELECT precio, nombre FROM Caja WHERE id_caja = ?");
    $stmtCaja->bind_param("i", $idCaja);
    $stmtCaja->execute();
    $caja = $stmtCaja->get_result()->fetch_assoc();

    if (!$caja) {
        throw new Exception("Esta caja no existe.");
    }

    if ($usuario['puntos'] < $caja['precio']) {
        throw new Exception("No tienes puntos suficientes. Cuesta " . $caja['precio'] . " y tienes " . $usuario['puntos']);
    }

    // 4. COBRAMOS LA CAJA (Restamos el precio al saldo actual)
    $saldoDespuesDeCobro = $usuario['puntos'] - $caja['precio'];
    $stmtCobro = $conexion->prepare("UPDATE Usuario SET puntos = ? WHERE id_usuario = ?");
    $stmtCobro->bind_param("ii", $saldoDespuesDeCobro, $idUsuario);
    $stmtCobro->execute();

    // 5. LA RULETA MATEMÁTICA (RNG) - ¡Primero decidimos qué toca!
    $stmtPremios = $conexion->prepare("SELECT * FROM Recompensa_Caja WHERE id_caja = ?");
    $stmtPremios->bind_param("i", $idCaja);
    $stmtPremios->execute();
    $premios = $stmtPremios->get_result()->fetch_all(MYSQLI_ASSOC);

    if (count($premios) === 0) {
        throw new Exception("La caja está vacía, contacta con el administrador.");
    }

    // Generamos un número aleatorio entre 0.00 y 100.00
    $tirada = mt_rand(0, 10000) / 100; 
    $acumulado = 0;
    $premioGanado = null;

    foreach ($premios as $premio) {
        $acumulado += (float) $premio['probabilidad'];
        if ($tirada <= $acumulado) {
            $premioGanado = $premio;
            break;
        }
    }

    // Por si las probabilidades no suman 100 exacto, damos el último
    if (!$premioGanado) { $premioGanado = end($premios); }

    // 6. ENTREGAMOS EL PREMIO (Ahora sí sabemos qué es)
    $saldoFinal = $saldoDespuesDeCobro;
    $mensajePremio = "";

    if ($premioGanado['tipo_premio'] === 'puntos') {
        // Le sumamos los puntos ganados al nuevo saldo
        $saldoFinal += $premioGanado['puntos_premio'];
        $stmtPuntos = $conexion->prepare("UPDATE Usuario SET puntos = ? WHERE id_usuario = ?");
        $stmtPuntos->bind_param("ii", $saldoFinal, $idUsuario);
        $stmtPuntos->execute();
        $mensajePremio = "¡Has ganado " . $premioGanado['puntos_premio'] . " puntos extra!";

    } elseif ($premioGanado['tipo_premio'] === 'avatar' || $premioGanado['tipo_premio'] === 'marco') {
        // Lo metemos en la tabla de usuario_items (Inventario)
        $idItem = $premioGanado['id_item'];
        $stmtItem = $conexion->prepare("INSERT IGNORE INTO usuario_items (id_usuario, id_item, equipado) VALUES (?, ?, 0)");
        $stmtItem->bind_param("ii", $idUsuario, $idItem);
        $stmtItem->execute();
        $mensajePremio = "¡Has conseguido un nuevo cosmético!";

    } elseif ($premioGanado['tipo_premio'] === 'juego') {
        // Lo metemos en la tabla de biblioteca
        $idJuego = $premioGanado['id_videojuego'];
        $stmtJuego = $conexion->prepare("INSERT IGNORE INTO biblioteca (id_usuario, id_videojuego) VALUES (?, ?)");
        $stmtJuego->bind_param("ii", $idUsuario, $idJuego);
        $stmtJuego->execute();
        $mensajePremio = "¡Te ha tocado un juego top!";
    }

    // 7. CONFIRMAMOS LA TRANSACCIÓN
    $conexion->commit();

    // Devolvemos el resultado al Front-end con todos los datos visuales listos para la ruleta
    echo json_encode([
        'status' => 'success',
        'nuevo_saldo' => $saldoFinal, // Importante: Enviamos el saldo con el cobro hecho y el premio sumado (si aplica)
        'mensaje' => $mensajePremio,
        'tipo_premio' => $premioGanado['tipo_premio'],
        'puntos_premio' => $premioGanado['puntos_premio'],
        'nombre_premio' => $premioGanado['nombre_premio'] ?? ($premioGanado['puntos_premio'] . ' Pts'),
        'imagen_premio' => $premioGanado['imagen_premio'] ?? 'logoPlatino.png'
    ]);

} catch (Exception $e) {
    $conexion->rollback(); // Si algo falla, deshacemos el cobro automáticamente
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
}