<?php
require '../../../../db/conexiones.php';

$token = $_GET['token'] ?? '';

$stmt = $conexion->prepare("
UPDATE Usuario
SET email_verificado = 1, token_verificacion = NULL
WHERE token_verificacion = ?
");

$stmt->bind_param("s",$token);
$stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Cuenta verificada</title>

<script>
setTimeout(function(){
    window.location.href = "../../login/login.php";
},3000);
</script>

<style>
body{
background:#14181c;
color:white;
font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;
display:flex;
justify-content:center;
align-items:center;
height:100vh;
}

.card{
background:#1f252c;
padding:40px;
border-radius:12px;
border:1px solid #2c3440;
text-align:center;
}

h2{
border-bottom:2px solid #e0be00;
padding-bottom:10px;
margin-bottom:20px;
}
</style>

</head>

<body>

<div class="card">
<h2>Cuenta verificada</h2>
<p>Tu correo ha sido verificado correctamente.</p>
<p>Redirigiendo al login...</p>
</div>

</body>
</html>