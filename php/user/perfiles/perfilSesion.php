<?php
session_start();
require '../../../db/conexiones.php';

if (!isset($_SESSION['tag'])) {
    header("Location: ../../../index.php");
    exit();
}

/* =========================
   MENSAJES
   ========================= */

if(isset($_SESSION["steam_vinculado"])){
    echo "<script>alert('Cuenta de Steam vinculada correctamente');</script>";
    unset($_SESSION["steam_vinculado"]);
}

if(isset($_SESSION["steam_sync"])){
    echo "<script>alert('".$_SESSION["steam_sync"]."');</script>";
    unset($_SESSION["steam_sync"]);
}

include 'statsUsuario.php'; 

$id = $_SESSION['id_usuario'];

/* =========================
   ITEMS EQUIPADOS
   ========================= */

$stmt = $conexion->prepare("
    SELECT ti.tipo, ti.imagen, ti.rareza
    FROM Usuario_Items ui
    JOIN Tienda_Items ti ON ui.id_item = ti.id_item
    WHERE ui.id_usuario = ?
    AND ui.equipado = 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$marco = null;
$fondo = null;
$avatar_item = null;

while ($item = $result->fetch_assoc()) {
    if ($item['tipo'] === 'marco') $marco = $item['imagen'];
    if ($item['tipo'] === 'fondo') $fondo = $item['imagen'];
    if ($item['tipo'] === 'avatar') $avatar_item = $item['imagen'];
}

/* =========================
   DATOS USUARIO
   ========================= */

$stmt = $conexion->prepare("
    SELECT biografia, avatar, puntos_actuales 
    FROM Usuario 
    WHERE id_usuario = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$biografia = $user['biografia'] ?? '';
$puntos_actuales = $user['puntos_actuales'] ?? 0;
$steamVinculado = isset($_SESSION["steamid"]) && !empty($_SESSION["steamid"]);

/* =========================
   AVATAR FINAL
   ========================= */

if ($avatar_item) {
    $img = "../../../media/" . $avatar_item;
} else {
    $avatar_db = trim($user['avatar'] ?? '');
    $img = empty($avatar_db) 
        ? "../../../media/perfil_default.jpg" 
        : "../../../media/" . $avatar_db;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?php echo htmlspecialchars($_SESSION['tag']); ?></title>

    <link rel="icon" href="../../../media/logoPlatino.png">
    <link rel="stylesheet" href="../../../estilos/estilos_perfilSesion.css?v=<?php echo time(); ?>">
</head>

<body style="<?php if($fondo): ?>
    background-image: url('../../../media/<?php echo htmlspecialchars($fondo); ?>');
<?php endif; ?>">

<main class="perfil-container">

    <div class="perfil-card">

        <div class="perfil-header">

            <!-- AVATAR + MARCO -->
            <div class="avatar-grande">

                <img src="<?php echo htmlspecialchars($img); ?>" class="avatar-img">

                <?php if($marco): ?>
                    <img src="../../../media/<?php echo htmlspecialchars($marco); ?>" class="marco-img">
                <?php endif; ?>

            </div>

            <h1><?php echo htmlspecialchars($_SESSION['tag']); ?></h1>

            <p class="status">
                <?= ($_SESSION['admin'] == 1) ? 'Administrador de SalsaBox' : 'Miembro de SalsaBox'; ?>
            </p>

            <br>

            <a href="../editarPerfil/editarPerfil.php"
               style="background-color: #e0be00; color:#000; padding: 0.6rem 1.2rem; border-radius:4px; text-decoration:none; font-weight:bold;">
               Editar Perfil
            </a>

        </div>

        <!-- STATS -->
        <div class="perfil-stats">

            <a href="mis_juegos.php" class="stat-link">
                <div class="stat-item">
                    <span class="stat-num"><?php echo $totalJuegos; ?></span>
                    <span class="stat-label">Juegos</span>
                </div>
            </a>

            <a href="mis_logros.php" class="stat-link">
                <div class="stat-item">
                    <span class="stat-num"><?php echo $puntos_actuales; ?></span>
                    <span class="stat-label">Puntos</span>
                </div>
            </a>

            <a href="mis_amigos.php" class="stat-link">
                <div class="stat-item">
                    <span class="stat-num"><?php echo $totalAmigos; ?></span>
                    <span class="stat-label">Amigos</span>
                </div>
            </a>

        </div>

        <br>

        <!-- STEAM -->
        <div>
            <a href="../../../Steam/steam_login.php">
                <img src="https://steamcommunity-a.akamaihd.net/public/images/signinthroughsteam/sits_01.png">
            </a>
        </div>

        <?php if (!$steamVinculado): ?>

            <a href="#" onclick="alert('Tienes que iniciar sesión con tu cuenta de Steam antes'); return false;" class="btn-sync-steam">
                Sincronizar logros de Steam
            </a>

        <?php else: ?>

            <a href="../../../Steam/sync_logros_steam.php" class="btn-sync-steam" id="steamSyncButton">
                Sincronizar logros de Steam
            </a>

            <div id="steamSyncFeedback" class="steam-sync-feedback" hidden aria-live="polite">
                <div class="steam-sync-head">
                    <span id="steamSyncLabel">Sincronizando logros de Steam...</span>
                    <span id="steamSyncPercent">0%</span>
                </div>
                <div class="steam-sync-track">
                    <div id="steamSyncBar" class="steam-sync-fill"></div>
                </div>
                <p id="steamSyncCopy" class="steam-sync-copy">Preparando tu biblioteca para la sincronizacion.</p>
                <iframe id="steamSyncFrame" class="steam-sync-frame" title="Sincronizacion Steam"></iframe>
            </div>

        <?php endif; ?>

        <!-- BIO -->
        <div class="perfil-body">
            <h3>Sobre ti</h3>
            <p class="bio-text">
                <?php echo !empty($biografia) 
                    ? nl2br(htmlspecialchars($biografia)) 
                    : "Bienvenido. Aún no tienes biografía."; ?>
            </p>
        </div>

        <!-- FOOTER -->
        <div class="perfil-footer">
            <a href="dashboard.php" class="btn-volver">Ver Dashboard</a>
            <a href="../../../index.php" class="btn-volver">Volver al inicio</a>
            <a href="../../sesiones/logout/logout.php" class="btn-logout">Cerrar Sesión</a>
        </div>

    </div>

</main>

<?php if ($steamVinculado): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const syncButton = document.getElementById('steamSyncButton');
    const feedback = document.getElementById('steamSyncFeedback');
    const bar = document.getElementById('steamSyncBar');
    const percent = document.getElementById('steamSyncPercent');
    const copy = document.getElementById('steamSyncCopy');
    const frame = document.getElementById('steamSyncFrame');

    if (!syncButton || !feedback || !bar || !percent || !copy || !frame) {
        return;
    }

    let syncing = false;
    let pollTimer = null;
    let activeToken = '';

    function buildSyncToken() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return `steam-sync-${window.crypto.randomUUID()}`;
        }

        return `steam-sync-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
    }

    function setProgress(value, message) {
        const safeValue = Math.max(0, Math.min(100, Number(value) || 0));
        bar.style.width = `${safeValue}%`;
        percent.textContent = `${Math.round(safeValue)}%`;

        if (message) {
            copy.textContent = message;
        }
    }

    function setButtonLoadingState(isLoading) {
        syncing = isLoading;
        syncButton.classList.toggle('is-loading', isLoading);
        syncButton.setAttribute('aria-disabled', isLoading ? 'true' : 'false');
        syncButton.style.pointerEvents = isLoading ? 'none' : '';
        syncButton.textContent = isLoading ? 'Sincronizando...' : 'Sincronizar logros de Steam';
    }

    function stopPolling() {
        if (pollTimer) {
            window.clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    async function readProgress(token) {
        try {
            const response = await fetch(`../../../Steam/steam_sync_status.php?token=${encodeURIComponent(token)}&ts=${Date.now()}`, {
                cache: 'no-store'
            });

            if (!response.ok) {
                return null;
            }

            return await response.json();
        } catch (error) {
            return null;
        }
    }

    function finishSync(status, message) {
        stopPolling();

        if (status === 'success') {
            setProgress(100, message);
            window.setTimeout(() => {
                window.location.reload();
            }, 700);
            return;
        }

        setProgress(100, message);
        setButtonLoadingState(false);
    }

    async function pollProgress() {
        if (!activeToken) {
            return;
        }

        const payload = await readProgress(activeToken);
        if (!payload) {
            return;
        }

        setProgress(payload.progress, payload.message);

        if (payload.status === 'success' || payload.status === 'error') {
            finishSync(payload.status, payload.message || 'La sincronizacion ha terminado.');
        }
    }

    function startPolling(token) {
        activeToken = token;
        stopPolling();
        pollTimer = window.setInterval(pollProgress, 450);
        pollProgress();
    }

    syncButton.addEventListener('click', event => {
        event.preventDefault();

        if (syncing) {
            return;
        }

        feedback.hidden = false;
        setButtonLoadingState(true);
        setProgress(0, 'Preparando sincronizacion...');

        activeToken = buildSyncToken();
        startPolling(activeToken);
        const separator = syncButton.href.includes('?') ? '&' : '?';
        frame.src = `${syncButton.href}${separator}mode=iframe&token=${encodeURIComponent(activeToken)}&ts=${Date.now()}`;
    });

    window.addEventListener('message', event => {
        if (event.origin !== window.location.origin) {
            return;
        }

        const payload = event.data || {};
        if (payload.type !== 'steam-sync-result') {
            return;
        }

        if (payload.status === 'success' || payload.status === 'error') {
            finishSync(payload.status, payload.message || 'La sincronizacion no pudo completarse.');
        }
    });
});
</script>
<?php endif; ?>

</body>
</html>
