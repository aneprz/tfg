let chatInterval = null;
let receptorNuevoID = null;

function seleccionarContacto(idReceptor, idConv, elemento) {
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    if (elemento) elemento.classList.add('activo');
    
    document.getElementById('chat-header').style.display = 'flex';
    document.getElementById('form-mensaje').style.display = 'flex';

    const nombre = elemento.querySelector('h4').innerText;
    const foto = elemento.querySelector('img').src;
    
    // Si idReceptor es null o "null", es un grupo
    const esGrupo = (!idReceptor || idReceptor === 'null' || idReceptor === "");

    document.getElementById('header-nombre').innerText = nombre;
    document.getElementById('header-avatar').src = foto;
    document.getElementById('header-estado').innerText = esGrupo ? "Toca para ver info del grupo" : "Ver perfil";
    
    document.getElementById('header-info').onclick = () => {
        if (!esGrupo) {
            window.location.href = `../user/amistades/perfilOtros.php?id=${idReceptor}`;
        } else {
            console.log("Intentando abrir ajustes del grupo:", idConv);
            abrirAjustesGrupo(idConv);
        }
    };

    document.getElementById('id_conversacion_activa').value = idConv;
    receptorNuevoID = idReceptor;
    iniciarBucle(idConv);
}

function abrirAjustesGrupo(idConv) {
    if (!idConv || idConv == 0) return;
    
    // 1. Asignar el ID al campo oculto
    const inputId = document.getElementById('ajuste_id_conv');
    if (inputId) inputId.value = idConv;
    
    // 2. Mostrar el modal
    const modal = document.getElementById('modal-ajustes-grupo');
    if (modal) {
        modal.style.display = 'flex';
        // 3. Cargar datos en tiempo real (nombre, miembros, etc)
        actualizarListasMiembros(idConv);
    }
}

function actualizarListasMiembros(idConv) {
    fetch(`obtener_info_grupo.php?id_conv=${idConv}`)
        .then(res => res.json())
        .then(data => {
            // 1. Actualizar textos estáticos del modal
            const titulo = document.querySelector('#modal-ajustes-grupo h3');
            if (titulo) titulo.innerText = "Ajustes de " + (data.nombre_grupo || "Grupo");
            
            const inputNombre = document.getElementById('edit-nombre-grupo');
            if (inputNombre) inputNombre.value = data.nombre_grupo || "";

            // 2. Referencias a los contenedores de listas
            const listaActual = document.getElementById('lista-gestion-miembros');
            const listaNuevos = document.getElementById('lista-añadir-miembros');

            // --- LISTA DE MIEMBROS ACTUALES ---
            listaActual.innerHTML = data.miembros.map(m => {
                const esCreador = (parseInt(m.id_usuario) === parseInt(data.id_creador));
                const puedoEliminar = data.soy_creador && !esCreador;
                
                return `
                <div class="miembro-item" style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222; background: #1a1a1a; margin-bottom:5px; border-radius:8px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="${m.foto_perfil || '../../img/avatares/default.png'}" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                        <span style="color:white; font-weight:500;">${m.gameTag} ${esCreador ? '<span title="Creador" style="margin-left:5px;">👑</span>' : ''}</span>
                    </div>
                    ${puedoEliminar ? 
                        `<button type="button" onclick="gestionarMiembro(${idConv}, ${m.id_usuario}, 'quitar')" 
                                 style="background:#ff4d4d; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:12px; transition:0.3s;">
                            Eliminar
                        </button>` 
                        : (esCreador ? '<span style="color:#555; font-size:11px; font-style:italic; margin-right:10px;">Admin</span>' : '')
                    }
                </div>`;
            }).join('');

            // --- LISTA DE AMIGOS PARA AÑADIR ---
            if (data.amigos_fuera && data.amigos_fuera.length > 0) {
                listaNuevos.innerHTML = data.amigos_fuera.map(a => `
                <div class="amigo-item" style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222; background: #1a1a1a; margin-bottom:5px; border-radius:8px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="${a.foto_perfil || '../../img/avatares/default.png'}" style="width:30px; height:30px; border-radius:50%;">
                        <span style="color:#ccc;">${a.gameTag}</span>
                    </div>
                    <button type="button" onclick="gestionarMiembro(${idConv}, ${a.id_usuario}, 'añadir')" 
                            style="background:#f0c330; color:black; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:12px; font-weight:bold;">
                        + Añadir
                    </button>
                </div>`).join('');
            } else {
                listaNuevos.innerHTML = '<div style="padding:20px; color:#555; text-align:center; font-size:13px;">No hay más amigos para invitar</div>';
            }
        })
        .catch(err => {
            console.error("Error cargando miembros:", err);
            const listaActual = document.getElementById('lista-gestion-miembros');
            if(listaActual) listaActual.innerHTML = '<p style="color:red; text-align:center;">Error al cargar la lista</p>';
        });
}

