let idReceptorNuevo = null; // Para cuando el chat no existe aún

function seleccionarContacto(idUsuario, idConversacion, elemento) {
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    elemento.classList.add('activo');

    const box = document.getElementById('mensajes-scroll');
    const form = document.getElementById('form-mensaje');
    
    if (idConversacion) {
        // Si el chat ya existe, cargamos normal
        idReceptorNuevo = null;
        cargarChat(idConversacion, elemento);
    } else {
        // Si el chat es nuevo
        if (chatInterval) clearInterval(chatInterval);
        idReceptorNuevo = idUsuario;
        document.getElementById('id_conversacion_activa').value = "";
        box.innerHTML = '<p style="text-align:center; color:#888; margin-top:100px;">Estás iniciando una conversación nueva.</p>';
        form.style.display = 'flex';
    }
}

// Modifica el evento del formulario para manejar la creación
document.getElementById('form-mensaje').addEventListener('submit', function(e) {
    e.preventDefault();
    const idConv = document.getElementById('id_conversacion_activa').value;
    const texto = document.getElementById('input-texto').value;

    if (!texto.trim()) return;

    // Si no hay idConv, enviamos también el idReceptorNuevo
    let body = `mensaje=${encodeURIComponent(texto)}`;
    if (idConv) {
        body += `&id_conversacion=${idConv}`;
    } else {
        body += `&id_receptor=${idReceptorNuevo}`;
    }

    fetch('enviar_mensaje.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('input-texto').value = '';
        if (data.nueva_id_conversacion) {
            // Si se creó un chat, recargamos la página para que aparezca el ID
            location.reload(); 
        } else {
            cargarChat(idConv, document.querySelector('.chat-item.activo'));
        }
    });
});

// Enviar mensaje por AJAX
document.getElementById('form-mensaje').addEventListener('submit', function(e) {
    e.preventDefault();
    const id = document.getElementById('id_conversacion_activa').value;
    const texto = document.getElementById('input-texto').value;

    if (!texto.trim()) return;

    fetch('enviar_mensaje.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_conversacion=${id}&mensaje=${encodeURIComponent(texto)}`
    })
    .then(() => {
        document.getElementById('input-texto').value = '';
        cargarChat(id, document.querySelector('.chat-item.activo')); // Recargar
    });
});

// Variable para guardar el intervalo y que no se duplique
let chatInterval = null;

function cargarChat(id, elemento) {
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    elemento.classList.add('activo');

    document.getElementById('id_conversacion_activa').value = id;
    document.getElementById('form-mensaje').style.display = 'flex';

    // Limpiar intervalo anterior si existe
    if (chatInterval) clearInterval(chatInterval);

    // Función interna para refrescar
    const refrescar = () => {
        fetch(`obtener_mensajes.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                const box = document.getElementById('mensajes-scroll');
                // Solo hacemos scroll si el usuario está al final (opcional)
                const isAtBottom = box.scrollHeight - box.clientHeight <= box.scrollTop + 10;
                
                box.innerHTML = html;
                
                if (isAtBottom) {
                    box.scrollTop = box.scrollHeight;
                }
            });
    };

    refrescar(); // Carga inmediata
    chatInterval = setInterval(refrescar, 3000); // Refresca cada 3 segundos
}