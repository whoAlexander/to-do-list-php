<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    $id_tarea = intval($_GET['id']);
    $usuario_id = $_SESSION['usuario_id'];

    $sql = "UPDATE tareas SET estado = 1 WHERE id_tarea = ? AND usuario_id = ?";
    
    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("ii", $id_tarea, $usuario_id);
        
        if ($stmt->execute()) {
            $_SESSION['alerta_flash'] = "¡Tarea completada! Excelente trabajo.";
        } else {
            $_SESSION['alerta_error'] = "Hubo un error al intentar completar la tarea.";
        }
        $stmt->close();
    }
} else {
    $_SESSION['alerta_error'] = "No se especificó qué tarea completar.";
}

$conexion->close();

$ruta_retorno = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
header("Location: " . $ruta_retorno);
exit();
?>