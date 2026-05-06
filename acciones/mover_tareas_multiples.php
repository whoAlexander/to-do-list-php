<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $id_lista_destino = intval($_POST['id_lista_destino']);
    
    // Verificamos si enviaron tareas y si es un arreglo
    if (isset($_POST['tareas_seleccionadas']) && is_array($_POST['tareas_seleccionadas'])) {
        
        $tareas_a_mover = $_POST['tareas_seleccionadas'];
        $movidas_exitosamente = 0;

        // Preparamos la consulta una sola vez para mayor rendimiento
        $sql = "UPDATE tareas SET lista_id = ? WHERE id_tarea = ? AND usuario_id = ?";
        
        if ($stmt = $conexion->prepare($sql)) {
            // Recorremos cada ID que el usuario seleccionó en los checkboxes
            foreach ($tareas_a_mover as $id_tarea) {
                $id_tarea_int = intval($id_tarea);
                
                // "iii" = 3 Integers (lista_id, id_tarea, usuario_id)
                $stmt->bind_param("iii", $id_lista_destino, $id_tarea_int, $usuario_id);
                
                if ($stmt->execute()) {
                    $movidas_exitosamente++;
                }
            }
            $stmt->close();
        }

        if ($movidas_exitosamente > 0) {
            $_SESSION['alerta_flash'] = "¡Se movieron $movidas_exitosamente tareas a esta lista!";
        } else {
            $_SESSION['alerta_error'] = "No se pudieron mover las tareas.";
        }
        
    } else {
        $_SESSION['alerta_error'] = "No seleccionaste ninguna tarea para mover.";
    }
}

$conexion->close();
$ruta_retorno = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
header("Location: " . $ruta_retorno);
exit();
?>