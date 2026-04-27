<?php
session_start();
require_once __DIR__ . '/../../../../db/conexiones.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SalsaBox - Importacion de Catalogo</title>
    <link rel="stylesheet" href="../../../../estilos/estilos_indexAdmin.css">
    <link rel="stylesheet" href="../../../../estilos/estilos_index.css">
    <link rel="icon" href="../../../../media/logoPlatino.png">
    <style>
        .import-shell {
            max-width: 1100px;
            margin: 40px auto 80px;
            padding: 0 20px;
        }

        .import-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;
            padding: 28px;
            backdrop-filter: blur(5px);
        }

        .import-copy {
            color: #d6deeb;
            margin: 0 0 24px;
            line-height: 1.6;
        }

        .config-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .config-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            text-align: left;
        }

        .config-field label {
            color: #00d4ff;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .config-field input {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
        }

        .actions-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 28px;
        }

        .action-btn {
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, opacity 0.2s ease;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .action-btn:disabled {
            opacity: 0.6;
            cursor: wait;
            transform: none;
        }

        .action-primary {
            background: #00d4ff;
            color: #10202a;
        }

        .action-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.14);
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .status-box {
            background: rgba(0, 0, 0, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 18px;
            text-align: left;
        }

        .status-box strong {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94dfff;
            margin-bottom: 8px;
        }

        .status-box span {
            display: block;
            font-size: 1.1rem;
            color: #fff;
            word-break: break-word;
        }

        .progress-track {
            width: 100%;
            height: 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #00d4ff, #e0be00);
            transition: width 0.25s ease;
        }

        .progress-copy {
            color: #d8e7f0;
            margin: 0 0 24px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .metric-card {
            background: rgba(0, 0, 0, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 18px;
            text-align: left;
        }

        .metric-card h3 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 1rem;
        }

        .metric-card p {
            margin: 0 0 8px;
            color: #cad8e1;
            font-size: 0.95rem;
        }

        .log-panel {
            background: #091018;
            border: 1px solid rgba(0, 212, 255, 0.18);
            border-radius: 14px;
            padding: 18px;
        }

        .log-panel h3 {
            margin: 0 0 12px;
            color: #fff;
        }

        #logOutput {
            margin: 0;
            max-height: 360px;
            overflow: auto;
            white-space: pre-wrap;
            font-family: Consolas, Monaco, monospace;
            color: #d9edf6;
            line-height: 1.5;
        }

        .hint {
            color: #b8c6cf;
            font-size: 0.92rem;
            margin-top: 12px;
        }
    </style>
</head>
<body>
    <header>
        <div class="tituloWeb">
            <img src="../../../../media/logoPlatino.png" alt="" width="40">
            <a href="../../../../index.php" class="logo">Salsa<span>Box</span></a>
        </div>
        <nav>
            <ul>
                <li><a href="gestionJuegos.php">Volver a gestion de videojuegos</a></li>
            </ul>
        </nav>
        <a class="tag" href="../../../user/perfiles/perfilSesion.php"><?php echo htmlspecialchars($_SESSION['tag']); ?></a>
    </header>

    <div class="central">
        <h1>Importacion del catalogo</h1>
        <p>Ejecuta <code>import_games</code>, <code>import_steamappid</code> e <code>import_logros</code> por lotes, en ese orden, desde el navegador.</p>
    </div>

    <main class="import-shell">
        <section class="import-card">
            <p class="import-copy">
                Esta version esta pensada para InfinityFree: cada peticion hace un trozo pequeno del trabajo y la siguiente peticion continua donde se quedo.
                Deja esta pestana abierta mientras corre la importacion. Si se corta, puedes reanudarla.
            </p>

            <div class="config-grid">
                <div class="config-field">
                    <label for="games_limit">Lote IGDB (<code>import_games</code>)</label>
                    <input id="games_limit" type="number" min="25" max="500" value="150">
                </div>
                <div class="config-field">
                    <label for="games_max_offset">Offset maximo IGDB</label>
                    <input id="games_max_offset" type="number" min="100" max="50000" value="10000">
                </div>
                <div class="config-field">
                    <label for="steam_batch_size">Lote <code>import_steamappid</code></label>
                    <input id="steam_batch_size" type="number" min="10" max="500" value="80">
                </div>
                <div class="config-field">
                    <label for="achievements_batch_size">Lote <code>import_logros</code></label>
                    <input id="achievements_batch_size" type="number" min="1" max="30" value="8">
                </div>
            </div>

            <div class="actions-row">
                <button id="startBtn" class="action-btn action-primary">Empezar desde cero</button>
                <button id="resumeBtn" class="action-btn action-secondary">Reanudar</button>
                <button id="stepBtn" class="action-btn action-secondary">Ejecutar un paso</button>
                <button id="resetBtn" class="action-btn action-secondary">Resetear estado</button>
            </div>

            <div class="status-grid">
                <div class="status-box">
                    <strong>Estado</strong>
                    <span id="statusValue">Cargando...</span>
                </div>
                <div class="status-box">
                    <strong>Fase actual</strong>
                    <span id="phaseValue">-</span>
                </div>
                <div class="status-box">
                    <strong>Progreso de fase</strong>
                    <span id="phaseProgressValue">0 / 0</span>
                </div>
            </div>

            <div class="progress-track">
                <div id="progressBar" class="progress-bar"></div>
            </div>
            <p id="progressCopy" class="progress-copy">Esperando accion.</p>

            <div class="metrics-grid">
                <article class="metric-card">
                    <h3>import_games</h3>
                    <p id="gamesMetrics">-</p>
                </article>
                <article class="metric-card">
                    <h3>import_steamappid</h3>
                    <p id="steamMetrics">-</p>
                </article>
                <article class="metric-card">
                    <h3>import_logros</h3>
                    <p id="achievementMetrics">-</p>
                </article>
            </div>

            <div class="log-panel">
                <h3>Registro</h3>
                <pre id="logOutput">Sin actividad todavia.</pre>
            </div>

            <p class="hint">
                Si InfinityFree vuelve a cortar por tiempo, baja el tamano de lote y reintenta.
            </p>
        </section>
    </main>

    <script>
        const statusValue = document.getElementById('statusValue');
        const phaseValue = document.getElementById('phaseValue');
        const phaseProgressValue = document.getElementById('phaseProgressValue');
        const progressBar = document.getElementById('progressBar');
        const progressCopy = document.getElementById('progressCopy');
        const gamesMetrics = document.getElementById('gamesMetrics');
        const steamMetrics = document.getElementById('steamMetrics');
        const achievementMetrics = document.getElementById('achievementMetrics');
        const logOutput = document.getElementById('logOutput');
        const startBtn = document.getElementById('startBtn');
        const resumeBtn = document.getElementById('resumeBtn');
        const stepBtn = document.getElementById('stepBtn');
        const resetBtn = document.getElementById('resetBtn');

        let autoRunning = false;

        function formConfig() {
            return {
                games_limit: document.getElementById('games_limit').value,
                games_max_offset: document.getElementById('games_max_offset').value,
                steam_batch_size: document.getElementById('steam_batch_size').value,
                achievements_batch_size: document.getElementById('achievements_batch_size').value
            };
        }

        function setButtonsBusy(busy) {
            startBtn.disabled = busy;
            resumeBtn.disabled = busy;
            stepBtn.disabled = busy;
            resetBtn.disabled = busy;
        }

        function appendLogs(logs) {
            if (!logs || !logs.length) {
                return;
            }

            const previous = logOutput.textContent === 'Sin actividad todavia.' ? '' : logOutput.textContent + '\n';
            logOutput.textContent = previous + logs.join('\n');
            logOutput.scrollTop = logOutput.scrollHeight;
        }

        function renderSummary(summary) {
            const state = summary.state;
            const phases = state.phases || {};
            const games = phases.import_games || {};
            const steam = phases.import_steamappid || {};
            const achievements = phases.import_logros || {};

            statusValue.textContent = summary.status || '-';
            phaseValue.textContent = summary.current_phase_label || '-';
            phaseProgressValue.textContent = `${summary.progress_processed} / ${summary.progress_total}`;
            progressBar.style.width = `${summary.progress_percent || 0}%`;
            progressCopy.textContent = `Progreso de fase: ${summary.progress_percent || 0}%`;

            gamesMetrics.textContent =
                `Lotes: ${games.processed_batches || 0}/${games.total_batches || 0} | ` +
                `recibidos: ${games.processed_records || 0} | ` +
                `insertados: ${games.inserted_records || 0} | ` +
                `omitidos: ${games.skipped_records || 0}`;

            steamMetrics.textContent =
                `procesados: ${steam.processed_games || 0}/${steam.total_games || 0} | ` +
                `match: ${steam.matched_games || 0} | ` +
                `sin match: ${steam.unmatched_games || 0}`;

            achievementMetrics.textContent =
                `juegos: ${achievements.processed_games || 0}/${achievements.total_games || 0} | ` +
                `con logros: ${achievements.games_with_achievements || 0} | ` +
                `sin logros: ${achievements.games_without_achievements || 0} | ` +
                `insertados: ${achievements.inserted_achievements || 0}`;

            if (state.last_error) {
                progressCopy.textContent = `Error: ${state.last_error}`;
            } else if (summary.status === 'completed') {
                progressCopy.textContent = 'Importacion completada.';
            }
        }

        async function callApi(action, extra = {}) {
            const body = new URLSearchParams({ action, ...extra });
            const response = await fetch('importadorCatalogoApi.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: body.toString()
            });

            const payload = await response.json();

            if (!response.ok || payload.error) {
                throw new Error(payload.error || 'No se pudo completar la accion.');
            }

            appendLogs(payload.logs || []);
            renderSummary(payload.summary);
            return payload;
        }

        async function refreshStatus() {
            try {
                const payload = await callApi('status');
                if ((payload.summary.status === 'running') && !autoRunning) {
                    appendLogs(['Hay una importacion en pausa lista para reanudar.']);
                }
            } catch (error) {
                appendLogs([`ERROR: ${error.message}`]);
            }
        }

        async function runLoop() {
            if (!autoRunning) {
                return;
            }

            try {
                const payload = await callApi('step');
                const status = payload.summary.status;

                if (status === 'running') {
                    setTimeout(runLoop, 350);
                } else {
                    autoRunning = false;
                    setButtonsBusy(false);
                }
            } catch (error) {
                autoRunning = false;
                setButtonsBusy(false);
                appendLogs([`ERROR: ${error.message}`]);
            }
        }

        startBtn.addEventListener('click', async () => {
            autoRunning = true;
            setButtonsBusy(true);
            logOutput.textContent = 'Sin actividad todavia.';

            try {
                await callApi('start', formConfig());
                runLoop();
            } catch (error) {
                autoRunning = false;
                setButtonsBusy(false);
                appendLogs([`ERROR: ${error.message}`]);
            }
        });

        resumeBtn.addEventListener('click', async () => {
            autoRunning = true;
            setButtonsBusy(true);
            runLoop();
        });

        stepBtn.addEventListener('click', async () => {
            setButtonsBusy(true);

            try {
                await callApi('step');
            } catch (error) {
                appendLogs([`ERROR: ${error.message}`]);
            } finally {
                setButtonsBusy(false);
            }
        });

        resetBtn.addEventListener('click', async () => {
            autoRunning = false;
            setButtonsBusy(true);

            try {
                logOutput.textContent = 'Sin actividad todavia.';
                await callApi('reset');
            } catch (error) {
                appendLogs([`ERROR: ${error.message}`]);
            } finally {
                setButtonsBusy(false);
            }
        });

        refreshStatus();
    </script>
</body>
</html>
