<?php

date_default_timezone_set('Europe/Madrid');

require '../../../db/conexiones.php';
require '../register/email/enviar_verificacion.php';

$email = trim($_POST['email'] ?? '');

if(!$email){
    header("Location: ../login/login.php");
    exit();
}

$stmt = $conexion->prepare("SELECT id_usuario FROM Usuario WHERE email=? LIMIT 1");
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){

    $Usuario = $result->fetch_assoc();

    $token = bin2hex(random_bytes(32));

    $stmt = $conexion->prepare("
    UPDATE Usuario
    SET token_reset_password=?, 
        token_reset_expira = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
    WHERE id_usuario=?
    ");

    $stmt->bind_param("si",$token,$Usuario['id_usuario']);
    $stmt->execute();

    $link = "http://localhost:3000/php/sesiones/reset/cambiar_password.php?token=".$token;

    enviarCorreoReset($email,$link);
}

echo "<script>
alert('Si el correo existe recibirás un enlace');
window.location.href='../login/login.php';
</script>";