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
            window.location.href = `../user/amistades/perfilOtros.php?id=${idReceptor}`;
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
    console.log("Cargando miembros para conv:", idConv); // Para debugear en F12
    
    fetch(`obtener_info_grupo.php?id_conv=${idConv}`)
        .then(res => res.json())
        .then(data => {
            console.log("Datos recibidos:", data); // Mira esto en la consola F12
            
            const listaActual = document.getElementById('lista-gestion-miembros');
            const listaNuevos = document.getElementById('lista-añadir-miembros');
            
            // 1. MIEMBROS ACTUALES
            listaActual.innerHTML = data.miembros.map(m => {
                // Si data.soy_creador es false, los botones no salen. 
                // Asegúrate de que en la DB tú seas el 'id_usuario_creador'
                const esCreadorDeLaFila = m.es_creador === true || m.es_creador === "true" || m.es_creador == 1;
                const puedoEliminar = data.soy_creador && !esCreadorDeLaFila;

                return `
                <div style="display:flex; align-items:center; justify-content:space-between; padding:12px; border-bottom:1px solid #222;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="color:white; font-size:14px;">${m.gameTag}</span>
                        ${esCreadorDeLaFila ? '<span title="Creador" style="font-size:16px;">👑</span>' : ''}
                    </div>
                    
                    ${puedoEliminar ? `
                        <button type="button" 
                                onclick="gestionarMiembro(${idConv}, ${m.id_usuario}, 'quitar')" 
                                style="background:#ff4d4d; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:11px; font-weight:bold;">
                            Eliminar
                        </button>
                    ` : ''}
                </div>`;
            }).join('');

            // 2. AÑADIR NUEVOS (AMIGOS)
            if (data.amigos_fuera && data.amigos_fuera.length > 0) {
                listaNuevos.innerHTML = data.amigos_fuera.map(a => `
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222;">
                        <span style="color:#ccc; font-size:13px;">${a.gameTag}</span>
                        <button type="button" onclick="gestionarMiembro(${idConv}, ${a.id_usuario}, 'añadir')" 
                                style="background:#f0c330; color:black; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:11px; font-weight:bold;">
                            + Añadir
                        </button>
                    </div>
                `).join('');
            } else {
                listaNuevos.innerHTML = '<div style="padding:15px; color:#555; font-size:12px; text-align:center;">No hay amigos para invitar</div>';
            }
        });
}

function abandonarGrupo() {
    const idConv = document.getElementById('ajuste_id_conv').value;
    if (!idConv || !confirm("¿Estás seguro de que quieres abandonar este grupo?")) return;

    const fd = new FormData();
    fd.append('id_conv', idConv);
    fd.append('accion', 'abandonar');

    fetch('gestionar_miembro.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Has salido del grupo");
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        });
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

function guardarAjustes() {
    const form = document.getElementById('form-ajustes-grupo'); // Asegúrate que el <form> tenga este ID
    const formData = new FormData(form); 
    
    // Añadimos manualmente el ID de la conversación si no está en el form
    const idConv = document.getElementById('ajuste_id_conv').value;
    formData.append('id_conv', idConv);

    fetch('actualizar_grupo.php', {
        method: 'POST',
        body: formData // Enviamos el objeto FormData directamente
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            location.reload(); // Recargamos para ver la nueva foto en la lista
        } else {
            alert("Error al guardar: " + data.error);
        }
    })
    .catch(err => console.error("Error en el fetch:", err));
}

// Detectar cuando el usuario elige una foto
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'input_foto_grupo') {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                // Cambiamos la imagen del círculo en el modal
                document.getElementById('img_previsualizacion').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});