<?php
session_start(); // Primero nos conectamos a la sesión actual

// Vaciamos todas las variables de la sesión 
session_unset();

// Destruimos la sesión por completo en el servidor
session_destroy();

// Mandamos al usuario de vuelta al Login 
header("Location: ../login.php");
exit();
?>