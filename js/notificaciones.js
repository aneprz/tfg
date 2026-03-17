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
        fetch('/php/notificaciones/notificaciones_ajax.php')
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
    console.log("Botón limpiar pulsado..."); // Si ves esto en la consola, el HTML está bien

    // Usamos la ruta absoluta empezando desde la raíz /
    fetch('/php/notificaciones/marcar_leidas.php')
        .then(res => {
            if (!res.ok) throw new Error("Error en la red: " + res.status);
            return res.json();
        })
        .then(data => {
            console.log("Respuesta del servidor:", data);
            if (data.success) {
                // Ocultar el numerito
                const badge = document.getElementById('notif-badge');
                if (badge) badge.style.display = 'none';

                // Vaciar la lista
                const list = document.getElementById('notif-list');
                if (list) {
                    list.innerHTML = '<li style="padding: 15px; color: #888; text-align: center;">No tienes notificaciones</li>';
                }
            }
        })
        .catch(err => console.error("Fallo al limpiar:", err));
}