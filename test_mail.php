<?php

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {

    if (file_exists(__DIR__ . '/API/credenciales.php')) {
        require __DIR__ . '/API/credenciales.php';
    }

    $gmailEmail = $gmail_email ?? getenv('GMAIL_EMAIL');
    $gmailPassword = $gmail_password ?? getenv('GMAIL_APP_PASSWORD');

    if (!$gmailEmail || !$gmailPassword) {
        throw new Exception("Faltan credenciales: define \$gmail_email/\$gmail_password en API/credenciales.php o las env vars GMAIL_EMAIL y GMAIL_APP_PASSWORD.");
    }

    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = $gmailEmail;
    $mail->Password = $gmailPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom($gmailEmail, "Test SalsaBox");
    $mail->addAddress($gmailEmail);

    $mail->Subject = "Prueba correo SalsaBox";
    $mail->Body = "Si ves este correo, PHPMailer funciona.";

    $mail->send();

    echo "Correo enviado correctamente";

} catch (Exception $e) {

    $details = $mail->ErrorInfo ?: $e->getMessage();
    echo "Error: {$details}";
}
