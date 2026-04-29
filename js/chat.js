let chatInterval = null;
let receptorNuevoID = null;
let idConversacionActual = null;

function seleccionarContacto(idReceptor, idConv, elemento) {
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    if (elemento) elemento.classList.add('activo');
    
    document.getElementById('chat-header').style.display = 'flex';
    document.getElementById('form-mensaje').style.display = 'flex';

    const nombre = elemento.querySelector('h4').innerText;
    const foto = elemento.querySelector('img').src;
    
    idConversacionActual = idConv;
    
    const esGrupo = (!idReceptor || idReceptor === 'null' || idReceptor === "");

    document.getElementById('header-nombre').innerText = nombre;
    document.getElementById('header-avatar').src = foto;
    document.getElementById('header-estado').innerText = esGrupo ? "Toca para ver info del grupo" : "Ver perfil";
    
    document.getElementById('header-info').onclick = () => {
        if (!esGrupo) {
            window.location.href = `../user/amistades/perfilOtros.php?id=${idReceptor}`;
        } else {
            abrirAjustesGrupo(idConv);
        }
    };

    document.getElementById('id_conversacion_activa').value = idConv;
    receptorNuevoID = idReceptor;
    iniciarBucle(idConv);
    
    const badgeChat = elemento.querySelector('.badge-chat');
    if (badgeChat) badgeChat.style.display = 'none';
    if (typeof actualizarBadgeGeneral === 'function') actualizarBadgeGeneral();
}

function abrirAjustesGrupo(idConv) {
    if (!idConv || idConv == 0) return;
    
    document.getElementById('ajuste_id_conv').value = idConv;
    document.getElementById('modal-ajustes-grupo').style.display = 'flex';
    actualizarListasMiembros(idConv);
}

function actualizarListasMiembros(idConv) {
    fetch(`obtener_info_grupo.php?id_conv=${idConv}`)
        .then(res => res.json())
        .then(data => {
            const inputNombre = document.getElementById('edit-nombre-grupo');
            if (inputNombre) inputNombre.value = data.nombre_grupo;
            
            const listaActual = document.getElementById('lista-gestion-miembros');
            listaActual.innerHTML = '';
            
            data.miembros.forEach(m => {
                const esCreador = (parseInt(m.id_usuario) === parseInt(data.id_creador));
                const puedeEliminar = data.soy_creador && !esCreador;
                
                const div = document.createElement('div');
                div.className = 'miembro-item';
                div.style.display = 'flex';
                div.style.alignItems = 'center';
                div.style.justifyContent = 'space-between';
                div.style.padding = '10px';
                div.style.borderBottom = '1px solid #222';
                div.style.background = '#1a1a1a';
                div.style.marginBottom = '5px';
                div.style.borderRadius = '8px';
                
                div.innerHTML = `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="${m.foto_perfil}" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                        <span style="color:white; font-weight:500;">${m.gameTag} ${esCreador ? '👑' : ''}</span>
                    </div>
                    ${puedeEliminar ? 
                        `<button class="btn-eliminar" onclick="gestionarMiembro(${idConv}, ${m.id_usuario}, 'quitar')" 
                            style="background:#ff4d4d; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:12px;">
                            Eliminar
                        </button>` : 
                        (esCreador ? '<span style="color:#888; font-size:12px;">Administrador</span>' : '')
                    }
                `;
                listaActual.appendChild(div);
            });
            
            const listaNuevos = document.getElementById('lista-añadir-miembros');
            listaNuevos.innerHTML = '';
            
            if (data.amigos_fuera && data.amigos_fuera.length > 0) {
                data.amigos_fuera.forEach(a => {
                    const div = document.createElement('div');
                    div.className = 'amigo-item';
                    div.style.display = 'flex';
                    div.style.alignItems = 'center';
                    div.style.justifyContent = 'space-between';
                    div.style.padding = '10px';
                    div.style.borderBottom = '1px solid #222';
                    div.style.background = '#1a1a1a';
                    div.style.marginBottom = '5px';
                    div.style.borderRadius = '8px';
                    
                    div.innerHTML = `
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="${a.foto_perfil}" style="width:30px; height:30px; border-radius:50%;">
                            <span style="color:#ccc;">${a.gameTag}</span>
                        </div>
                        <button class="btn-anadir" onclick="gestionarMiembro(${idConv}, ${a.id_usuario}, 'añadir')" 
                            style="background:#f0c330; color:black; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:bold;">
                            + Añadir
                        </button>
                    `;
                    listaNuevos.appendChild(div);
                });
            } else {
                listaNuevos.innerHTML = '<div style="padding:20px; color:#555; text-align:center;">No hay amigos para invitar</div>';
            }
        })
        .catch(err => console.error("Error:", err));
}

function gestionarMiembro(idConv, idUser, accion) {
    const fd = new FormData();
    fd.append('id_conv', idConv);
    fd.append('id_user', idUser);
    fd.append('accion', accion);
    
    fetch('gestionar_miembro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                actualizarListasMiembros(idConv);
            }
        })
        .catch(err => console.error("Error:", err));
}

