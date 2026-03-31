<?php
// Configuración de la base de datos
$servername = "localhost"; // El servidor es localhost
$username = "root";         // Usuario por defecto en XAMPP
$password = "";             // Contraseña por defecto (vacía)
$dbname = "to do list"; // Cambia esto por tu base de datos
$message = "";
$user = "";
// Crear conexión
$conexion = new mysqli($servername, $username, $password, $dbname);

// Comprobar la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
else{
    echo "<script>console.log('conexion exitosa a la base de datos 👌')</script>";
}