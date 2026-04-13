
<?php
require '../../../db/conexiones.php';

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirmPassword'] ?? '';

if(!$token || !$password || !$confirm){
    die("Datos incompletos");
}

if($password !== $confirm){
    echo "<script>
    alert('Las contraseñas no coinciden');
    window.history.back();
    </script>";
    exit();
}

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

$Usuario = $result->fetch_assoc();

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conexion->prepare("
UPDATE Usuario
SET password=?, 
token_reset_password=NULL,
token_reset_expira=NULL
WHERE id_usuario=?
");

$stmt->bind_param("si",$hash,$Usuario['id_usuario']);
$stmt->execute();

echo "<script>
alert('Contraseña cambiada correctamente');
window.location.href='../login/login.php';
</script>";
