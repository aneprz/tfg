const contraseña = document.getElementById("contraseña");
const repetirContraseña = document.getElementById("repetirContraseña");
const botonRegistrarse = document.getElementById("registrarse");

const checkIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 16.6l7.05-7.05l-1.4-1.4l-5.65 5.65l-2.85-2.85l-1.4 1.4zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>`;
const circleIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>`;

function textoValidarContraseña() {
    document.getElementById("requisitosContraseña").style.display = "block";
}

function validarTodo() {
    const val = contraseña.value;
    
    const reglas = {
        longitud: val.length >= 8,
        mayuscula: /[A-Z]/.test(val),
        minuscula: /[a-z]/.test(val),
        numero: /[0-9]/.test(val),
        caracterEspecial: /[._,:;+<>#@¿?!¡=~|º{}\[\]()¨\/-]/.test(val),
        contraseñasRepetidas: val === repetirContraseña.value && val !== ""
    };

    for (const [id, valida] of Object.entries(reglas)) {
        const elemento = document.getElementById(id);
        if (valida) {
            elemento.style.color = "green";
            elemento.innerHTML = checkIcon + " " + elemento.innerText;
        } else {
            elemento.style.color = ""; 
            elemento.innerHTML = circleIcon + " " + elemento.innerText;
        }
    }

    botonRegistrarse.disabled = !Object.values(reglas).every(v => v === true);
}

contraseña.addEventListener("input", validarTodo);
repetirContraseña.addEventListener("input", validarTodo);

function configurarOjo(idInput, idBoton, idIcono) {
    const inputPass = document.getElementById(idInput);
    const btnPass = document.getElementById(idBoton);
    const icono = document.getElementById(idIcono);

    btnPass.addEventListener('click', () => {
        const esPass = inputPass.type === "password";
        inputPass.type = esPass ? "text" : "password";
    });
}

document.addEventListener("DOMContentLoaded", () => {
    configurarOjo('contraseña', 'btnVerPass', 'iconoPass');
    configurarOjo('repetirContraseña', 'btnVerPass2', 'iconoPass2');
});