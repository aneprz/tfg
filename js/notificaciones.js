document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('bell-icon');
    const dropdown = document.getElementById('notif-dropdown');
    const badge = document.getElementById('notif-badge');
    const list = document.getElementById('notif-list');

    if (bell && dropdown) {
        bell.onclick = function(e) {
            e.stopPropagation();
            // Alternar visibilidad
            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                dropdown.style.display = "block";
                console.log("Abriendo notificaciones...");
                cargarNotificaciones();
            }
        };

        // Cerrar al clicar fuera
        document.onclick = function(e) {
            if (!dropdown.contains(e.target) && e.target !== bell) {
                dropdown.style.display = "none";
            }
        };
    }

    function cargarNotificaciones() {
        // Usamos una ruta relativa a la raíz para evitar fallos en otras páginas
        fetch('php/notificaciones/notificaciones_ajax.php')
            .then(res => res.json())
            .then(data => {
                if (badge && data.total > 0) {
                    badge.style.display = 'block';
                    badge.innerText = data.total;
                } else if (badge) {
                    badge.style.display = 'none';
                }
                if (list) list.innerHTML = data.html;
            })
            .catch(err => console.error("Error en fetch:", err));
    }

    // Cargar cada 30 seg
    setInterval(cargarNotificaciones, 30000);
    cargarNotificaciones();
});

function marcarLeidas() {
    fetch('/php/notificaciones/marcar_leidas.php')
        .then(() => {
            const badge = document.getElementById('notif-badge');
            if (badge) badge.style.display = 'none';
            document.getElementById('notif-list').innerHTML = '<li style="padding: 15px; color: #666; text-align: center;">No hay notificaciones</li>';
        });
}