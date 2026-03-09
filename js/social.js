document.addEventListener('click', function (e) {
    // Detectamos si el clic fue en un botón de agregar amigo
    if (e.target && e.target.classList.contains('btn-agregar')) {
        const boton = e.target;
        const idAmigo = boton.getAttribute('data-id');

        // Evitar múltiples clics
        boton.disabled = true;
        boton.textContent = "...";

        fetch(`../../includes/añadir_amigo_ajax.php?id=${idAmigo}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' || data.status === 'info') {
                    boton.textContent = "Pendiente";
                    boton.style.backgroundColor = "#6c757d";
                    boton.classList.remove('btn-agregar');
                    boton.classList.add('btn-pendiente');
                } else {
                    alert("Error: " + data.message);
                    boton.disabled = false;
                    boton.textContent = "Añadir amigo";
                }
            })
            .catch(error => {
                console.error('Error:', error);
                boton.disabled = false;
            });
    }
});