<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['tag'])) {
    header("Location: ../../../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link rel="stylesheet" href="../../../estilos/estilos_perfilSesion.css?v=<?php echo time(); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="dashboard-body">

<main class="dashboard-page">

<div class="perfil-card dashboard-card">

<div class="perfil-header">
    <h1>📊 Dashboard de <?= htmlspecialchars($_SESSION['tag']); ?></h1>
    <p class="status">Estadísticas personales</p>
</div>

<div class="dashboard-container">

    <div class="dashboard-grid">

        <div class="dash-card">
            <div class="dash-num" id="horas">0</div>
            <div class="dash-label">Horas</div>
        </div>

        <div class="dash-card">
            <div class="dash-num" id="juegos">0</div>
            <div class="dash-label">Juegos</div>
        </div>

        <div class="dash-card">
            <div class="dash-num" id="completados">0</div>
            <div class="dash-label">Completados</div>
        </div>

        <div class="dash-card">
            <div class="dash-num" id="ratio">0%</div>
            <div class="dash-label">Abandono</div>
        </div>

    </div>

    <section class="dashboard-panel">
        <h3>Evolución de puntuación</h3>
        <div class="chart-wrapper">
            <canvas id="grafica"></canvas>
        </div>
    </section>

    <section class="dashboard-panel">
        <h3>Top juegos</h3>
        <ul id="top"></ul>
    </section>

    <div class="perfil-footer dashboard-footer">
        <a href="perfilSesion.php" class="btn-volver dashboard-back-btn">Volver al perfil</a>
    </div>

</div>

</div>
</main>

<script src="/js/dashboard.js"></script>

</body>
</html>
