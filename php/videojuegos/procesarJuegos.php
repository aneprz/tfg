<?php
require '../../db/conexiones.php';

$buscar = $_GET['buscar'] ?? '';
$orden  = $_GET['orden'] ?? 'nombre_asc';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$porPagina = 48;
$offset = ($pagina - 1) * $porPagina;


/* ORDEN */

switch ($orden) {

    case 'nombre_desc':
        $orderBy = "titulo DESC";
        break;

    case 'nota_desc':
        $orderBy = "rating_medio DESC";
        break;

    case 'nota_asc':
        $orderBy = "rating_medio ASC";
        break;

    case 'fecha_desc':
        $orderBy = "fecha_lanzamiento DESC";
        break;

    case 'fecha_asc':
        $orderBy = "fecha_lanzamiento ASC";
        break;

    default:
        $orderBy = "titulo ASC";
}


/* BUSQUEDA */

$where = "";

if ($buscar !== "") {

    $buscar = mysqli_real_escape_string($conexion, $buscar);

    $where = "WHERE titulo LIKE '%$buscar%'";
}


/* TOTAL RESULTADOS */

$totalQuery = mysqli_query(
    $conexion,
    "SELECT COUNT(*) as total FROM Videojuego $where"
);

$filaTotal = mysqli_fetch_assoc($totalQuery);
$total = (int)$filaTotal['total'];

$totalPaginas = ceil($total / $porPagina);


/* JUEGOS */

$sql = "
    SELECT id_videojuego, titulo, rating_medio, portada
    FROM Videojuego
    $where
    ORDER BY $orderBy
    LIMIT $porPagina OFFSET $offset
";

$resultado = mysqli_query($conexion, $sql);

$html = "";

while ($fila = mysqli_fetch_assoc($resultado)) {

    $titulo = htmlspecialchars($fila['titulo']);
    $id = (int)$fila['id_videojuego'];
    $rating = $fila['rating_medio'];

    $estrellas = ($rating !== null) ? max(0, min(5, $rating / 2)) : 0;
    $porcentaje = ($estrellas / 5) * 100;

    $portada = $fila['portada'] ?: '../../media/logoPlatino.png';

    $html .= "

    <a class='juegoLink' href='juego.php?id=$id'>

        <article class='juego'>

            <div class='portadaJuego'>

                <img src='$portada' alt='Portada de $titulo'>

            </div>

            <div class='infoJuego'>

                <div class='tituloJuego'>
                    $titulo
                </div>

                <div class='puntuacionJuego'>

                    <div class='estrellas'>
                        <div class='relleno' style='width: {$porcentaje}%'></div>
                    </div>

                    <span class='nota'>
                        ".($rating !== null ? number_format($rating,1) : "Sin nota")."
                    </span>

                </div>

            </div>

        </article>

    </a>

    ";
}


/* PAGINACION */

$paginacion = "";

if ($pagina > 1) {

    $prev = $pagina - 1;

    $paginacion .= "<button class='pag-btn' data-pagina='$prev'>←</button>";

}

if ($pagina < $totalPaginas) {

    $next = $pagina + 1;

    $paginacion .= "<button class='pag-btn' data-pagina='$next'>→</button>";

}


echo json_encode([
    "html" => $html,
    "paginacion" => $paginacion,
    "total" => $total
]);