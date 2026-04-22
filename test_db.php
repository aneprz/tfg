<?php
$conexion = mysqli_connect(
    "sql100.infinityfree.com",
    "if0_41716581",
    "SalsaBox12",
    "if0_41716581_salsabox_db"
);

if (!$conexion) {
    die("❌ Error de conexión: " . mysqli_connect_error());
}

echo "✅ Conectado correctamente a la base de datos";
?>