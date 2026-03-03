<?php

$_SESSION['tag'] = $_POST['gameTag'];
$_SESSION['nombreApellido'] = $_POST['nombreApellido'];
$_SESSION['email'] = $_POST['email'];

header("Location: ../login/login.php"); 
    exit();

