<?php
require '../../db/conexiones.php';

$buscar = $_GET['buscar'] ?? '';
$orden = $_GET['orden'] ?? 'precio_asc';
$pagina = max(1, (int)($_GET['pagina'] ?? 1));

$limite = 12;
$offset = ($pagina - 1) * $limite;

/* =========================
   FILTROS
   ========================= */

$where = "WHERE activo = 1";

if ($buscar !== '') {
    $buscar = mysqli_real_escape_string($conexion, $buscar);
    $where .= " AND nombre LIKE '%$buscar%'";
}

/* =========================
   ORDEN
   ========================= */

$orderBy = "precio ASC";

switch ($orden) {
    case 'precio_desc': $orderBy = "precio DESC"; break;
    case 'nombre_asc': $orderBy = "nombre ASC"; break;
    case 'nombre_desc': $orderBy = "nombre DESC"; break;
    case 'rareza_desc': $orderBy = "FIELD(rareza,'legendario','epico','raro','comun')"; break;
}

/* =========================
   TOTAL
   ========================= */

$resTotal = mysqli_query($conexion, "SELECT COUNT(*) as total FROM Tienda_Items $where");
$total = mysqli_fetch_assoc($resTotal)['total'];

/* =========================
   QUERY
   ========================= */

$res = mysqli_query($conexion, "
SELECT *
FROM Tienda_Items
$where
ORDER BY $orderBy
LIMIT $limite OFFSET $offset
");

/* =========================
   HTML
   ========================= */

$html = '';

while ($item = mysqli_fetch_assoc($res)) {

    $html .= "
    <div class='juego'>

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

            <form action='comprar_item.php' method='POST'>
                <input type='hidden' name='id_item' value='".$item['id_item']."'>
                <button class='btn-comprar'>Comprar</button>
            </form>

        </div>

    </div>";
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