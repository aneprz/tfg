<?php
session_start();
require '../../../db/conexiones.php';

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
$usuario=$result->fetch_assoc();

if($usuario && password_verify($pass,$usuario['password'])){

	if(!$usuario['email_verificado']){
		echo "<script>
		alert('Debes verificar tu correo antes de iniciar sesión');
		window.location.href='../login/login.php';
		</script>";
		exit();
	}

	$_SESSION['tag']=$tag;
	$_SESSION['id_usuario']=$usuario['id_usuario'];
	$_SESSION['admin']=(bool)$usuario['admin'];

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