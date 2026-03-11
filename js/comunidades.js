document.addEventListener("DOMContentLoaded", () => {
    // --- LÓGICA DE MODAL (Solo para ver_comunidad.php) ---
    const modal = document.getElementById("modalMiembros");
    const btnVer = document.getElementById("btnVerMiembros");
    const listaDestino = document.querySelector(".lista-miembros-modal");
    const inputId = document.querySelector("input[name='id_comunidad']");

    if (modal && btnVer && inputId) {
        const idComunidad = inputId.value;
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
    
    window.onclick = (e) => { 
        if (modal && e.target == modal) modal.style.display = "none"; 
    };

    // --- LÓGICA DE BOTONES (Delegación de eventos) ---
    document.addEventListener("click", (e) => {
        
        // 1. Unirse / Abandonar Comunidad
        const btnCom = e.target.closest(".btn-accion-comunidad");
        if (btnCom) {
            e.preventDefault();
            const idCom = btnCom.getAttribute("data-id");
            const accion = btnCom.getAttribute("data-accion");
            const originalText = btnCom.innerHTML;

            // Estado de carga
            btnCom.textContent = "...";

            fetch(`gestionar_miembro.php?id_comunidad=${idCom}&accion=${accion}&ajax=1`)
                .then(res => res.text())
                .then(data => {
                    // Importante: trim() para limpiar espacios del PHP
                    if (data.trim() === "success") {
                        if (accion === "unirse") {
                            // Cambiamos a estado ABANDONAR (Rojo y letras negras)
                            btnCom.innerHTML = "Abandonar";
                            btnCom.setAttribute("data-accion", "salir");
                            btnCom.style.backgroundColor = "#ff4d4d";
                            btnCom.style.borderColor = "#cc0000";
                            btnCom.style.color = "#000000";
                            btnCom.style.fontWeight = "bold";
                        } else {
                            // Cambiamos a estado UNIRSE (Amarillo y letras negras)
                            btnCom.innerHTML = "Unirse";
                            btnCom.setAttribute("data-accion", "unirse");
                            btnCom.style.backgroundColor = "#f1c40f"; 
                            btnCom.style.borderColor = "#f1c40f";
                            btnCom.style.color = "#000000";
                            btnCom.style.fontWeight = "bold";
                        }
                    } else {
                        btnCom.innerHTML = originalText;
                        alert("Error al actualizar: " + data);
                    }
                })
                .catch(err => {
                    console.error("Error Fetch:", err);
                    btnCom.innerHTML = originalText;
                });
            return;
        }

        // 2. Agregar Amigos
        const btnAmigo = e.target.closest(".btn-agregar");
        if (btnAmigo && btnAmigo.getAttribute("data-id")) {
            e.preventDefault();
            const idAmigo = btnAmigo.getAttribute("data-id");
            const originalAmigoText = btnAmigo.textContent;
            btnAmigo.textContent = "...";

            fetch(`agregar_amigo_ajax.php?id=${idAmigo}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        btnAmigo.parentElement.innerHTML = "<span class='badge-amigo'>Amigos ✓</span>";
                    } else {
                        btnAmigo.textContent = originalAmigoText;
                    }
                })
                .catch(() => {
                    btnAmigo.textContent = originalAmigoText;
                });
        }
    });

    // Bajar scroll chat automáticamente
    const feed = document.getElementById("chat-feed");
    if (feed) {
        feed.scrollTop = feed.scrollHeight;
    }
});