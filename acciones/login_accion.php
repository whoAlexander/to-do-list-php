<?php
// 1. INICIAMOS LA SESIÓN (¡Súper importante!)
// Esto permite guardar el nombre del usuario para mostrarlo en la siguiente página
session_start();

require '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Asumimos que en tu login.php los inputs tienen name="email" y name="password"
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // 2. Buscamos al usuario en la base de datos por su correo
    $sql = "SELECT id_usuario, username, clave FROM usuarios WHERE username = ?";
    
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();

        // 3. ¿Existe el correo en la base de datos?
        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();

            // 4. Verificamos la contraseña
            // password_verify compara la contraseña escrita con el Hash raro de la BD
            if (password_verify($password, $usuario['clave'])) {
                
                // ¡ÉXITO! Las credenciales son correctas.
                // Guardamos sus datos en la "mochila" de la sesión
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['username'] = $usuario['username'];

                // === 5. LA REDIRECCIÓN ===
                // Lo mandamos a la página principal de tareas (ej: dashboard.php)
                header("Location: ../dashboard.php"); 
                exit(); // Siempre pon exit() después de un header
                
            } else {
                // Contraseña incorrecta (Alerta amarilla que configuramos antes)
                header("Location: ../login.php?mensaje=error_login");
                exit();
            }
        } else {
            // El correo no existe (Usamos el mismo error por seguridad)
            header("Location: ../login.php?mensaje=error_login");
            exit();
        }
        $stmt->close();
    }
    $conexion->close();
}
?>