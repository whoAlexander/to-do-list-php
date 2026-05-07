<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibimos los datos
    $titulo_tarea = trim($_POST['nombre_tarea']); 
    $descripcion_tarea = trim($_POST['descripcion_tarea']); 
    $lista_id_recibida = $_POST['lista_id'];
    
    $usuario_id = $_SESSION['usuario_id']; 

    $lista_id_final = ($lista_id_recibida == 0) ? null : $lista_id_recibida;

    if (!empty($titulo_tarea)) {
        
        $sql = "INSERT INTO tareas (usuario_id, titulo, descripcion, lista_id) VALUES (?, ?, ?, ?)";
        
        if ($stmt = $conexion->prepare($sql)) {
            // "issi" (Int para usuario, String para titulo, String para desc, Int/Null para lista)
            $stmt->bind_param("issi", $usuario_id, $titulo_tarea, $descripcion_tarea, $lista_id_final);
            
            if ($stmt->execute()) {
                $_SESSION['alerta_flash'] = "¡Tarea creada con éxito!";
            } else {
                $_SESSION['alerta_error'] = "Hubo un error al guardar: " . $stmt->error;
            }
            
            $stmt->close();
        }
    } else {
        $_SESSION['alerta_error'] = "El título de la tarea no puede estar vacío.";
    }
    
    $conexion->close();
    
    $ruta_retorno = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
    header("Location: " . $ruta_retorno);
    exit();
    
} else {
    $ruta_retorno = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
    header("Location: " . $ruta_retorno);
    exit();
}
?>