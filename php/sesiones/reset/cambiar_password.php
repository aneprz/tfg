<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../../db/conexiones.php';

$token = $_GET['token'] ?? '';

$stmt = $conexion->prepare("
SELECT id_usuario
FROM Usuario
WHERE token_reset_password = ? 
AND token_reset_expira > NOW()
");

$stmt->bind_param("s",$token);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Token inválido o expirado");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../../../estilos/estilos_login.css">
<link rel="icon" href="../../../media/logoplatino.png">
<title>Cambiar contraseña</title>
</head>

<body>

<main class="login-container">

<form action="guardar_password.php" method="POST" class="login-form">

<h2>Cambiar contraseña</h2>

<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

<div class="input-group">
<label>Nueva contraseña</label>
<input 
type="password" 
name="password" 
required 
placeholder="••••••••">
</div>

<div class="input-group">
<label>Confirmar contraseña</label>
<input 
type="password" 
name="confirmPassword" 
required 
placeholder="••••••••">
</div>

<button type="submit" class="btn-submit">
Cambiar contraseña
</button>

<p class="form-footer">
<a href="../login/login.php">Volver al login</a>
</p>

</form>

</main>

</body>
</html>