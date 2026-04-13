<?php
session_start();
require '../../../db/conexiones.php';
require_once __DIR__ . '/../remember_me.php';

$tag=$_POST['gameTag']??'';
$pass=$_POST['password']??'';

$stmt=$conexion->prepare("
SELECT id_usuario,password,admin,email_verificado
FROM Usuario
WHERE gameTag=?
");

$stmt->bind_param("s",$tag);
$stmt->execute();
$result=$stmt->get_result();
$Usuario=$result->fetch_assoc();

if($Usuario && password_verify($pass,$Usuario['password'])){

	if(!$Usuario['email_verificado']){
		echo "<script>
		alert('Debes verificar tu correo antes de iniciar sesión');
		window.location.href='../login/login.php';
		</script>";
		exit();
	}

	session_regenerate_id(true);
	$_SESSION['tag']=$tag;
	$_SESSION['id_usuario']=$Usuario['id_usuario'];
	$_SESSION['admin']=(bool)$Usuario['admin'];

	if(!empty($_POST['remember'])){
		salsabox_issue_remember_token($conexion,(int)$Usuario['id_usuario']);
	}else{
		// Si el Usuario no marca "recordarme", limpia cookie antigua si existía.
		salsabox_forget_current_remember_token($conexion);
		salsabox_clear_remember_cookie();
	}

	header("Location: ../../../index.php");
	exit();

}else{

	echo "<script>
	alert('Datos incorrectos');
	window.location.href='../login/login.php';
	</script>";
	exit();

}
?>
