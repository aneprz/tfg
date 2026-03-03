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
    <form action="procesarRegistro.php" method="POST">
        <div>
            <h2>Crear Cuenta</h2>
            <label for="GameTag">Nombre en juegos: </label><br>
            <input type="text" id="GameTag" name="GameTag" required placeholder="GameTag">
        </div>

        <br>    

        <div>
            <label for="NombreApellido">Nombre y Apellido reales:</label><br>
            <input type="text" id="NombreApellido" name="NombreApellido" required placeholder="Nombre Apellido">
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
            <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
        </div>

        <br>

        <button type="submit">Registrarse</button>

    </form>
</body>
</html>