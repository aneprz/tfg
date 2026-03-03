<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../../media/logoplatinoSinFondo.png">
    <link rel="stylesheet" href="../../../estilos/estilos_register.css">
    <title>Registro de usuario</title>
</head>
<body>
    <form action="procesarRegister.php" method="POST">
        <div>
            <h2>Crear Cuenta</h2>
            <label for="GameTag">Nombre en juegos: </label><br>
            <input type="text" id="gameTag" name="gameTag" required placeholder="GameTag">
        </div>

        <br>    

        <div>
            <label for="NombreApellido">Nombre y Apellido reales:</label><br>
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
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>

        <br>

        <div>
            <label for="confirm_password">Repetir contraseña:</label><br>
            <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="••••••••">
        </div>

        <br>

        <button type="submit">Registrarse</button>
        <p class="form-footer">
        ¿Ya tienes cuenta? <a href="../login/login.php">Inicia sesión aquí</a>
        </p>
    </form>
</body>
</html>