function guardarAjustes() {
    const form = document.getElementById('form-ajustes-grupo');
    const fd = new FormData(form);
    const idConv = document.getElementById('ajuste_id_conv').value;
    
    fd.set('id_conv', idConv);
    fd.set('nombre_grupo', document.getElementById('edit-nombre-grupo').value);

    fetch('actualizar_grupo.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert("¡Actualizado!");
            location.reload();
        } else {
            alert("Error: " + data.error);
        }
    });
}

function abandonarGrupo() {
    // 1. Obtenemos el ID del grupo del campo oculto del modal
    const idConv = document.getElementById('ajuste_id_conv').value;
    
    console.log("Intentando abandonar el grupo ID:", idConv);

    if (!idConv || idConv == 0) {
        alert("Error: No se ha detectado el ID del grupo.");
        return;
    }

    // 2. Confirmación de seguridad
    if (!confirm("¿Estás seguro de que quieres abandonar este grupo? Esta acción no se puede deshacer.")) {
        return;
    }

    // 3. Preparamos los datos para enviar al PHP
    const fd = new FormData();
    fd.append('id_conv', idConv);
    fd.append('accion', 'abandonar'); // Esta es la clave que lee tu gestionar_miembro.php

    // 4. Petición al servidor
    fetch('gestionar_miembro.php', {
        method: 'POST',
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Has salido del grupo correctamente.");
            // Redirigir a la bandeja para limpiar la vista del chat actual
            window.location.href = 'bandeja.php';
        } else {
            alert("Error al intentar salir: " + (data.error || "Error desconocido"));
        }
    })
    .catch(err => {
        console.error("Error en la petición:", err);
        alert("Hubo un error de conexión con el servidor.");
    });
}

function gestionarMiembro(idConv, idUser, accion) {
    const fd = new FormData();
    fd.append('id_conv', idConv);
    fd.append('id_user', idUser);
    fd.append('accion', accion);
    fetch('gestionar_miembro.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => { if(data.success) actualizarListasMiembros(idConv); });
}

function cerrarModalAjustes() { document.getElementById('modal-ajustes-grupo').style.display = 'none'; }

// Bucle y envío de mensajes (Tus funciones originales)
function iniciarBucle(id) {
    if (chatInterval) clearInterval(chatInterval);
    const refrescar = () => {
        fetch(`obtener_mensajes.php?id_conversacion=${id}`)
            .then(res => res.text())
            .then(html => {
                const box = document.getElementById('mensajes-scroll');
                if (box.innerHTML !== html) { box.innerHTML = html; box.scrollTop = box.scrollHeight; }
            });
    };
    refrescar(); chatInterval = setInterval(refrescar, 2500);
}

document.getElementById('form-mensaje').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('input-texto');
    const idConv = document.getElementById('id_conversacion_activa').value;
    if (!input.value.trim()) return;
    let p = new URLSearchParams();
    p.append('mensaje', input.value);
    if (idConv && idConv !== "0") p.append('id_conversacion', idConv);
    else p.append('id_receptor', receptorNuevoID);
    fetch('enviar_mensaje.php', { method: 'POST', body: p }).then(() => { input.value = ''; });
});

function abrirModalGrupo() {
    console.log("Abriendo modal de creación de grupo...");
    const modal = document.getElementById('modal-grupo');
    if (modal) {
        modal.style.display = 'flex';
    } else {
        console.error("No se encontró el elemento con ID 'modal-grupo'");
    }
}

function cerrarModalGrupo() {
    const modal = document.getElementById('modal-grupo');
    if (modal) {
        modal.style.display = 'none';
    }
}

/**
 * PROCESAR CREACIÓN DE GRUPO
 */
function crearGrupoProcesar() {
    const nombreInput = document.getElementById('nombre-grupo');
    const nombre = nombreInput ? nombreInput.value.trim() : "";
    
    // Capturamos los ids de los amigos seleccionados
    const seleccionados = Array.from(document.querySelectorAll('.check-amigo:checked'))
                               .map(cb => cb.value);

    if (!nombre) return alert("Por favor, escribe un nombre para el grupo.");
    if (seleccionados.length === 0) return alert("Selecciona al menos a un amigo.");

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
            alert("¡Grupo creado con éxito!");
            location.reload(); 
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error("Error grave en la petición:", err));
}

