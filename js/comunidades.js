document.addEventListener("DOMContentLoaded", () => {
    
    const modal = document.getElementById("modalMiembros");
    const btnVer = document.getElementById("btnVerMiembros");
    const listaDestino = document.querySelector(".lista-miembros-modal");
    
    if (btnVer && modal) {
        btnVer.addEventListener("click", function() {
            modal.style.display = "flex";
        });
    }

    const btnClose = document.querySelector(".close");
    if (btnClose) btnClose.onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if (e.target == modal) modal.style.display = "none"; };

    document.addEventListener("click", function(e) {
        const btn = e.target.closest(".btn-agregar, .btn-pendiente-cancelar");
        
        if (!btn || !btn.hasAttribute("data-id")) return;

        e.preventDefault();
        
        const idAmigo = btn.getAttribute("data-id");
        const esCancel = btn.classList.contains("btn-pendiente-cancelar");
        const accion = esCancel ? 'cancelar' : 'agregar';

        const contenedor = btn.parentElement;
        btn.textContent = "...";
        btn.disabled = true;

        fetch(`agregar_amigo_ajax.php?id=${idAmigo}&accion=${accion}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    if (accion === 'agregar') {
                        contenedor.innerHTML = `<button class="btn-pendiente-cancelar" data-id="${idAmigo}" style="background:#6c757d; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:12px;">Pendiente (X)</button>`;
                    } else {
                        contenedor.innerHTML = `<button class="btn-agregar" data-id="${idAmigo}" style="background:#007bff; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size:12px;">Añadir</button>`;
                    }
                }
            })
            .catch(err => {
                console.error("Error:", err);
                btn.textContent = "Error";
                btn.disabled = false;
            });
    });
    
    // ========== MANEJAR UNIRSE/ABANDONAR COMUNIDAD SIN RECARGAR ==========
document.addEventListener("click", function(e) {
    const btn = e.target.closest(".btn-accion-comunidad");
    if (!btn) return;
    
    e.preventDefault();
    
    const idComunidad = btn.getAttribute("data-id");
    const accion = btn.getAttribute("data-accion");
    
    btn.textContent = "...";
    btn.disabled = true;
    
    fetch(`gestionar_miembro.php?accion=${accion}&id_comunidad=${idComunidad}`)
        .then(response => response.text())
        .then(() => {
            // Cambiar el botón al estado contrario sin recargar
            if (accion === 'unirse') {
                btn.setAttribute("data-accion", "salir");
                btn.textContent = "Abandonar";
                btn.style.backgroundColor = "#ff4d4d";
                btn.style.color = "#000";
                btn.style.border = "2px solid #cc0000";
            } else {
                btn.setAttribute("data-accion", "unirse");
                btn.textContent = "Unirse";
                btn.style.backgroundColor = "#f1c40f";
                btn.style.color = "#000";
                btn.style.border = "2px solid #f1c40f";
            }
            btn.disabled = false;
        })
        .catch(err => {
            console.error("Error:", err);
            btn.textContent = "Error";
            btn.disabled = false;
        });
});
});
