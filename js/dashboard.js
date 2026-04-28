const dashboardCharts = {};

document.addEventListener('DOMContentLoaded', () => {
    cargarDashboard();
});

async function cargarDashboard() {
    try {
        setNotice('Cargando datos del dashboard...', 'info');

        const response = await fetch('/API/stats_usuario.php');
        const data = await response.json();

        if (!response.ok || data.error) {
            throw new Error(data.error || 'No se pudieron cargar las estadisticas.');
        }

        renderDashboard(data);
        setNotice('Dashboard actualizado correctamente.', 'success');
    } catch (error) {
        console.error(error);
        setNotice('No se pudo cargar el dashboard. Intentalo de nuevo en unos segundos.', 'error');
    }
}

function renderDashboard(data) {
    renderRank(data.rank || {});
    renderOverview(data.overview || {}, data.social || {});
    renderCharts(data.charts || {});
    renderInsights(data.insights || {});
    renderTopGames(data.lists?.top_games || []);
    renderRecentReviews(data.lists?.recent_reviews || []);
    renderRecentAchievements(data.lists?.recent_achievements || []);
    renderCommunities(data.lists?.communities || []);
    renderPointMovements(data.lists?.point_movements || []);
    renderSocialStats(data.social || {});
    renderInventory(data.inventory || {});
    renderActivityTimeline(data.activity || {});
}

function renderRank(rank) {
    const label = rank.label || 'Sin rango';
    const nextLabel = rank.next_label;
    const currentPoints = Number(rank.current_points || 0);
    const pointsToNext = Number(rank.points_to_next || 0);
    const progress = clamp(Number(rank.progress_percent || 0), 0, 100);

    setText('rankLabel', label);

    if (nextLabel) {
        setText(
            'rankCopy',
            `${formatInteger(currentPoints)} pts acumulados. Te faltan ${formatInteger(pointsToNext)} para ${nextLabel}.`
        );
    } else {
        setText('rankCopy', `${formatInteger(currentPoints)} pts acumulados. Ya estas en el rango mas alto.`);
    }

    const bar = document.getElementById('rankBar');
    if (bar) {
        bar.style.width = `${progress}%`;
    }
}

function renderOverview(overview, social) {
    const avgHours = Number(overview.horas_totales || 0) / Math.max(1, Number(overview.juegos_totales || 0));

    setText('kpiHours', formatDecimal(overview.horas_totales || 0));
    setText('kpiHoursMeta', `${formatDecimal(avgHours)} horas por juego`);

    setText('kpiGames', formatInteger(overview.juegos_totales || 0));
    setText(
        'kpiGamesMeta',
        `${formatInteger(overview.pendientes || 0)} pendientes y ${formatInteger(overview.jugando || 0)} en juego`
    );

    setText('kpiCompleted', formatInteger(overview.completados || 0));
    setText('kpiCompletedMeta', `${formatPercent(overview.ratio_completado || 0)} de tu biblioteca cerrada`);

    setText('kpiRating', formatScore(overview.media_puntuacion || 0));
    setText(
        'kpiRatingMeta',
        Number(overview.media_puntuacion || 0) > 0
            ? `${formatPercent(overview.ratio_abandono || 0)} de abandono en juegos finalizados`
            : 'Aun no has generado media suficiente'
    );

    setText('kpiPoints', formatInteger(overview.puntos_actuales || 0));
    setText(
        'kpiPointsMeta',
        `${formatInteger(overview.puntos_ganados_totales || 0)} ganados | ${formatInteger(overview.puntos_gastados || 0)} gastados`
    );

    setText('kpiAchievements', formatInteger(overview.logros_desbloqueados || 0));
    setText(
        'kpiAchievementsMeta',
        `${formatPercent(overview.porcentaje_logros || 0)} del catalogo disponible`
    );

    setText('kpiFriends', formatInteger(overview.amigos || 0));
    setText(
        'kpiFriendsMeta',
        `${formatInteger(social.pending_requests || 0)} solicitudes pendientes | ${formatInteger(social.unread_chat_messages || 0)} chats sin leer`
    );

    setText('kpiCommunities', formatInteger(overview.comunidades || 0));
    setText(
        'kpiCommunitiesMeta',
        `${formatInteger(social.posts || 0)} posts | ${formatInteger(social.likes_received || 0)} likes recibidos`
    );
}

