<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../estilos/estilos_login.css">
    <link rel="icon" href="../../../media/logoplatinoSinFondo.png">
    <title>Iniciar Sesión</title>
</head>
<body>

    <main class="login-container">
        <form action="procesarLogin.php" method="POST" class="login-form">
            <h2>Iniciar Sesión</h2>

            <div class="input-group">
                <label for="gameTag">Nombre en juegos: </label>
                <input type="text" id="gameTag" name="gameTag" required placeholder="Tu GameTag">
            </div>

            <div class="input-group">
                <label for="password">Contraseña: </label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-submit">Entrar al Sistema</button>

            <p class="form-footer">
                ¿No tienes cuenta? <a href="../register/register.php">Regístrate aquí</a>
            </p>
        </form>
    </main>

</body>
</html>