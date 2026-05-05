<?php
// 1. Incluimos la conexión a la base de datos
// Usamos "../" para salir de la carpeta 'acciones' y buscar 'config'
require_once '../config/conexion.php'; 

// 2. Verificamos que los datos vengan del método POST (del botón enviar)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (username, email, clave) VALUES (?, ?, ?)";

    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("sss", $username, $email, $password_hashed);

        if ($stmt->execute()) {
            
            header("Location: ../login.php?mensaje=registrado");
            exit(); 
        } else {
            if ($conexion->errno == 1062) {
                header("Location: ../register.php?mensaje=duplicado");
                exit();
            } else {
                header("Location: ../register.php?mensaje=error");
                exit();
            }
        }
        // si se loguea desde login mandar a home.php



        $stmt->close();
    }
    $conexion->close();
}
?>