function guardarAjustes() {
    const idConv = document.getElementById('ajuste_id_conv').value;
    const nuevoNombre = document.getElementById('edit-nombre-grupo').value;
    
    if (!nuevoNombre.trim()) return;
    
    const fd = new FormData();
    fd.append('id_conv', idConv);
    fd.append('nombre_grupo', nuevoNombre);
    
    fetch('actualizar_grupo.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('header-nombre').innerText = nuevoNombre;
            }
        })
        .catch(err => console.error("Error:", err));
}

function abandonarGrupo() {
    if (!idConversacionActual) return;
    
    if (!confirm('¿Estás seguro de que quieres abandonar este grupo?')) return;
    
    const fd = new FormData();
    fd.append('id_conv', idConversacionActual);
    fd.append('accion', 'abandonar');
    
    fetch('gestionar_miembro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(err => console.error("Error:", err));
}

function cerrarModalAjustes() {
    document.getElementById('modal-ajustes-grupo').style.display = 'none';
}

function iniciarBucle(id) {
    if (chatInterval) clearInterval(chatInterval);
    const refrescar = () => {
        fetch(`obtener_mensajes.php?id_conversacion=${id}`)
            .then(res => res.text())
            .then(html => {
                const box = document.getElementById('mensajes-scroll');
                if (box.innerHTML !== html) { 
                    box.innerHTML = html; 
                    box.scrollTop = box.scrollHeight; 
                }
            })
            .catch(err => console.error("Error:", err));
    };
    refrescar(); 
    chatInterval = setInterval(refrescar, 2500);
}

// ========== ENVÍO DE TEXTO NORMAL ==========
document.getElementById('form-mensaje').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('input-texto');
    const idConv = document.getElementById('id_conversacion_activa').value;
    if (!input.value.trim()) return;
    
    let p = new URLSearchParams();
    p.append('mensaje', input.value);
    if (idConv && idConv !== "0") {
        p.append('id_conversacion', idConv);
    } else {
        p.append('id_receptor', receptorNuevoID);
    }
    
    fetch('enviar_mensaje.php', { method: 'POST', body: p })
        .then(() => { input.value = ''; })
        .catch(err => console.error("Error:", err));
});

// ========== ENVÍO DE IMÁGENES ==========
const fileInput = document.createElement('input');
fileInput.type = 'file';
fileInput.accept = 'image/jpeg,image/png,image/gif,image/webp';
fileInput.style.display = 'none';
document.body.appendChild(fileInput);

const btnAdjuntar = document.createElement('button');
btnAdjuntar.type = 'button';
btnAdjuntar.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f0c330" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
</svg>`;
btnAdjuntar.style.fontSize = '0';
btnAdjuntar.style.background = 'none';
btnAdjuntar.style.border = 'none';
btnAdjuntar.style.color = '#f0c330';
btnAdjuntar.style.fontSize = '1.5rem';
btnAdjuntar.style.cursor = 'pointer';
btnAdjuntar.style.marginRight = '10px';
btnAdjuntar.style.padding = '5px';

const formMensaje = document.getElementById('form-mensaje');
const inputTexto = document.getElementById('input-texto');
if (formMensaje && inputTexto) {
    formMensaje.insertBefore(btnAdjuntar, inputTexto);
}

btnAdjuntar.onclick = () => fileInput.click();

fileInput.onchange = function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.size > 5 * 1024 * 1024) {
        alert('La imagen no puede superar los 5MB');
        fileInput.value = '';
        return;
    }
    
    const formData = new FormData();
    formData.append('imagen', file);
    const idConv = document.getElementById('id_conversacion_activa').value;
    if (idConv && idConv !== "0") {
        formData.append('id_conversacion', idConv);
    } else {
        formData.append('id_receptor', receptorNuevoID);
    }
    
    fetch('enviar_imagen.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const idConvActual = document.getElementById('id_conversacion_activa').value;
                if (idConvActual) iniciarBucle(idConvActual);
                fileInput.value = '';
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => console.error('Error:', err));
};

function abrirModalGrupo() {
    const modal = document.getElementById('modal-grupo');
    if (modal) modal.style.display = 'flex';
}

function cerrarModalGrupo() {
    const modal = document.getElementById('modal-grupo');
    if (modal) modal.style.display = 'none';
}

function crearGrupoProcesar() {
    const nombreInput = document.getElementById('nombre-grupo');
    const nombre = nombreInput ? nombreInput.value.trim() : "";
    
    const seleccionados = Array.from(document.querySelectorAll('.check-amigo:checked'))
                               .map(cb => cb.value);

    if (!nombre) return alert("Escribe un nombre para el grupo.");
    if (seleccionados.length === 0) return alert("Selecciona al menos un amigo.");

    const fd = new FormData();
    fd.append('nombre', nombre);
    fd.append('usuarios', JSON.stringify(seleccionados));

    fetch('crear_grupo.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(err => console.error("Error:", err));
}