function renderCharts(charts) {
    renderRatingChart(charts.rating_evolution || []);
    renderPointsChart(charts.points_evolution || []);
    renderStatesChart(charts.state_distribution || []);
    renderGenresChart(charts.top_genres || []);
}

function renderRatingChart(rows) {
    const ctx = prepareChartCanvas('chartRating', rows.length > 0, 'Todavia no hay reseñas suficientes para mostrar la evolucion.');
    if (!ctx) {
        return;
    }

    dashboardCharts.chartRating = new Chart(ctx, {
        type: 'line',
        data: {
            labels: rows.map(row => formatMonth(row.mes)),
            datasets: [{
                label: 'Puntuacion media',
                data: rows.map(row => Number(row.media || 0)),
                borderColor: '#e0be00',
                backgroundColor: 'rgba(224, 190, 0, 0.18)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 5
            }]
        },
        options: buildChartOptions({
            scales: {
                y: {
                    min: 0,
                    max: 10,
                    ticks: {
                        stepSize: 2
                    }
                }
            }
        })
    });
}

function renderPointsChart(rows) {
    const ctx = prepareChartCanvas('chartPoints', rows.length > 0, 'Aun no hay movimientos de puntos para este grafico.');
    if (!ctx) {
        return;
    }

    dashboardCharts.chartPoints = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: rows.map(row => formatMonth(row.mes)),
            datasets: [
                {
                    label: 'Ganados',
                    data: rows.map(row => Number(row.ganados || 0)),
                    backgroundColor: 'rgba(73, 211, 158, 0.75)',
                    borderRadius: 8
                },
                {
                    label: 'Gastados',
                    data: rows.map(row => Number(row.gastados || 0)),
                    backgroundColor: 'rgba(255, 107, 107, 0.72)',
                    borderRadius: 8
                }
            ]
        },
        options: buildChartOptions()
    });
}

function renderStatesChart(rows) {
    const ctx = prepareChartCanvas('chartStates', rows.length > 0, 'No hay suficientes juegos para mostrar la distribucion de estados.');
    if (!ctx) {
        return;
    }

    dashboardCharts.chartStates = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: rows.map(row => row.label),
            datasets: [{
                data: rows.map(row => Number(row.value || 0)),
                backgroundColor: ['#e0be00', '#00c2ff', '#49d39e', '#ff6b6b'],
                borderColor: '#1f252c',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#dbe6f0'
                    }
                }
            }
        }
    });
}

function renderGenresChart(rows) {
    const ctx = prepareChartCanvas('chartGenres', rows.length > 0, 'Juega un poco mas para descubrir tus generos dominantes.');
    if (!ctx) {
        return;
    }

    dashboardCharts.chartGenres = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: rows.map(row => row.label),
            datasets: [{
                label: 'Horas',
                data: rows.map(row => Number(row.hours || 0)),
                backgroundColor: ['#e0be00', '#cda600', '#00c2ff', '#49d39e', '#7ba6ff'],
                borderRadius: 10
            }]
        },
        options: buildChartOptions({
            indexAxis: 'y'
        })
    });
}

