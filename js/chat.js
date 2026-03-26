let chatInterval = null;
let receptorNuevoID = null;

/**
 * SELECCIONAR CONTACTO
 */
function seleccionarContacto(idReceptor, idConv, elemento) {
    // Marcado visual
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    elemento.classList.add('activo');
    
    // Mostrar Header y Formulario
    document.getElementById('chat-header').style.display = 'flex';
    document.getElementById('form-mensaje').style.display = 'flex';

    // Obtener datos
    const nombre = elemento.querySelector('h4').innerText;
    const foto = elemento.querySelector('img').src;
    const esGrupo = (idReceptor === null || idReceptor === 'null');

    // Rellenar Header
    document.getElementById('header-nombre').innerText = nombre;
    document.getElementById('header-avatar').src = foto;
    document.getElementById('header-estado').innerText = esGrupo ? "Toca para ver info del grupo" : "Ver perfil";
    
    // Configurar clicks del Header
    document.getElementById('header-info').onclick = () => {
        if (!esGrupo) {
            window.location.href = `../user/perfiles/perfilOtros.php?id=${idReceptor}`;
        } else {
            abrirAjustesGrupo(idConv);
        }
    };

    // Configurar botón de ajustes (la rueda)
    const btnAjustes = document.getElementById('btn-ajustes-grupo');
    if (esGrupo) {
        btnAjustes.style.display = 'block';
        btnAjustes.onclick = (e) => {
            e.stopPropagation(); // Evita conflictos con el click del header
            abrirAjustesGrupo(idConv);
        };
    } else {
        btnAjustes.style.display = 'none';
    }

    // Cargar mensajes
    document.getElementById('id_conversacion_activa').value = idConv;
    receptorNuevoID = idReceptor;
    iniciarBucle(idConv);
}

/**
 * BUCLE DE MENSAJES
 */
function iniciarBucle(id) {
    // Limpiamos cualquier bucle anterior para no saturar el navegador
    if (chatInterval) clearInterval(chatInterval);

    const refrescar = () => {
        // Corregido: Usamos id_conversacion para coincidir con tu PHP
        fetch(`obtener_mensajes.php?id_conversacion=${id}`)
            .then(res => res.text())
            .then(html => {
                const box = document.getElementById('mensajes-scroll');
                // Solo actualizamos el scroll si el contenido ha cambiado
                if (box.innerHTML !== html) {
                    box.innerHTML = html;
                    box.scrollTop = box.scrollHeight;
                }
            });
    };
    
    refrescar();
    chatInterval = setInterval(refrescar, 2500);
}

/**
 * ENVIAR MENSAJE
 */
document.getElementById('form-mensaje').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('input-texto');
    const texto = input.value.trim();
    const idConv = document.getElementById('id_conversacion_activa').value;
    
    if (!texto) return;

    let params = new URLSearchParams();
    params.append('mensaje', texto);
    if (idConv && idConv !== "0") {
        params.append('id_conversacion', idConv);
    } else {
        params.append('id_receptor', receptorNuevoID);
    }

    fetch('enviar_mensaje.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(res => res.json())
    .then(data => {
        input.value = '';
        if (data.nueva_id_conversacion) {
            // Si es un chat nuevo, recargamos para obtener la ID real
            location.reload();
        }
    });
});

/**
 * GESTIÓN DE GRUPOS (CREACIÓN)
 */
function abrirModalGrupo() { document.getElementById('modal-grupo').style.display = 'flex'; }
function cerrarModalGrupo() { 
    document.getElementById('modal-grupo').style.display = 'none';
    document.getElementById('nombre-grupo').value = '';
}

function crearGrupoProcesar() {
    const nombre = document.getElementById('nombre-grupo').value.trim();
    const seleccionados = Array.from(document.querySelectorAll('.check-amigo:checked')).map(cb => cb.value);

    if (!nombre) return alert("Ponle un nombre al grupo");
    if (seleccionados.length < 1) return alert("Selecciona al menos a un amigo");

    const fd = new FormData();
    fd.append('nombre', nombre);
    fd.append('usuarios', JSON.stringify(seleccionados));

    fetch('crear_grupo.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
        if (data.success) location.reload();
        else alert("Error: " + data.error);
    });
}

/**
 * AJUSTES DE GRUPO (EDICIÓN Y MIEMBROS)
 */
function abrirAjustesGrupo(idConv) {
    document.getElementById('ajuste_id_conv').value = idConv;
    const modal = document.getElementById('modal-ajustes-grupo');
    modal.style.display = 'flex';
    
    const nombreActual = document.querySelector('.chat-item.activo h4').innerText;
    document.getElementById('edit-nombre-grupo').value = nombreActual;

    // Cargar miembros y amigos (para añadir/quitar)
    actualizarListasMiembros(idConv);
}

function actualizarListasMiembros(idConv) {
    fetch(`obtener_info_grupo.php?id_conv=${idConv}`)
        .then(res => res.json())
        .then(data => {
            const listaActual = document.getElementById('lista-gestion-miembros');
            const listaNuevos = document.getElementById('lista-añadir-miembros');
            
            // 1. MIEMBROS ACTUALES
            if (data.miembros.length > 0) {
                listaActual.innerHTML = data.miembros.map(m => `
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <span style="color:white; font-size:14px;">${m.gameTag}</span>
                            ${m.es_creador ? '<span title="Creador" style="font-size:12px;">👑</span>' : ''}
                        </div>
                        ${(!m.es_creador && data.soy_creador) ? 
                            `<button type="button" onclick="gestionarMiembro(${idConv}, ${m.id_usuario}, 'quitar')" 
                             style="background:#ff4444; color:white; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:10px;">Eliminar</button>` 
                            : ''}
                    </div>
                `).join('');
            } else {
                listaActual.innerHTML = '<div style="padding:10px; color:#666;">No hay miembros</div>';
            }

            // 2. AÑADIR NUEVOS (AMIGOS)
            if (data.amigos_fuera.length > 0) {
                listaNuevos.innerHTML = data.amigos_fuera.map(a => `
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222;">
                        <span style="color:#ccc; font-size:13px;">${a.gameTag}</span>
                        <button type="button" onclick="gestionarMiembro(${idConv}, ${a.id_usuario}, 'añadir')" 
                         style="background:#f0c330; color:black; border:none; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:10px; font-weight:bold;">+ Añadir</button>
                    </div>
                `).join('');
            } else {
                listaNuevos.innerHTML = '<div style="padding:10px; color:#666; font-size:12px;">No hay amigos para añadir</div>';
            }
        })
        .catch(err => console.error("Error cargando miembros:", err));
}

function gestionarMiembro(idConv, idUser, accion) {
    const fd = new FormData();
    fd.append('id_conv', idConv);
    fd.append('id_user', idUser);
    fd.append('accion', accion);

    fetch('gestionar_miembro.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
        if(data.success) actualizarListasMiembros(idConv);
        else alert(data.error);
    });
}

function cerrarModalAjustes() {
    document.getElementById('modal-ajustes-grupo').style.display = 'none';
}

// Formulario de edición (Nombre/Foto)
document.getElementById('form-editar-grupo').onsubmit = function(e) {
    e.preventDefault();
    fetch('editar_grupo.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert("¡Guardado!");
            location.reload();
        } else {
            alert("Error: " + data.error);
        }
    });
};