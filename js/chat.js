let chatInterval = null;
let receptorNuevoID = null;
let idConversacionActual = null; // ← Variable GLOBAL para el grupo actual

function seleccionarContacto(idReceptor, idConv, elemento) {
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    if (elemento) elemento.classList.add('activo');
    
    document.getElementById('chat-header').style.display = 'flex';
    document.getElementById('form-mensaje').style.display = 'flex';

    const nombre = elemento.querySelector('h4').innerText;
    const foto = elemento.querySelector('img').src;
    
    // Guardamos el ID actual para usarlo en abandonarGrupo()
    idConversacionActual = idConv;
    
    const esGrupo = (!idReceptor || idReceptor === 'null' || idReceptor === "");

    document.getElementById('header-nombre').innerText = nombre;
    document.getElementById('header-avatar').src = foto;
    document.getElementById('header-estado').innerText = esGrupo ? "Toca para ver info del grupo" : "Ver perfil";
    
    document.getElementById('header-info').onclick = () => {
        if (!esGrupo) {
            window.location.href = `../user/amistades/perfilOtros.php?id=${idReceptor}`;
        } else {
            console.log("Abriendo ajustes del grupo:", idConv);
            abrirAjustesGrupo(idConv);
        }
    };

    document.getElementById('id_conversacion_activa').value = idConv;
    receptorNuevoID = idReceptor;
    iniciarBucle(idConv);
}

function abrirAjustesGrupo(idConv) {
    if (!idConv || idConv == 0) return;
    
    // Guardamos el ID actual también aquí
    idConversacionActual = idConv;
    
    // Asignar el ID al campo oculto
    const inputId = document.getElementById('ajuste_id_conv');
    if (inputId) inputId.value = idConv;
    
    // Mostrar el modal
    const modal = document.getElementById('modal-ajustes-grupo');
    if (modal) {
        modal.style.display = 'flex';
        // Cargar datos en tiempo real
        actualizarListasMiembros(idConv);
    }
}

function actualizarListasMiembros(idConv) {
    fetch(`obtener_info_grupo.php?id_conv=${idConv}`)
        .then(res => res.json())
        .then(data => {
            // Actualizar título del modal
            const titulo = document.querySelector('#modal-ajustes-grupo h3');
            if (titulo) titulo.innerText = "Ajustes de " + (data.nombre_grupo || "Grupo");
            
            // Actualizar campo de nombre
            const inputNombre = document.getElementById('edit-nombre-grupo');
            if (inputNombre) inputNombre.value = data.nombre_grupo || "";
            
            // Actualizar foto de previsualización si existe
            if (data.foto_grupo) {
                const preview = document.getElementById('preview-foto-ajuste');
                if (preview) preview.src = "../../" + data.foto_grupo;
            }
            
            // También actualizar el header del chat si este grupo está activo
            if (idConversacionActual == idConv) {
                document.getElementById('header-nombre').innerText = data.nombre_grupo || "Grupo";
                if (data.foto_grupo) {
                    document.getElementById('header-avatar').src = "../../" + data.foto_grupo;
                }
            }

            // --- LISTA DE MIEMBROS ACTUALES ---
            const listaActual = document.getElementById('lista-gestion-miembros');
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
                                 style="background:#ff4d4d; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer; font-size:12px;">
                            Eliminar
                        </button>` 
                        : (esCreador ? '<span style="color:#555; font-size:11px; margin-right:10px;">Admin</span>' : '')
                    }
                </div>`;
            }).join('');

            // --- LISTA DE AMIGOS PARA AÑADIR ---
            const listaNuevos = document.getElementById('lista-añadir-miembros');
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
                listaNuevos.innerHTML = '<div style="padding:20px; color:#555; text-align:center;">No hay más amigos para invitar</div>';
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
    
    // Asegurar que el ID está presente
    fd.set('id_conv', idConv);
    
    const btnGuardar = document.querySelector('#modal-ajustes-grupo button[type="submit"]');
    const textoOriginal = btnGuardar.innerText;
    btnGuardar.innerText = 'Guardando...';
    btnGuardar.disabled = true;

    fetch('actualizar_grupo.php', { 
        method: 'POST', 
        body: fd 
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Actualizar el nombre en el header si cambió
            const nuevoNombre = document.getElementById('edit-nombre-grupo').value;
            if (nuevoNombre) {
                document.getElementById('header-nombre').innerText = nuevoNombre;
            }
            
            // Actualizar la foto en el header si se subió una nueva
            if (data.nueva_foto && data.nueva_foto != '') {
                const nuevaRuta = '../../' + data.nueva_foto;
                document.getElementById('header-avatar').src = nuevaRuta + '?t=' + Date.now();
                document.getElementById('preview-foto-ajuste').src = nuevaRuta + '?t=' + Date.now();
            }
            
            alert("¡Grupo actualizado correctamente!");
            cerrarModalAjustes();
            // Recargar la lista de conversaciones para actualizar el nombre
            location.reload();
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error de conexión: " + error.message);
    })
    .finally(() => {
        btnGuardar.innerText = textoOriginal;
        btnGuardar.disabled = false;
    });
}

