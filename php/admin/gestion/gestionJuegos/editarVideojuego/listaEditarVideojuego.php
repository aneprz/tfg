<?php
    session_start();
    require_once __DIR__ . '/../../../../../db/conexiones.php';

    if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
        header("Location: ../../index.php");
        exit();
    }
    $admin = true;
    $res = $conexion->query("SELECT id_videojuego, titulo FROM Videojuego ORDER BY titulo ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Editar Videojuego</title>
    <link rel="stylesheet" href="../../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../../media/logoPlatino.png">
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../../../../media/logoPlatino.png" alt="" width="40px">
            <a href="../../../../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="../gestionJuegos.php">Volver al panel de gestión</a></li>
            </ul>
        </nav>
        <?php if(isset($_SESSION['tag'])) : ?>
            <a class="tag" href="../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
        <?php endif; ?>
    </header>
    <div class="central">
        <h1>Seleccionar Videojuego</h1>
    </div>
    <div class="admin-container">
    <input type="text" id="buscador" placeholder="🔍 Buscar juego..." onkeyup="filtrar()" style="width:100%; padding:12px; border: 1px solid #ddd; border-radius: 8px;">

    <table class="admin-table" id="tablaJuegos">
        <thead>
            <tr>
                <th>Título del Videojuego</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                    <td>
                        <a href="editarVideojuego.php?id=<?php echo (int)$row['id_videojuego']; ?>" class="btn-editar">Editar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    function filtrar() {
        let val = document.getElementById("buscador").value.toLowerCase();
        let filas = document.getElementById("tablaJuegos").getElementsByTagName("tr");
        for(let i = 1; i < filas.length; i++) {
            let titulo = filas[i].cells[0].textContent.toLowerCase();
            filas[i].style.display = titulo.includes(val) ? "" : "none";
        }
    }
</script>
</body>
</html>