function renderInsights(insights) {
    const cards = [
        {
            title: 'Backlog',
            value: `${formatInteger(insights.backlog_count || 0)} juegos`,
            copy: `${formatPercent((Number(insights.backlog_percent || 0)) / 100)} de tu biblioteca sigue pendiente.`
        },
        {
            title: 'Partidas activas',
            value: `${formatInteger(insights.active_games || 0)} en curso`,
            copy: `Media global de ${formatDecimal(insights.avg_hours_per_game || 0)} horas por juego.`
        },
        {
            title: 'Estilo de juego',
            value: insights.play_style?.label || 'Sin datos',
            copy: insights.play_style?.copy || 'Todavia no hay un patron claro.'
        },
        {
            title: 'Genero favorito',
            value: insights.favorite_genre?.label || 'Sin definir',
            copy: insights.favorite_genre
                ? `${formatDecimal(insights.favorite_genre.hours || 0)} horas repartidas en ${formatInteger(insights.favorite_genre.games || 0)} apariciones.`
                : 'Aun no hay suficientes juegos para detectarlo.'
        },
        {
            title: 'Plataforma dominante',
            value: insights.favorite_platform?.label || 'Sin definir',
            copy: insights.favorite_platform
                ? `${formatDecimal(insights.favorite_platform.hours || 0)} horas acumuladas.`
                : 'Aun no hay suficientes datos.'
        },
        {
            title: 'Juego estrella',
            value: insights.most_played_game?.titulo || 'Sin datos',
            copy: insights.most_played_game
                ? `${formatDecimal(insights.most_played_game.horas_totales || 0)} horas y estado ${insights.most_played_game.estado}.`
                : 'Tu biblioteca todavia esta vacia.'
        },
        {
            title: 'Foco en logros',
            value: insights.achievement_focus?.titulo || 'Sin lider claro',
            copy: insights.achievement_focus
                ? `${formatInteger(insights.achievement_focus.total || 0)} logros desbloqueados en ese juego.`
                : 'Todavia no hay logros suficientes para destacarlo.'
        },
        {
            title: 'Logro mas raro',
            value: insights.rarest_achievement?.nombre_logro || 'Sin logro raro',
            copy: insights.rarest_achievement?.porcentaje_global != null
                ? `${formatDecimal(insights.rarest_achievement.porcentaje_global)}% global en ${insights.rarest_achievement.titulo}.`
                : 'Aun no hay porcentaje global disponible.'
        }
    ];

    document.getElementById('insightCards').innerHTML = cards.map(card => `
        <article class="dashboard-mini-card">
            <span class="mini-card-title">${escapeHtml(card.title)}</span>
            <strong class="mini-card-value">${escapeHtml(card.value)}</strong>
            <p class="mini-card-copy">${escapeHtml(card.copy)}</p>
        </article>
    `).join('');
}

function renderTopGames(games) {
    const container = document.getElementById('topGamesList');

    if (!games.length) {
        container.innerHTML = emptyState('Todavia no hay juegos en tu biblioteca.');
        return;
    }

    const maxHours = Math.max(...games.map(game => Number(game.horas_totales || 0)), 1);

    container.innerHTML = games.map(game => {
        const width = clamp((Number(game.horas_totales || 0) / maxHours) * 100, 8, 100);
        const score = game.puntuacion != null ? `${formatScore(game.puntuacion)}/10` : 'Sin nota';

        return `
            <article class="dashboard-list-item">
                <div class="list-item-main">
                    <div class="list-item-topline">
                        <a class="list-item-title" href="/php/videojuegos/juego.php?id=${Number(game.id_videojuego || 0)}">${escapeHtml(game.titulo || 'Sin titulo')}</a>
                        <span class="list-item-value">${formatDecimal(game.horas_totales || 0)} h</span>
                    </div>
                    <p class="list-item-meta">${escapeHtml(game.estado || 'Sin estado')} · ${escapeHtml(score)}</p>
                    <div class="list-progress">
                        <span style="width:${width}%"></span>
                    </div>
                </div>
            </article>
        `;
    }).join('');
}

function renderRecentReviews(reviews) {
    const container = document.getElementById('recentReviewsList');

    if (!reviews.length) {
        container.innerHTML = emptyState('Todavia no has publicado reseñas recientes.');
        return;
    }

    container.innerHTML = reviews.map(review => `
        <article class="dashboard-list-item">
            <div class="list-item-topline">
                <span class="list-item-title">${escapeHtml(review.titulo || 'Sin juego')}</span>
                <span class="list-item-value">${review.puntuacion != null ? `${formatScore(review.puntuacion)}/10` : 'Sin nota'}</span>
            </div>
            <p class="list-item-meta">${escapeHtml(formatDate(review.fecha_publicacion))}</p>
            <p class="list-item-copy">${escapeHtml(review.texto_resena || 'Sin texto de reseña.')}</p>
        </article>
    `).join('');
}