function actualizarListasMiembros(idConv) {
    const listaActual = document.getElementById('lista-gestion-miembros');
    const listaNuevos = document.getElementById('lista-añadir-miembros');
    
    // Mostrar loading
    listaActual.innerHTML = '<div style="padding:20px; text-align:center; color:#888;">Cargando...</div>';
    listaNuevos.innerHTML = '<div style="padding:20px; text-align:center; color:#888;">Cargando...</div>';
    
    // Hacer fetch y ver la respuesta en consola
    fetch(`obtener_info_grupo.php?id_conv=${idConv}`)
        .then(res => {
            console.log("Respuesta status:", res.status);
            return res.text(); // Usamos text() primero para ver qué devuelve
        })
        .then(texto => {
            console.log("Respuesta completa:", texto);
            // Intentar parsear como JSON
            try {
                const data = JSON.parse(texto);
                console.log("Datos parseados:", data);
                
                // Si hay error en la respuesta
                if (data.error) {
                    listaActual.innerHTML = `<div style="padding:20px; text-align:center; color:#ff6666;">Error: ${data.error}</div>`;
                    listaNuevos.innerHTML = '<div style="padding:20px; text-align:center; color:#666;">No se pudieron cargar amigos</div>';
                    return;
                }
                
                // Actualizar título y nombre
                if (data.nombre_grupo) {
                    const titulo = document.querySelector('#modal-ajustes-grupo h3');
                    if (titulo) titulo.innerText = "Ajustes de " + data.nombre_grupo;
                    document.getElementById('edit-nombre-grupo').value = data.nombre_grupo;
                }
                
                // Actualizar foto
                if (data.foto_grupo) {
                    const preview = document.getElementById('preview-foto-ajuste');
                    if (preview) preview.src = "../../" + data.foto_grupo + "?t=" + Date.now();
                }
                
                // Mostrar MIEMBROS
                if (data.miembros && data.miembros.length > 0) {
                    listaActual.innerHTML = data.miembros.map(m => {
                        const esCreador = m.es_creador;
                        const puedoEliminar = data.soy_creador && !esCreador;
                        
                        return `
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222; background:#1a1a1a; margin-bottom:5px; border-radius:8px;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <img src="${m.foto_perfil}" style="width:35px; height:35px; border-radius:50%; object-fit:cover;" onerror="this.src='../../img/avatares/default.png'">
                                <span style="color:white;">${m.gameTag} ${esCreador ? '👑' : ''}</span>
                            </div>
                            ${puedoEliminar ? 
                                `<button onclick="gestionarMiembro(${idConv}, ${m.id_usuario}, 'quitar')" 
                                    style="background:#ff4d4d; color:white; border:none; padding:6px 12px; border-radius:6px; cursor:pointer;">
                                    Eliminar
                                </button>` 
                                : (esCreador ? '<span style="color:#888;">Admin</span>' : '')
                            }
                        </div>`;
                    }).join('');
                } else {
                    listaActual.innerHTML = '<div style="padding:20px; text-align:center; color:#666;">No hay miembros en este grupo</div>';
                }
                
                // Mostrar AMIGOS para añadir
                if (data.amigos_fuera && data.amigos_fuera.length > 0) {
                    listaNuevos.innerHTML = data.amigos_fuera.map(a => `
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222; background:#1a1a1a; margin-bottom:5px; border-radius:8px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="${a.foto_perfil}" style="width:30px; height:30px; border-radius:50%;" onerror="this.src='../../img/avatares/default.png'">
                            <span style="color:#ccc;">${a.gameTag}</span>
                        </div>
                        <button onclick="gestionarMiembro(${idConv}, ${a.id_usuario}, 'añadir')" 
                                style="background:#f0c330; color:black; border:none; padding:6px 12px; border-radius:6px; cursor:pointer;">
                            + Añadir
                        </button>
                    </div>`).join('');
                } else {
                    listaNuevos.innerHTML = '<div style="padding:20px; text-align:center; color:#666;">No hay amigos disponibles para invitar</div>';
                }
                
            } catch(e) {
                console.error("Error al parsear JSON:", e);
                listaActual.innerHTML = '<div style="padding:20px; text-align:center; color:#ff6666;">Error: El servidor no devolvió JSON válido<br><small>Revisa la consola (F12)</small></div>';
                listaNuevos.innerHTML = '<div style="padding:20px; text-align:center; color:#666;">No se pudieron cargar amigos</div>';
            }
        })
        .catch(err => {
            console.error("Error en fetch:", err);
            listaActual.innerHTML = '<div style="padding:20px; text-align:center; color:#ff6666;">Error de conexión: ' + err.message + '</div>';
            listaNuevos.innerHTML = '<div style="padding:20px; text-align:center; color:#666;">No se pudieron cargar amigos</div>';
        });
}

