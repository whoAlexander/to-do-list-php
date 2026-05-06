<?php
session_start();

// 1. Validamos sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php';

// 2. Verificamos que se reciba el ID de la tarea por la URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_tarea = intval($_GET['id']); 
    $usuario_id = $_SESSION['usuario_id'];

    // 3. Preparamos la consulta para eliminar
    // SIEMPRE validamos con usuario_id para que nadie borre tareas de otros
    $sql = "DELETE FROM tareas WHERE id_tarea = ? AND usuario_id = ?";
    
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("ii", $id_tarea, $usuario_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['alerta_flash'] = "¡Tarea eliminada correctamente!";
            } else {
                $_SESSION['alerta_error'] = "No se pudo eliminar. Quizás la tarea no existe.";
            }
        } else {
            $_SESSION['alerta_error'] = "Error al intentar eliminar la tarea en la base de datos.";
        }
        
        $stmt->close();
    }

} else {
    $_SESSION['alerta_error'] = "No se especificó qué tarea eliminar.";
    $ruta_retorno = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
    header("Location: " . $ruta_retorno);
    exit();
}

$conexion->close();
    
    // Preguntamos: "¿De qué página venía el usuario?" Si no sabemos, lo mandamos al dashboard por defecto.
    $ruta_retorno = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
    header("Location: " . $ruta_retorno);
    exit();
?>