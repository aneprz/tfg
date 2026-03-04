<?php
require_once __DIR__ . '/../../db/conexiones.php';

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

$sql = "SELECT nombre_logro, descripcion, puntos_logro FROM Logros";

if (!empty($busqueda)) {
    $sql .= " WHERE nombre_logro LIKE ? OR descripcion LIKE ? ORDER BY nombre_logro ASC";
    $stmt = $conexion->prepare($sql);
    $termino = "%$busqueda%";
    $stmt->bind_param("ss", $termino, $termino);
} else {
    $sql .= " ORDER BY puntos_logro DESC";
    $stmt = $conexion->prepare($sql);
}

$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        echo '<div class="logro-card">
                <div class="logro-icono">🏆</div>
                <div class="logro-info">
                    <div class="logro-header">
                        <h3>' . htmlspecialchars($row['nombre_logro']) . '</h3>
                        <span class="puntos">' . intval($row['puntos_logro']) . ' G</span>
                    </div>
                    <p class="logro-desc">' . htmlspecialchars($row['descripcion']) . '</p>
                    <span class="juego-tag">SalsaBox Original</span>
                </div>
              </div>';
    }
} else {
    echo '<div class="no-results"></div>';
}

$stmt->close();
$conexion->close();