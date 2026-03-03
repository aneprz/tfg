<?php
session_start();
require '../../../db/conexiones.php';

function volverConError($mensaje) {
    header("Location: ../register/register.php?error=" . urlencode($mensaje));
    exit();
}

$tag = trim($_POST['gameTag'] ?? '');
$nombreApellido = trim($_POST['nombreApellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';
$confirmar = $_POST['confirmPassword'] ?? '';

if (empty($tag) || empty($nombreApellido) || empty($email) || empty($pass)) {
    volverConError("Todos los campos son obligatorios.");
}

if ($pass !== $confirmar) {
    volverConError("Las contraseñas no coinciden.");
}

if (strlen($pass) < 8) {
    volverConError("La contraseña debe tener al menos 8 caracteres.");
}

$stmt = $conexion->prepare("SELECT id_usuario FROM Usuario WHERE gameTag = ? OR email = ?");
$stmt->bind_param("ss", $tag, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    volverConError("El GameTag o el Email ya están registrados.");
}
$stmt->close();

$passHash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $conexion->prepare("INSERT INTO Usuario (gameTag, nombre_apellido, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $tag, $nombreApellido, $email, $passHash);

if ($stmt->execute()) {
    header("Location: ../login/login.php?success=Cuenta creada correctamente");
    exit();
} else {
    volverConError("Error al guardar en la base de datos.");
}

$stmt->close();
$conexion->close();
?>