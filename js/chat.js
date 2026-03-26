let chatInterval = null;
let receptorNuevoID = null;

function seleccionarContacto(idUsuario, idConv, elemento) {
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    elemento.classList.add('activo');

    if (chatInterval) clearInterval(chatInterval);
    document.getElementById('form-mensaje').style.display = 'flex';
    document.getElementById('input-texto').value = '';

    if (idConv && idConv !== 'null') {
        receptorNuevoID = null;
        document.getElementById('id_conversacion_activa').value = idConv;
        iniciarBucle(idConv);
    } else {
        receptorNuevoID = idUsuario;
        document.getElementById('id_conversacion_activa').value = '';
        document.getElementById('mensajes-scroll').innerHTML = '<p style="text-align:center; color:#888; margin-top:100px;">Di hola para empezar.</p>';
    }
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