<?php
session_start();
require '../../../db/conexiones.php';
require 'email/enviar_verificacion.php';

function volverConError($mensaje){
	echo "<script>
	alert('".addslashes($mensaje)."');
	window.location.href='../register/register.php';
	</script>";
	exit();
}

$tag=trim($_POST['gameTag']??'');
$nombreApellido=trim($_POST['nombreApellido']??'');
$email=trim($_POST['email']??'');
$pass=$_POST['password']??'';

$biografia='';
$avatar='../../../media/perfil_default.jpg';

if(empty($tag)||empty($email)||empty($pass)){
	volverConError("Datos del formulario incompletos.");
}

$stmt=$conexion->prepare("SELECT id_usuario FROM Usuario WHERE gameTag=? OR email=?");
$stmt->bind_param("ss",$tag,$email);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows>0){
	$stmt->close();
	volverConError("El GameTag o el Email ya están registrados.");
}

$stmt->close();

$passHash=password_hash($pass,PASSWORD_DEFAULT);
$token=bin2hex(random_bytes(32));

$stmt=$conexion->prepare("
INSERT INTO Usuario
(gameTag,nombre_apellido,email,password,biografia,avatar,token_verificacion,email_verificado)
VALUES (?,?,?,?,?,?,?,0)
");

$stmt->bind_param("sssssss",$tag,$nombreApellido,$email,$passHash,$biografia,$avatar,$token);

if($stmt->execute()){
	enviarCorreoVerificacion($email,$token);

	$stmt->close();
	$conexion->close();

	echo "<script>
	alert('Cuenta creada. Revisa tu correo para verificarla.');
	window.location.href='../login/login.php';
	</script>";
	exit();
}else{

	$stmt->close();
	$conexion->close();
	volverConError("Error crítico al guardar en la base de datos.");

}
?>