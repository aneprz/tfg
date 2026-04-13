<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../media/logoplatino.png">
    <link rel="stylesheet" href="../../../estilos/estilos_register.css">
    <title>Registro de Usuario</title>
</head>
<body>
    <form action="procesarRegister.php" method="POST">
        <div>
            <h2>Crear Cuenta</h2>
            <label for="gameTag">Nombre de Usuario: </label><br>
            <input type="text" id="gameTag" name="gameTag" required placeholder="GameTag">
        </div>

        <br>    

        <div>
            <label for="nombreApellido">Nombre y Apellido:</label><br>
            <input type="text" id="nombreApellido" name="nombreApellido" required placeholder="Nombre Apellido">
        </div>

        <br>

        <div>
            <label for="email">Correo electrónico:</label><br>
            <input type="email" id="email" name="email" required placeholder="tucorreo@gmail.com">
        </div>

        <br>

        <div>
            <label for="password">Contraseña:</label><br>
            <input type="password" id="contraseña" name="password" required placeholder="••••••••" onfocus="textoValidarContraseña()">
        </div>

        <br>

        <div>
            <label for="confirmPassword">Repetir contraseña:</label><br>
            <input type="password" id="repetirContraseña" name="confirmPassword" required placeholder="••••••••">
        </div>

        <div id="requisitosContraseña" class="my-3" style="display: none; font-size: 0.85em; text-align: left; margin-left: 20px;">
            <p class="textoContraseña" id="longitud">Mínimo 8 carácteres.</p>
            <p class="textoContraseña" id="mayuscula">Al menos una mayúscula.</p>
            <p class="textoContraseña" id="minuscula">Al menos una minúscula.</p>
            <p class="textoContraseña" id="numero">Al menos un número.</p>
            <p class="textoContraseña" id="caracterEspecial">Al menos un carácter especial.</p>
            <p class="textoContraseña" id="contraseñasRepetidas">Se repiten las contraseñas.</p>
        </div>

        <br>

        <button type="submit" id="registrarse" disabled>Registrarse</button>
        
        <p class="form-footer">
            ¿Ya tienes cuenta? <a href="../login/login.php">Inicia sesión aquí</a>
        </p>
    </form>

    <script src="validaciones/validacionesRegister.js"></script>
</body>
</html>