function renderRecentAchievements(achievements) {
    const container = document.getElementById('recentAchievementsList');

    if (!achievements.length) {
        container.innerHTML = emptyState('Todavia no hay logros recientes que mostrar.');
        return;
    }

    container.innerHTML = achievements.map(achievement => {
        const rarity = achievement.porcentaje_global != null
            ? `${formatDecimal(achievement.porcentaje_global)}% global`
            : 'Rareza no disponible';

        return `
            <article class="dashboard-list-item">
                <div class="list-item-topline">
                    <span class="list-item-title">${escapeHtml(achievement.nombre_logro || 'Sin logro')}</span>
                    <span class="list-item-value">${escapeHtml(rarity)}</span>
                </div>
                <p class="list-item-meta">${escapeHtml(achievement.titulo || 'Sin juego')} · ${escapeHtml(formatDate(achievement.fecha_obtencion))}</p>
            </article>
        `;
    }).join('');
}

function renderCommunities(communities) {
    const container = document.getElementById('communitiesList');

    if (!communities.length) {
        container.innerHTML = emptyState('Todavia no perteneces a ninguna comunidad.');
        return;
    }

    container.innerHTML = communities.map(community => `
        <article class="dashboard-list-item">
            <div class="list-item-topline">
                <a class="list-item-title" href="/php/comunidades/ver_comunidad.php?id=${Number(community.id_comunidad || 0)}">${escapeHtml(community.nombre || 'Sin nombre')}</a>
                <span class="list-item-value">${formatInteger(community.miembros || 0)} miembros</span>
            </div>
            <p class="list-item-meta">${escapeHtml(community.juego || 'Sin juego')} · ${formatInteger(community.posts || 0)} posts</p>
        </article>
    `).join('');
}

function renderPointMovements(movements) {
    const container = document.getElementById('pointMovementsList');

    if (!movements.length) {
        container.innerHTML = emptyState('Aun no hay movimientos de puntos registrados.');
        return;
    }

    container.innerHTML = movements.map(movement => {
        const sign = Number(movement.puntos || 0) > 0 ? '+' : '';
        const toneClass = Number(movement.puntos || 0) >= 0 ? 'is-positive' : 'is-negative';

        return `
            <article class="dashboard-list-item">
                <div class="list-item-topline">
                    <span class="list-item-title">${escapeHtml(movement.descripcion || 'Movimiento')}</span>
                    <span class="list-item-value ${toneClass}">${sign}${formatInteger(movement.puntos || 0)}</span>
                </div>
                <p class="list-item-meta">${escapeHtml(capitalize(movement.tipo || 'sistema'))} · ${escapeHtml(formatDate(movement.fecha))}</p>
            </article>
        `;
    }).join('');
}

function renderSocialStats(social) {
    const cards = [
        { title: 'Posts', value: formatInteger(social.posts || 0), copy: 'Publicaciones realizadas' },
        { title: 'Likes', value: formatInteger(social.likes_received || 0), copy: 'Likes recibidos en tus posts' },
        { title: 'Pendientes', value: formatInteger(social.pending_requests || 0), copy: 'Solicitudes de amistad' },
        { title: 'Chat', value: formatInteger(social.unread_chat_messages || 0), copy: 'Mensajes sin leer' },
        { title: 'Alertas', value: formatInteger(social.unread_notifications || 0), copy: 'Notificaciones sin leer' },
        { title: 'Comunidades', value: formatInteger(social.communities || 0), copy: 'Espacios donde participas' }
    ];

    document.getElementById('socialStats').innerHTML = cards.map(card => `
        <article class="dashboard-mini-card">
            <span class="mini-card-title">${escapeHtml(card.title)}</span>
            <strong class="mini-card-value">${escapeHtml(card.value)}</strong>
            <p class="mini-card-copy">${escapeHtml(card.copy)}</p>
        </article>
    `).join('');
}

