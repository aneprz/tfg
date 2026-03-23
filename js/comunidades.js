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
});
