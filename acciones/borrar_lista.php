<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php';

// 2. Verificamos que hayamos recibido un ID por la URL (ej: borrar_lista.php?id=3)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    
    // Convertimos a número entero por seguridad
    $id_lista = intval($_GET['id']); 
    $usuario_id = $_SESSION['usuario_id'];

    $sql_actualizar_tareas = "UPDATE tareas SET lista_id = NULL WHERE lista_id = ? AND usuario_id = ?";
    if ($stmt_tareas = $conexion->prepare($sql_actualizar_tareas)) {
        $stmt_tareas->bind_param("ii", $id_lista, $usuario_id);
        $stmt_tareas->execute();
        $stmt_tareas->close();
    }

    $sql_borrar_lista = "DELETE FROM listas WHERE id_lista = ? AND usuario_id = ?";
    
    if ($stmt = $conexion->prepare($sql_borrar_lista)) {
        $stmt->bind_param("ii", $id_lista, $usuario_id);
        
        if ($stmt->execute()) {
            // affected_rows nos dice si realmente se borró algo
            if ($stmt->affected_rows > 0) {
                $_SESSION['alerta_flash'] = "¡Lista eliminada correctamente!";
            } else {
                // Si affected_rows es 0, significa que la lista no existía o no era de ese usuario
                $_SESSION['alerta_error'] = "No se pudo eliminar la lista. Quizás ya no existe.";
            }
        } else {
            $_SESSION['alerta_error'] = "Error al intentar eliminar la lista en la base de datos.";
        }
        
        $stmt->close();
    }

} else {
    // Si no enviaron el ID por la URL
    $_SESSION['alerta_error'] = "No se especificó qué lista eliminar.";
}

$conexion->close();

header("Location: ../dashboard.php");
exit();
?>