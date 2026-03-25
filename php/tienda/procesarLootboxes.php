<?php
session_start();
require '../../db/conexiones.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;
$pagina = max(1, (int)($_GET['pagina'] ?? 1));

$limite = 24;
$offset = ($pagina - 1) * $limite;

/* =========================
   FILTRO LOOTBOXES
========================= */
$where = "WHERE ti.activo = 1 AND ti.tipo='lootbox'";

/* =========================
   TOTAL
========================= */
$resTotal = mysqli_query($conexion, "
    SELECT COUNT(*) as total
    FROM Tienda_Items ti
    $where
");
$total = mysqli_fetch_assoc($resTotal)['total'];

/* =========================
   QUERY PRINCIPAL
========================= */
$res = mysqli_query($conexion, "
    SELECT ti.*
    FROM Tienda_Items ti
    $where
    ORDER BY ti.precio ASC
    LIMIT $limite OFFSET $offset
");

/* ✅ CONTROL ERROR */
if (!$res) {
    echo json_encode([
        "html" => "",
        "paginacion" => "",
        "total" => 0,
        "error" => "Error en la base de datos"
    ]);
    exit;
}

/* =========================
   HTML
========================= */
$html = '';

while ($item = mysqli_fetch_assoc($res)) {

    $rareza = htmlspecialchars($item['rareza'] ?? 'comun');

    $html .= "
    <div class='juego item-preview rareza-$rareza' data-id='".$item['id_item']."'>
        <div class='portadaJuego'>
            <img src='../../media/".htmlspecialchars($item['imagen'])."'>
        </div>

        <div class='infoJuego'>
            <div class='tituloJuego'>
                ".htmlspecialchars($item['nombre'])."
            </div>

            <div class='precioItem'>
                ".$item['precio']." pts
            </div>

            <button class='btn-comprar-lootbox' data-id='".$item['id_item']."'>
                Abrir caja
            </button>
        </div>
    </div>
    ";
}

/* =========================
   PAGINACIÓN
========================= */
$totalPaginas = ceil($total / $limite);
$paginacion = '';

for ($i = 1; $i <= $totalPaginas; $i++) {
    $paginacion .= "<button class='pag-btn' data-pagina='$i'>$i</button>";
}

echo json_encode([
    "html" => $html,
    "paginacion" => $paginacion,
    "total" => $total
]);