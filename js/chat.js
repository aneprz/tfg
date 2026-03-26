let chatInterval = null;
let receptorNuevoID = null;

function seleccionarContacto(idReceptor, idConv, elemento) {
    // 1. Marcar el chat como activo visualmente
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    elemento.classList.add('activo');

    // 2. Limpiar bucles anteriores
    if (chatInterval) clearInterval(chatInterval);

    // 3. Configurar IDs
    document.getElementById('id_conversacion_activa').value = idConv;
    document.getElementById('form-mensaje').style.display = 'flex';
    
    // Si idReceptor es null, es un grupo. Si no, es individual.
    receptorNuevoID = idReceptor; 

    // 4. Iniciar carga de mensajes
    iniciarBucle(idConv);
}

function iniciarBucle(id) {
    const refrescar = () => {
        // Dentro de la función refrescar en chat.js
        fetch(`obtener_mensajes.php?id=${id}`)
            .then(res => res.text()) // Importante que sea .text()
            .then(html => {
                const box = document.getElementById('mensajes-scroll');
                if (box.innerHTML !== html) {
                    box.innerHTML = html;
                    box.scrollTop = box.scrollHeight;
                }
            });
    };
    refrescar();
    chatInterval = setInterval(refrescar, 2500);
}

document.getElementById('form-mensaje').addEventListener('submit', function(e) {
    e.preventDefault();
    const texto = document.getElementById('input-texto').value.trim();
    const idConv = document.getElementById('id_conversacion_activa').value;
    if (!texto) return;

    let params = `mensaje=${encodeURIComponent(texto)}`;
    if (idConv) params += `&id_conversacion=${idConv}`;
    else params += `&id_receptor=${receptorNuevoID}`;

    fetch('enviar_mensaje.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('input-texto').value = '';
        if (data.nueva_id_conversacion) location.reload();
    });
});

function abrirModalGrupo() {
    document.getElementById('modal-grupo').style.display = 'flex';
}

function cerrarModalGrupo() {
    document.getElementById('modal-grupo').style.display = 'none';
    document.getElementById('nombre-grupo').value = '';
}

function crearGrupoProcesar() {
    const nombre = document.getElementById('nombre-grupo').value.trim();
    const seleccionados = Array.from(document.querySelectorAll('.check-amigo:checked')).map(cb => cb.value);

    if (!nombre) { alert("Ponle un nombre al grupo"); return; }
    if (seleccionados.length < 1) { alert("Selecciona al menos a un amigo"); return; }

    const fd = new FormData();
    fd.append('nombre', nombre);
    fd.append('usuarios', JSON.stringify(seleccionados));

    fetch('crear_grupo.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Recargamos para que aparezca el nuevo grupo en la lista
        } else {
            alert("Error al crear el grupo");
        }
    });
}