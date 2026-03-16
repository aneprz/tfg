<?php

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';

    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "sendoa.perez06@gmail.com";
    $mail->Password = "tvpf nhqy qqaq fokp";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom("sendoa.perez06@gmail.com", "Test SalsaBox");
    $mail->addAddress("sendoa.perez06@gmail.com");

    $mail->Subject = "Prueba correo SalsaBox";
    $mail->Body = "Si ves este correo, PHPMailer funciona.";

    $mail->send();

    echo "Correo enviado correctamente";

} catch (Exception $e) {

    echo "Error: {$mail->ErrorInfo}";
}