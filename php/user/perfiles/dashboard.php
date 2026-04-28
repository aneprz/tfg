<?php
session_start();

if (!isset($_SESSION['tag'])) {
    header("Location: ../../../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard avanzado - SalsaBox</title>

<link rel="stylesheet" href="../../../estilos/estilos_perfilSesion.css?v=<?php echo time(); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="dashboard-body">

<main class="dashboard-page">

<div class="perfil-card dashboard-card">

<div class="perfil-header dashboard-hero">
    <div class="dashboard-hero-copy">
        <span class="dashboard-kicker">Panel avanzado</span>
        <h1>Dashboard de <?php echo htmlspecialchars($_SESSION['tag']); ?></h1>
        <p class="status">Tu resumen mas completo dentro de SalsaBox</p>
    </div>

    <aside class="dashboard-rank-card">
        <span class="dashboard-rank-label">Rango actual</span>
        <strong id="rankLabel" class="dashboard-rank-value">-</strong>
        <p id="rankCopy" class="dashboard-rank-copy">Preparando tus metricas...</p>
        <div class="dashboard-rank-track">
            <div id="rankBar" class="dashboard-rank-bar"></div>
        </div>
    </aside>
</div>

<div class="dashboard-container">
    <p id="dashboardNotice" class="dashboard-notice">Cargando datos del dashboard...</p>

    <section class="dashboard-section">
        <div class="dashboard-section-head">
            <div>
                <h2>Resumen general</h2>
                <p>Una fotografia rapida de tu progreso, tu perfil gamer y tu actividad.</p>
            </div>
        </div>

        <div class="dashboard-grid dashboard-overview-grid">
            <article class="dash-card dash-card-highlight">
                <span class="dash-eyebrow">Tiempo</span>
                <div class="dash-num" id="kpiHours">0</div>
                <div class="dash-label">Horas jugadas</div>
                <p class="dash-meta" id="kpiHoursMeta">0 horas por juego</p>
            </article>

            <article class="dash-card">
                <span class="dash-eyebrow">Biblioteca</span>
                <div class="dash-num" id="kpiGames">0</div>
                <div class="dash-label">Juegos</div>
                <p class="dash-meta" id="kpiGamesMeta">0 pendientes</p>
            </article>

            <article class="dash-card">
                <span class="dash-eyebrow">Progreso</span>
                <div class="dash-num" id="kpiCompleted">0</div>
                <div class="dash-label">Completados</div>
                <p class="dash-meta" id="kpiCompletedMeta">0% de cierre</p>
            </article>

            <article class="dash-card">
                <span class="dash-eyebrow">Valoracion</span>
                <div class="dash-num" id="kpiRating">0.0</div>
                <div class="dash-label">Media</div>
                <p class="dash-meta" id="kpiRatingMeta">Sin reseñas aun</p>
            </article>

            <article class="dash-card">
                <span class="dash-eyebrow">Wallet</span>
                <div class="dash-num" id="kpiPoints">0</div>
                <div class="dash-label">Puntos actuales</div>
                <p class="dash-meta" id="kpiPointsMeta">0 ganados en total</p>
            </article>

            <article class="dash-card">
                <span class="dash-eyebrow">Logros</span>
                <div class="dash-num" id="kpiAchievements">0</div>
                <div class="dash-label">Desbloqueados</div>
                <p class="dash-meta" id="kpiAchievementsMeta">0% del catalogo disponible</p>
            </article>

            <article class="dash-card">
                <span class="dash-eyebrow">Social</span>
                <div class="dash-num" id="kpiFriends">0</div>
                <div class="dash-label">Amigos</div>
                <p class="dash-meta" id="kpiFriendsMeta">0 solicitudes pendientes</p>
            </article>

            <article class="dash-card">
                <span class="dash-eyebrow">Comunidad</span>
                <div class="dash-num" id="kpiCommunities">0</div>
                <div class="dash-label">Comunidades</div>
                <p class="dash-meta" id="kpiCommunitiesMeta">0 posts publicados</p>
            </article>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-head">
            <div>
                <h2>Analitica</h2>
                <p>Graficos de puntuacion, economia, distribucion de estados y preferencias.</p>
            </div>
        </div>

        <div class="dashboard-analytics-grid">
            <article class="dashboard-panel dashboard-panel-wide">
                <div class="dashboard-panel-head">
                    <h3>Evolucion de puntuacion</h3>
                    <span class="panel-tag">Reseñas</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chartRating"></canvas>
                </div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Puntos por mes</h3>
                    <span class="panel-tag">Wallet</span>
                </div>
                <div class="chart-wrapper chart-wrapper-compact">
                    <canvas id="chartPoints"></canvas>
                </div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Estados de biblioteca</h3>
                    <span class="panel-tag">Mix actual</span>
                </div>
                <div class="chart-wrapper chart-wrapper-compact">
                    <canvas id="chartStates"></canvas>
                </div>
            </article>

            <article class="dashboard-panel dashboard-panel-wide">
                <div class="dashboard-panel-head">
                    <h3>Generos favoritos</h3>
                    <span class="panel-tag">Horas invertidas</span>
                </div>
                <div class="chart-wrapper">
                    <canvas id="chartGenres"></canvas>
                </div>
            </article>
        </div>
    </section>

    <section class="dashboard-columns">
        <div class="dashboard-column">
            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Insights</h3>
                    <span class="panel-tag">Lectura rapida</span>
                </div>
                <div id="insightCards" class="dashboard-mini-grid"></div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Top juegos</h3>
                    <span class="panel-tag">Por horas</span>
                </div>
                <div id="topGamesList" class="dashboard-list"></div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Reseñas recientes</h3>
                    <span class="panel-tag">Ultimas valoraciones</span>
                </div>
                <div id="recentReviewsList" class="dashboard-list"></div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Logros recientes</h3>
                    <span class="panel-tag">Desbloqueos</span>
                </div>
                <div id="recentAchievementsList" class="dashboard-list"></div>
            </article>
        </div>

        <div class="dashboard-column">
            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Actividad social</h3>
                    <span class="panel-tag">Comunidad</span>
                </div>
                <div id="socialStats" class="dashboard-mini-grid"></div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Comunidades destacadas</h3>
                    <span class="panel-tag">Tus espacios</span>
                </div>
                <div id="communitiesList" class="dashboard-list"></div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Inventario</h3>
                    <span class="panel-tag">Coleccion</span>
                </div>
                <div id="inventorySummary" class="dashboard-stack"></div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Actividad reciente</h3>
                    <span class="panel-tag">Ultimos hitos</span>
                </div>
                <div id="activityTimeline" class="dashboard-list"></div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <h3>Movimientos de puntos</h3>
                    <span class="panel-tag">Historial</span>
                </div>
                <div id="pointMovementsList" class="dashboard-list"></div>
            </article>
        </div>
    </section>

    <div class="perfil-footer dashboard-footer dashboard-footer-row">
        <a href="perfilSesion.php" class="btn-volver dashboard-back-btn">Volver al perfil</a>
        <a href="mis_juegos.php" class="btn-volver dashboard-back-btn">Mi biblioteca</a>
        <a href="mis_logros.php" class="btn-volver dashboard-back-btn">Mis logros</a>
    </div>
</div>

</div>
</main>

<script src="/js/dashboard.js"></script>

</body>
</html>
