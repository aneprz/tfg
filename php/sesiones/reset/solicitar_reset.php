<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Recuperar contraseña</title>
<link rel="stylesheet" href="../../../estilos/estilos_login.css">
</head>

<body>

<main class="login-container">
<form action="procesar_reset.php" method="POST" class="login-form">

<h2>Recuperar contraseña</h2>

<div class="input-group">
<label>Email</label>
<input type="email" name="email" required>
</div>

<button class="btn-submit">Enviar enlace</button>

<p class="form-footer">
Recibirás un enlace si el email existe
</p>

</form>
</main>

</body>
</html>