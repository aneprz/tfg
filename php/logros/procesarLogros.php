<?php
require_once __DIR__ . '/../../db/conexiones.php';

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$filtro = $_GET['filtro'] ?? 'mas';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$porPagina = 60;
$offset = ($pagina - 1) * $porPagina;

/* ORDEN */

$orderBy = "total_logros DESC";

switch ($filtro) {

    case "menos":
        $orderBy = "total_logros ASC";
        break;

    case "nombre_asc":
        $orderBy = "v.titulo ASC";
        break;

    case "nombre_desc":
        $orderBy = "v.titulo DESC";
        break;

    case "nota_desc":
        $orderBy = "v.rating_medio DESC";
        break;

    case "nota_asc":
        $orderBy = "v.rating_medio ASC";
        break;

    case "fecha_desc":
        $orderBy = "v.fecha_lanzamiento DESC";
        break;

    case "fecha_asc":
        $orderBy = "v.fecha_lanzamiento ASC";
        break;

}
/* QUERY BASE */

$sqlBase = "
FROM Videojuego v
JOIN Logros l ON v.id_videojuego = l.id_videojuego
";

$where = "";

if (!empty($busqueda)) {
    $where = "WHERE v.titulo LIKE ?";
}

/* TOTAL JUEGOS */

$sqlTotal = "
SELECT COUNT(DISTINCT v.id_videojuego) as total
$sqlBase
$where
";

$stmtTotal = $conexion->prepare($sqlTotal);

if (!empty($busqueda)) {
    $termino = "%$busqueda%";
    $stmtTotal->bind_param("s", $termino);
}

$stmtTotal->execute();
$total = $stmtTotal->get_result()->fetch_assoc()['total'];

$totalPaginas = ceil($total / $porPagina);

/* JUEGOS PAGINA ACTUAL */

$sql = "
SELECT 
    v.id_videojuego,
    v.titulo,
    v.portada,
    COUNT(l.id_logro) as total_logros
$sqlBase
$where
GROUP BY v.id_videojuego
ORDER BY $orderBy
LIMIT $porPagina OFFSET $offset
";

$stmt = $conexion->prepare($sql);

if (!empty($busqueda)) {
    $stmt->bind_param("s", $termino);
}

$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {

    while ($row = $resultado->fetch_assoc()) {

        $portada = $row['portada'] ?: '../../media/logoPlatino.png';

        echo '
        <a class="juegoLink" href="logrosJuego.php?id='.$row['id_videojuego'].'">

            <article class="juego">

                <div class="portadaJuego">
                    <img src="'.htmlspecialchars($portada).'" alt="">
                </div>

                <div class="infoJuego">

                    <div class="tituloJuego">
                        '.htmlspecialchars($row['titulo']).'
                    </div>

                    <div class="logros-count">
                        '.$row['total_logros'].' logros
                    </div>

                </div>

            </article>

        </a>';
    }

} else {

    echo '<div class="no-results"></div>';
}

/* PAGINACIÓN */

echo '<div class="paginacion">';

if ($pagina > 1) {

    echo '<button class="pag-btn" data-pagina="'.($pagina - 1).'">←</button>';

}

if ($pagina < $totalPaginas) {

    echo '<button class="pag-btn" data-pagina="'.($pagina + 1).'">→</button>';

}

echo '</div>';

$stmt->close();
$conexion->close();