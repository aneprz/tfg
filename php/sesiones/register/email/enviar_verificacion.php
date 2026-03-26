<?php

$autoload = __DIR__ . '/../../../../vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    die("Falta vendor/autoload.php. Ejecuta 'composer install' en la raíz del proyecto.");
}
require $autoload;
require __DIR__ . '/../../../../API/credenciales.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!class_exists(PHPMailer::class)) {
    http_response_code(500);
    die("PHPMailer no está instalado. Ejecuta 'composer install' (o 'composer require phpmailer/phpmailer') en la raíz del proyecto.");
}
function enviarCorreoReset($email,$link){

    global $gmail_email;
    global $gmail_password;

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host="smtp.gmail.com";
    $mail->SMTPAuth=true;
    $mail->Username=$gmail_email;
    $mail->Password=$gmail_password;
    $mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port=587;

    $mail->setFrom($gmail_email,"SalsaBox");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->CharSet="UTF-8";
    $mail->Subject="Recuperar contraseña SalsaBox";

    $mail->Body='
    <table width="100%" style="background:#14181c;padding:40px;font-family:Segoe UI;">
    <tr>
    <td align="center">

    <table width="420" style="background:#1f252c;border:1px solid #2c3440;padding:40px;border-radius:12px;color:white;">

    <tr>
    <td align="center">
    <h2 style="border-bottom:2px solid #e0be00;padding-bottom:10px;">Recuperar contraseña</h2>
    </td>
    </tr>

    <tr>
    <td style="text-align:center;color:#9ab;padding:20px;">
    Recibimos una solicitud para cambiar tu contraseña.
    </td>
    </tr>

    <tr>
    <td align="center">
    <a href="'.$link.'" style="
    background:#e0be00;
    padding:14px 28px;
    color:black;
    text-decoration:none;
    border-radius:6px;
    font-weight:bold;
    ">
    CAMBIAR CONTRASEÑA
    </a>
    </td>
    </tr>

    <tr>
    <td style="text-align:center;color:#9ab;padding-top:20px;font-size:12px;">
    Este enlace expira en 30 minutos
    </td>
    </tr>

    </table>

    </td>
    </tr>
    </table>
    ';

    $mail->send();
}

function enviarCorreoVerificacion($email, $token)
{
    global $gmail_email;
    global $gmail_password;

    $link = "http://localhost:3000/php/sesiones/register/email/verificar.php?token=".$token;

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = $gmail_email;
    $mail->Password = $gmail_password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom($gmail_email, "SalsaBox");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->CharSet = "UTF-8";
    $mail->Subject = "Verifica tu cuenta en SalsaBox";

    $mail->Body = '
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#14181c;padding:40px 0;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;">
        <tr>
        <td align="center">

        <table width="420" cellpadding="0" cellspacing="0" style="background:#1f252c;border-radius:12px;border:1px solid #2c3440;padding:40px;color:#ffffff;">

        <tr>
        <td align="center" style="padding-bottom:20px;">
        <h2 style="
        margin:0;
        text-transform:uppercase;
        letter-spacing:2px;
        font-size:20px;
        border-bottom:2px solid #e0be00;
        padding-bottom:10px;
        ">
        SalsaBox
        </h2>
        </td>
        </tr>

        <tr>
        <td style="color:#9ab;font-size:15px;text-align:center;padding-top:10px;">
        Tu cuenta ha sido creada correctamente.
        </td>
        </tr>

        <tr>
        <td style="color:#9ab;font-size:15px;text-align:center;padding-top:10px;">
        Haz clic en el botón para verificar tu correo.
        </td>
        </tr>

        <tr>
        <td align="center" style="padding:30px 0;">
        <a href="'.$link.'" 
        style="
        background:#e0be00;
        color:#000;
        padding:14px 28px;
        text-decoration:none;
        font-weight:bold;
        border-radius:6px;
        display:inline-block;
        font-size:14px;
        ">
        VERIFICAR CUENTA
        </a>
        </td>
        </tr>

        <tr>
        <td style="color:#9ab;font-size:13px;text-align:center;padding-top:10px;">
        Si no creaste esta cuenta puedes ignorar este correo.
        </td>
        </tr>

        </table>

        </td>
        </tr>
        </table>
        ';

    if(!$mail->send()){
        echo "ERROR: ".$mail->ErrorInfo;
    }else{
        echo "Correo enviado correctamente";
    }
}
