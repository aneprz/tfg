<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../estilos/estilos_login.css">
    <link rel="icon" href="../../../media/logoplatino.png">
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

            <div class="input-group">
                <label style="display:flex; align-items:center; gap:10px; font-weight:normal;">
                    <input type="checkbox" name="remember" value="1" style="width:auto; margin:0;">
                    Recordarme en este dispositivo
                </label>
            </div>

            <button type="submit" class="btn-submit">Entrar al Sistema</button>

            <p class="form-footer">
                ¿No tienes cuenta? <a href="../register/register.php">Regístrate aquí</a>
            </p>
            <p class="form-footer">
                <a href="../reset/solicitar_reset.php">¿Olvidaste tu contraseña?</a>
            </p>
        </form>
    </main>

</body>
</html>