function abandonarGrupo() {
    // Verificar que tenemos un grupo seleccionado
    if (!idConversacionActual || idConversacionActual === 0) {
        alert("No hay ningún grupo seleccionado.");
        return;
    }

    if (!confirm('¿Estás seguro de que deseas abandonar este grupo?\nPerderás acceso a todos los mensajes.')) return;

    const datos = new FormData();
    datos.append('id_conv', idConversacionActual);
    datos.append('accion', 'abandonar');

    fetch('gestionar_miembro.php', {
        method: 'POST',
        body: datos
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Has salido del grupo correctamente.");
            location.reload(); // Recargar para que desaparezca de la lista
        } else {
            alert("Error: " + (data.error || "No se pudo abandonar el grupo"));
        }
    })
    .catch(error => {
        console.error("Error en la petición:", error);
        alert("Hubo un fallo en la conexión con el servidor.");
    });
}

function gestionarMiembro(idConv, idUser, accion) {
    const fd = new FormData();
    fd.append('id_conv', idConv);
    fd.append('id_user', idUser);
    fd.append('accion', accion);
    
    fetch('gestionar_miembro.php', { 
        method: 'POST', 
        body: fd 
    })
    .then(res => res.json())
    .then(data => { 
        if(data.success) {
            actualizarListasMiembros(idConv);
        } else {
            alert("Error: " + (data.error || "No se pudo realizar la acción"));
        }
    })
    .catch(err => console.error("Error:", err));
}

function cerrarModalAjustes() { 
    document.getElementById('modal-ajustes-grupo').style.display = 'none'; 
}

// Bucle y envío de mensajes
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
            .catch(err => console.error("Error cargando mensajes:", err));
    };
    refrescar(); 
    chatInterval = setInterval(refrescar, 2500);
}

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
        .catch(err => console.error("Error enviando mensaje:", err));
});

function abrirModalGrupo() {
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

function crearGrupoProcesar() {
    const nombreInput = document.getElementById('nombre-grupo');
    const nombre = nombreInput ? nombreInput.value.trim() : "";
    
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
    .catch(err => console.error("Error:", err));
}

// Vista previa de la foto al seleccionar un archivo
document.addEventListener('DOMContentLoaded', function() {
    const inputFoto = document.getElementById('input-foto-ajuste');
    if (inputFoto) {
        inputFoto.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('preview-foto-ajuste');
                    if (preview) preview.src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
});