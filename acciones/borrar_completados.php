<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php';

$usuario_id = $_SESSION['usuario_id'];

$sql = "DELETE FROM tareas WHERE usuario_id = ? AND estado = 1";

if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("i", $usuario_id);
    
    if ($stmt->execute()) {
        // affected_rows nos dice exactamente cuántas tareas se borraron
        if ($stmt->affected_rows > 0) {
            $_SESSION['alerta_flash'] = "¡Se eliminaron " . $stmt->affected_rows . " tareas completadas!";
        } else {
            $_SESSION['alerta_flash'] = "No había tareas completadas para eliminar.";
        }
    } else {
        $_SESSION['alerta_error'] = "Hubo un error al intentar vaciar las tareas.";
    }
    
    $stmt->close();
}

$conexion->close();

// Redirección dinámica
$ruta_retorno = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
header("Location: " . $ruta_retorno);
exit();
?>