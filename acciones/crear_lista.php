<?php
session_start();

// Validamos que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibimos el nombre de la lista desde el formulario
    $nombre_lista = trim($_POST['nombre_lista']); 
    
    // El ID del usuario lo sacamos de la sesión, como siempre
    $usuario_id = $_SESSION['usuario_id']; 

    // Validamos que no nos envíen una lista en blanco
    if (!empty($nombre_lista)) {
        
        // Preparamos la consulta SQL
        // IMPORTANTE: Asumo que tu tabla se llama 'listas' y las columnas son 'usuario_id' y 'nombre'
        $sql = "INSERT INTO listas (usuario_id, nombre_lista) VALUES (?, ?)";
        
        if ($stmt = $conexion->prepare($sql)) {
            // "is" significa: Integer (usuario_id), String (nombre)
            $stmt->bind_param("is", $usuario_id, $nombre_lista);
            
            if ($stmt->execute()) {
                $_SESSION['alerta_flash'] = "¡Lista creada con éxito!";
            } else {
                $_SESSION['alerta_error'] = "Hubo un error al crear la lista: " . $stmt->error;
            }
            
            $stmt->close();
        }
    } else {
        $_SESSION['alerta_error'] = "El nombre de la lista no puede estar vacío.";
    }
    
    $conexion->close();
    
    // Devolvemos al usuario al Dashboard
    header("Location: ../dashboard.php");
    exit();
    
} else {
    header("Location: ../dashboard.php");
    exit();
}
?>