<?php
session_start();
require '../../db/conexiones.php';

$id_usuario = $_SESSION['id_usuario'] ?? 0;

$buscar = $_GET['buscar'] ?? '';
$orden = $_GET['orden'] ?? 'precio_asc';
$pagina = max(1, (int)($_GET['pagina'] ?? 1));

$limite = 24;
$offset = ($pagina - 1) * $limite;

/* =========================
   FILTRO
   =========================
   Solo traer items que no sean lootboxes
*/
$where = "WHERE ti.activo = 1 AND ti.tipo IN ('avatar','marco','fondo')";

if ($buscar !== '') {
    $buscar = mysqli_real_escape_string($conexion, $buscar);
    $where .= " AND ti.nombre LIKE '%$buscar%'";
}

/* =========================
   ORDEN
*/
$orderBy = "ti.precio ASC";

switch ($orden) {
    case 'precio_desc': $orderBy = "ti.precio DESC"; break;
    case 'nombre_asc': $orderBy = "ti.nombre ASC"; break;
    case 'nombre_desc': $orderBy = "ti.nombre DESC"; break;
    case 'rareza_desc': $orderBy = "FIELD(ti.rareza,'legendario','epico','raro','comun')"; break;
}

/* =========================
   TOTAL
*/
$resTotal = mysqli_query($conexion, "
    SELECT COUNT(*) as total
    FROM Tienda_Items ti
    $where
");

$total = mysqli_fetch_assoc($resTotal)['total'];

/* =========================
   QUERY PRINCIPAL
*/
$res = mysqli_query($conexion, "
    SELECT ti.*, ui.id_usuario_item
    FROM Tienda_Items ti
    LEFT JOIN Usuario_Items ui 
    ON ui.id_item = ti.id_item AND ui.id_usuario = $id_usuario
    $where
    ORDER BY $orderBy
    LIMIT $limite OFFSET $offset
");

/* =========================
   HTML
*/
$html = '';

while ($item = mysqli_fetch_assoc($res)) {

    $tiene = $item['id_usuario_item'] !== null;

    $html .= "
    <div class='juego item-preview' 
        data-tipo='".htmlspecialchars($item['tipo'])."' 
        data-imagen='".htmlspecialchars($item['imagen'])."'>

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
    ";

    if ($tiene) {
        $html .= "<button class='btn-comprar' disabled>Ya lo tienes</button>";
    } else {
        $html .= "
        <form action='comprar_item.php' method='POST'>
            <input type='hidden' name='id_item' value='".$item['id_item']."'>
            <button class='btn-comprar'>Comprar</button>
        </form>";
    }

    $html .= "
        </div>
    </div>
    ";
}

/* =========================
   PAGINACIÓN
*/
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