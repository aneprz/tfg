<?php
session_start();
$_SESSION['tag'] = $_POST['gameTag'];

header("Location: ../../../index.php"); 
exit();

