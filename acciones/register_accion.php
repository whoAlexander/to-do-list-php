<?php

session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
require_once '../config/conexion.php'; 

// Verificamos que los datos vengan del método POST
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

        $stmt->close();
    }
    $conexion->close();
}
?>