async function cargarStats() {
    try {
        const res = await fetch('/API/stats_usuario.php');
        const data = await res.json();

        if (data.error) return alert(data.error);

        document.getElementById('horas').innerText = data.horas_totales;
        document.getElementById('juegos').innerText = data.juegos_totales;
        document.getElementById('completados').innerText = data.completados;
        document.getElementById('ratio').innerText =
            (data.ratio_abandono * 100).toFixed(1) + '%';

        // ✅ YA VIENE EN 0–10 DIRECTO
        const labels = data.evolucion.map(e => formatearMes(e.mes));
        const valores = data.evolucion.map(e => Number(e.media));

        new Chart(document.getElementById('grafica'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Puntuación (0-10)',
                    data: valores,
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 0,
                        max: 10
                    }
                }
            }
        });

        const ul = document.getElementById('top');
        ul.innerHTML = '';

        data.top_juegos.forEach(j => {
            ul.innerHTML += `<li>${j.titulo} - ${j.horas_totales}h</li>`;
        });

    } catch (err) {
        console.error(err);
    }
}

function formatearMes(valorMes) {
    const [anio, mes] = valorMes.split('-').map(Number);
    const fecha = new Date(anio, mes - 1, 1);

    return fecha.toLocaleDateString('es-ES', {
        month: 'short',
        year: 'numeric'
    }).replace('.', '').replace(/^./, letra => letra.toUpperCase());
}

cargarStats();
