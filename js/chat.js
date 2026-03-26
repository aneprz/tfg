let chatInterval = null;
let receptorNuevoID = null;

// Variable global para identificarme (puedes definirla en el HTML con PHP)
const MI_ID_REAL = typeof MI_ID_USUARIO !== 'undefined' ? MI_ID_USUARIO : 0;

/**
 * SELECCIONAR CONTACTO
 */
function seleccionarContacto(idReceptor, idConv, elemento) {
    document.querySelectorAll('.chat-item').forEach(i => i.classList.remove('activo'));
    elemento.classList.add('activo');
    
    document.getElementById('chat-header').style.display = 'flex';
    document.getElementById('form-mensaje').style.display = 'flex';

    const nombre = elemento.querySelector('h4').innerText;
    const foto = elemento.querySelector('img').src;
    const esGrupo = (idReceptor === null || idReceptor === 'null' || idReceptor === "");

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

    const btnAjustes = document.getElementById('btn-ajustes-grupo');
    if (esGrupo) {
        btnAjustes.style.display = 'block';
        btnAjustes.onclick = (e) => {
            e.stopPropagation();
            abrirAjustesGrupo(idConv);
        };
    } else {
        btnAjustes.style.display = 'none';
    }

    document.getElementById('id_conversacion_activa').value = idConv;
    receptorNuevoID = idReceptor;
    iniciarBucle(idConv);
}

/**
 * BUCLE DE MENSAJES
 */
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
        if (data.nueva_id_conversacion) location.reload();
    });
});

/**
 * GESTIÓN DE GRUPOS (CREACIÓN)
 * Esta es la función que te faltaba y por eso daba error
 */
function abrirModalGrupo() { document.getElementById('modal-grupo').style.display = 'flex'; }
function cerrarModalGrupo() { document.getElementById('modal-grupo').style.display = 'none'; }

function crearGrupoProcesar() {
    const nombreInput = document.getElementById('nombre-grupo');
    const nombre = nombreInput.value.trim();
    const seleccionados = Array.from(document.querySelectorAll('.check-amigo:checked')).map(cb => cb.value);

    if (!nombre) return alert("Ponle un nombre al grupo");
    if (seleccionados.length < 1) return alert("Selecciona al menos a un amigo");

    const fd = new FormData();
    fd.append('nombre', nombre);
    fd.append('usuarios', JSON.stringify(seleccionados));

    // Dentro de crearGrupoProcesar...
    fetch('crear_grupo.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                // SI HAY UN ERROR DE SQL, LO VERÁS AQUÍ
                alert("Atención: " + data.error);
            }
    })
    .catch(err => console.error("Error grave:", err));
}

/**
 * AJUSTES DE GRUPO
 */
function abrirAjustesGrupo(idConv) {
    document.getElementById('ajuste_id_conv').value = idConv;
    document.getElementById('modal-ajustes-grupo').style.display = 'flex';
    actualizarListasMiembros(idConv);
}

function actualizarListasMiembros(idConv) {
    fetch(`obtener_info_grupo.php?id_conv=${idConv}`)
        .then(res => res.json())
        .then(data => {
            const listaActual = document.getElementById('lista-gestion-miembros');
            const listaNuevos = document.getElementById('lista-añadir-miembros');

            // 1. Miembros actuales
            listaActual.innerHTML = data.miembros.map(m => {
                const esCreador = (m.id_usuario == data.id_creador); // Comparación con el ID real de la DB
                const puedoEliminar = data.soy_creador && !esCreador;
                return `
                <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="${m.foto_perfil}" style="width:35px; height:35px; border-radius:50%; object-fit:cover;">
                        <span style="color:white;">${m.gameTag}</span>
                        ${esCreador ? '<span>👑</span>' : ''}
                    </div>
                    ${puedoEliminar ? `<button type="button" onclick="gestionarMiembro(${idConv}, ${m.id_usuario}, 'quitar')" style="background:#ff4d4d; color:white; border:none; padding:5px; border-radius:5px; cursor:pointer;">Eliminar</button>` : ''}
                </div>`;
            }).join('');

            // 2. Amigos fuera
            listaNuevos.innerHTML = data.amigos_fuera.length > 0 
                ? data.amigos_fuera.map(a => `
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:10px; border-bottom:1px solid #222;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="${a.foto_perfil}" style="width:30px; height:30px; border-radius:50%;">
                            <span style="color:#ccc;">${a.gameTag}</span>
                        </div>
                        <button type="button" onclick="gestionarMiembro(${idConv}, ${a.id_usuario}, 'añadir')" style="background:#f0c330; border:none; padding:5px; border-radius:5px; cursor:pointer;">+ Añadir</button>
                    </div>`).join('')
                : '<div style="padding:10px; color:#555; text-align:center;">No hay amigos para invitar</div>';
        })
        .catch(err => console.error("Error al cargar miembros:", err));
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

function abandonarGrupo() {
    const idConv = document.getElementById('ajuste_id_conv').value;
    if (!idConv || !confirm("¿Estás seguro de que quieres salir del grupo?")) return;

    const fd = new FormData();
    fd.append('id_conv', idConv);
    fd.append('accion', 'abandonar');

    fetch('gestionar_miembro.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
        if(data.success) location.reload();
        else alert(data.error);
    });
}

function guardarAjustes() {
    const form = document.getElementById('form-ajustes-grupo');
    const formData = new FormData(form);
    const idConv = document.getElementById('ajuste_id_conv').value;
    formData.append('id_conv', idConv);

    fetch('actualizar_grupo.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) location.reload();
        else alert("Error: " + data.error);
    });
}

// Previsualización de imagen
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'input-foto-ajuste') {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('preview-foto-ajuste');
                if (preview) preview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
});

function cerrarModalAjustes() {
    document.getElementById('modal-ajustes-grupo').style.display = 'none';
}

/**
 * ESTA ES LA FUNCIÓN QUE TE FALTA EN EL JS
 */
function crearGrupoProcesar() {
    const nombreInput = document.getElementById('nombre-grupo');
    const nombre = nombreInput.value.trim();
    
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
            location.reload(); // Recarga para ver el nuevo grupo en la lista
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error("Error:", err));
}