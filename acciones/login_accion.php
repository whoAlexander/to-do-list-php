<?php

session_start();

require '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // 2. Buscamos al usuario en la base de datos por su nombre
    $sql = "SELECT id_usuario, username, clave FROM usuarios WHERE username = ?";
    
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();

            // 4. Verificamos la contraseña
            if (password_verify($password, $usuario['clave'])) {
                
                // Guardamos sus datos en la sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['username'] = $usuario['username'];

                header("Location: ../dashboard.php"); 
                exit(); 
                
            } else {
                
                header("Location: ../login.php?mensaje=error_login");
                exit();
            }
        } else {
            header("Location: ../login.php?mensaje=error_login");
            exit();
        }
        $stmt->close();
    }
    $conexion->close();
}
?>