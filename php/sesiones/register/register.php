<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../media/logoplatino.png">
    <link rel="stylesheet" href="../../../estilos/estilos_register.css">
    <title>Registro de Usuario</title>
</head>
<body>
    <form action="procesarRegister.php" method="POST" class="registerForm" autocomplete="on">
        <h2>Crear Cuenta</h2>

        <div class="formGroup">
            <label for="gameTag">Nombre de Usuario</label>
            <input type="text" id="gameTag" name="gameTag" required placeholder="GameTag" autocomplete="username">
        </div>

        <div class="formGroup">
            <label for="nombreApellido">Nombre y Apellido</label>
            <input type="text" id="nombreApellido" name="nombreApellido" required placeholder="Nombre Apellido" autocomplete="name">
        </div>

        <div class="formGroup">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required placeholder="tucorreo@gmail.com" autocomplete="email">
        </div>

        <div class="formGroup">
            <label for="contraseña">Contraseña</label>
            <input type="password" id="contraseña" name="password" required placeholder="••••••••" onfocus="textoValidarContraseña()" autocomplete="new-password">
        </div>

        <div class="formGroup">
            <label for="repetirContraseña">Repetir contraseña</label>
            <input type="password" id="repetirContraseña" name="confirmPassword" required placeholder="••••••••" autocomplete="new-password">
        </div>

        <div id="requisitosContraseña" class="passwordRules" style="display: none;" aria-live="polite">
            <div class="passwordRulesTitle">Requisitos de contraseña</div>
            <p class="textoContraseña" id="longitud">Mínimo 8 carácteres.</p>
            <p class="textoContraseña" id="mayuscula">Al menos una mayúscula.</p>
            <p class="textoContraseña" id="minuscula">Al menos una minúscula.</p>
            <p class="textoContraseña" id="numero">Al menos un número.</p>
            <p class="textoContraseña" id="caracterEspecial">Al menos un carácter especial.</p>
            <p class="textoContraseña" id="contraseñasRepetidas">Se repiten las contraseñas.</p>
        </div>

        <button type="submit" id="registrarse" disabled>Registrarse</button>
        
        <p class="form-footer">
            ¿Ya tienes cuenta? <a href="../login/login.php">Inicia sesión aquí</a>
        </p>
    </form>

    <script src="validaciones/validacionesRegister.js"></script>
</body>
</html>
