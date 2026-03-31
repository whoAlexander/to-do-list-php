<?php
session_start(); // Primero nos conectamos a la sesión actual

// 1. Vaciamos todas las variables de la sesión ($_SESSION['username'], etc.)
session_unset();

// 2. Destruimos la sesión por completo en el servidor
session_destroy();

// 3. Mandamos al usuario de vuelta al Login o al Inicio
header("Location: ../login.php");
exit();
?>