function renderInventory(inventory) {
    const container = document.getElementById('inventorySummary');
    const equippedItems = Object.entries(inventory.equipped || {})
        .filter(([, item]) => item);

    const typeBadges = (inventory.counts_by_type || []).length
        ? (inventory.counts_by_type || []).map(item => badgeHtml(item.label, item.value)).join('')
        : '<span class="dashboard-badge">Sin items</span>';

    const rarityBadges = (inventory.counts_by_rarity || []).length
        ? (inventory.counts_by_rarity || []).map(item => badgeHtml(item.label, item.value)).join('')
        : '<span class="dashboard-badge">Sin rarezas</span>';

    const equippedHtml = equippedItems.length
        ? equippedItems.map(([slot, item]) => `
            <article class="dashboard-list-item dashboard-list-item-compact">
                <div class="list-item-topline">
                    <span class="list-item-title">${escapeHtml(capitalize(slot))}</span>
                    <span class="list-item-value">Equipado</span>
                </div>
                <p class="list-item-meta">${escapeHtml(item.nombre || 'Sin item')}</p>
            </article>
        `).join('')
        : emptyState('Todavia no tienes items equipados.');

    container.innerHTML = `
        <div class="dashboard-inline-stats">
            <div class="dashboard-stat-pill">
                <strong>${formatInteger(inventory.total_items || 0)}</strong>
                <span>items totales</span>
            </div>
            <div class="dashboard-stat-pill">
                <strong>${formatInteger(inventory.equipped_count || 0)}</strong>
                <span>equipados</span>
            </div>
            <div class="dashboard-stat-pill">
                <strong>${escapeHtml(capitalize(inventory.highest_rarity || 'sin rareza'))}</strong>
                <span>rareza maxima</span>
            </div>
        </div>
        <div class="dashboard-subpanel">
            <h4>Por tipo</h4>
            <div class="dashboard-badge-row">${typeBadges}</div>
        </div>
        <div class="dashboard-subpanel">
            <h4>Por rareza</h4>
            <div class="dashboard-badge-row">${rarityBadges}</div>
        </div>
        <div class="dashboard-subpanel">
            <h4>Equipado ahora</h4>
            <div class="dashboard-list">${equippedHtml}</div>
        </div>
    `;
}

function renderActivityTimeline(activity) {
    const entries = [];

    if (activity.latest_review) {
        entries.push({
            title: 'Ultima reseña',
            value: activity.latest_review.titulo,
            meta: `${formatScore(activity.latest_review.puntuacion || 0)}/10 · ${formatDate(activity.latest_review.fecha)}`
        });
    }

    if (activity.latest_achievement) {
        entries.push({
            title: 'Ultimo logro',
            value: activity.latest_achievement.nombre_logro,
            meta: `${activity.latest_achievement.titulo} · ${formatDate(activity.latest_achievement.fecha)}`
        });
    }

    if (activity.latest_purchase) {
        entries.push({
            title: 'Ultima compra',
            value: activity.latest_purchase.nombre,
            meta: `${capitalize(activity.latest_purchase.rareza || 'comun')} · ${formatDate(activity.latest_purchase.fecha)}`
        });
    }

    if (activity.latest_post) {
        entries.push({
            title: 'Ultimo post',
            value: activity.latest_post.nombre,
            meta: `${activity.latest_post.contenido || 'Sin contenido'} · ${formatDate(activity.latest_post.fecha)}`
        });
    }

    if (activity.latest_friend) {
        entries.push({
            title: 'Ultima amistad',
            value: activity.latest_friend.gameTag,
            meta: formatDate(activity.latest_friend.fecha)
        });
    }

    const container = document.getElementById('activityTimeline');

    if (!entries.length) {
        container.innerHTML = emptyState('Aun no hay actividad reciente para resumir.');
        return;
    }

    container.innerHTML = entries.map(entry => `
        <article class="dashboard-list-item timeline-item">
            <span class="timeline-dot"></span>
            <div class="timeline-content">
                <div class="list-item-topline">
                    <span class="list-item-title">${escapeHtml(entry.title)}</span>
                    <span class="list-item-value">${escapeHtml(entry.value)}</span>
                </div>
                <p class="list-item-meta">${escapeHtml(entry.meta)}</p>
            </div>
        </article>
    `).join('');
}

