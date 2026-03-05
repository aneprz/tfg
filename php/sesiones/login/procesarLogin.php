<?php
session_start();
require '../../../db/conexiones.php';

$tag = $_POST['gameTag'];
$pass = $_POST['password'];

$stmt = $conexion->prepare("SELECT id_usuario, password FROM Usuario WHERE gameTag = ?");
$stmt->bind_param("s", $tag);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if ($usuario && password_verify($pass, $usuario['password'])) {
    $_SESSION['tag'] = $tag;
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    header("Location: ../../../index.php");
} else {
    echo "<script>alert('Datos incorrectos'); window.location.href='../login/login.php';</script>";
}
exit();
?>