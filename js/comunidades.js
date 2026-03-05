document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalMiembros");
    const btnVer = document.getElementById("btnVerMiembros");
    const listaDestino = document.querySelector(".lista-miembros-modal");
    
    const inputId = document.querySelector("input[name='id_comunidad']");
    if (!inputId) return;
    const idComunidad = inputId.value;

    // Abrir Modal y cargar miembros
    if (btnVer) {
        btnVer.onclick = () => {
            modal.style.display = "flex";
            listaDestino.innerHTML = "<li>Cargando miembros...</li>";
            fetch(`obtener_miembros.php?id=${idComunidad}`)
                .then(res => res.text())
                .then(html => {
                    listaDestino.innerHTML = html;
                });
        };
    }

    // Cerrar Modal
    const btnClose = document.querySelector(".close");
    if (btnClose) btnClose.onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if (e.target == modal) modal.style.display = "none"; };

    // Lógica para Agregar Amigos (vía AJAX para que sea fluido)
    document.addEventListener("click", (e) => {
        const boton = e.target.closest(".btn-agregar");
        if (boton) {
            e.preventDefault();
            const idAmigo = boton.getAttribute("data-id");
            boton.textContent = "...";

            fetch(`agregar_amigo_ajax.php?id=${idAmigo}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        boton.parentElement.innerHTML = "<span class='badge-amigo'>Amigos ✓</span>";
                    } else {
                        boton.textContent = "Agregar";
                    }
                });
        }
    });

    // Bajar el scroll al final del chat automáticamente al entrar
    const feed = document.getElementById("chat-feed");
    if (feed) {
        feed.scrollTop = feed.scrollHeight;
    }
});