function prepareChartCanvas(canvasId, hasData, emptyMessage) {
    destroyChart(canvasId);

    const existingCanvas = document.getElementById(canvasId);
    if (!existingCanvas) {
        return null;
    }

    const wrapper = existingCanvas.closest('.chart-wrapper');
    if (!wrapper) {
        return null;
    }

    if (!hasData) {
        wrapper.innerHTML = emptyState(emptyMessage);
        return null;
    }

    return existingCanvas.getContext('2d');
}

function destroyChart(canvasId) {
    if (dashboardCharts[canvasId]) {
        dashboardCharts[canvasId].destroy();
        delete dashboardCharts[canvasId];
    }
}

function buildChartOptions(extra = {}) {
    const extraScales = extra.scales || {};
    const extraPlugins = extra.plugins || {};
    const extraYTicks = (extraScales.y && extraScales.y.ticks) || {};
    const extraYGrid = (extraScales.y && extraScales.y.grid) || {};
    const extraXTicks = (extraScales.x && extraScales.x.ticks) || {};
    const extraXGrid = (extraScales.x && extraScales.x.grid) || {};
    const topLevel = { ...extra };

    delete topLevel.scales;
    delete topLevel.plugins;

    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#dbe6f0'
                }
            },
            ...extraPlugins
        },
        scales: {
            x: {
                ...(extraScales.x || {}),
                ticks: {
                    color: '#b8c6d3',
                    ...extraXTicks
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.06)',
                    ...extraXGrid
                }
            },
            y: {
                ...(extraScales.y || {}),
                ticks: {
                    color: '#b8c6d3',
                    ...extraYTicks
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.06)',
                    ...extraYGrid
                }
            }
        },
        ...topLevel
    };
}

function setNotice(message, tone) {
    const notice = document.getElementById('dashboardNotice');
    if (!notice) {
        return;
    }

    notice.textContent = message;
    notice.className = `dashboard-notice is-${tone}`;
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) {
        element.textContent = value;
    }
}

function badgeHtml(label, value) {
    return `<span class="dashboard-badge"><strong>${formatInteger(value || 0)}</strong> ${escapeHtml(label || 'Dato')}</span>`;
}

function emptyState(message) {
    return `<div class="dashboard-empty">${escapeHtml(message)}</div>`;
}

function formatMonth(value) {
    if (!value || !value.includes('-')) {
        return 'Sin fecha';
    }

    const [year, month] = value.split('-').map(Number);
    const date = new Date(year, month - 1, 1);

    return date.toLocaleDateString('es-ES', {
        month: 'short',
        year: 'numeric'
    }).replace('.', '').replace(/^./, letter => letter.toUpperCase());
}

function formatDate(value) {
    if (!value) {
        return 'Sin fecha';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return 'Sin fecha';
    }

    return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    }).replace('.', '').replace(/^./, letter => letter.toUpperCase());
}

function formatInteger(value) {
    return new Intl.NumberFormat('es-ES', {
        maximumFractionDigits: 0
    }).format(Number(value || 0));
}

function formatDecimal(value) {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: Number(value || 0) % 1 === 0 ? 0 : 1,
        maximumFractionDigits: 1
    }).format(Number(value || 0));
}

function formatScore(value) {
    return new Intl.NumberFormat('es-ES', {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1
    }).format(Number(value || 0));
}

function formatPercent(ratio) {
    return `${(Number(ratio || 0) * 100).toFixed(1)}%`;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function capitalize(value) {
    const text = String(value || '');
    if (!text) {
        return '';
    }

    return text.charAt(0).toUpperCase() + text.slice(1);
}

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}
