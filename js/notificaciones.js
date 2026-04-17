document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('bell-icon');
    const dropdown = document.getElementById('notif-dropdown');
    const badge = document.getElementById('notif-badge');
    const list = document.getElementById('notif-list');

    // Usar ruta ABSOLUTA desde la raíz del sitio
    const baseUrl = '/';

    if (bell && dropdown) {
        bell.onclick = function(e) {
            e.stopPropagation();
            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
            } else {
                dropdown.style.display = "block";
                cargarNotificaciones();
            }
        };

        document.onclick = function(e) {
            if (dropdown && !dropdown.contains(e.target) && e.target !== bell) {
                dropdown.style.display = "none";
            }
        };
    }

    function cargarNotificaciones() {
        // Ruta ABSOLUTA desde la raíz
        fetch('/php/notificaciones/notificaciones_ajax.php')
            .then(res => res.json())
            .then(data => {
                console.log("Total notificaciones:", data.total);
                
                if (badge) {
                    if (data.total > 0) {
                        badge.style.display = 'block';
                        badge.innerText = data.total;
                    } else {
                        badge.style.display = 'none';
                    }
                }
                
                const chatBadge = document.getElementById('chat-badge');
                if (chatBadge) {
                    if (data.total > 0) {
                        chatBadge.style.display = 'inline-block';
                        chatBadge.innerText = data.total;
                    } else {
                        chatBadge.style.display = 'none';
                    }
                }
                
                if (list) list.innerHTML = data.html;
            })
            .catch(err => console.error("Error:", err));
    }

    cargarNotificaciones();
    setInterval(cargarNotificaciones, 10000);
});

function marcarLeidas() {
    fetch('/php/notificaciones/marcar_leidas.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('notif-badge');
                if (badge) badge.style.display = 'none';
                
                const chatBadge = document.getElementById('chat-badge');
                if (chatBadge) chatBadge.style.display = 'none';
                
                const list = document.getElementById('notif-list');
                if (list) {
                    list.innerHTML = '<li style="padding: 15px; color: #888; text-align: center;">No tienes notificaciones</li>';
                }
            }
        })
        .catch(err => console.error("Error:", err));
}