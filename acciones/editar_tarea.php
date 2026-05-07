<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibimos todos los datos del formulario (Modal)
    $id_tarea = intval($_POST['id_tarea']);
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $lista_id_recibida = $_POST['lista_id'];
    
    $usuario_id = $_SESSION['usuario_id'];

    // Convertimos el "0" de la Bandeja de entrada en un valor NULL para MySQL
    $lista_id_final = ($lista_id_recibida == 0) ? null : intval($lista_id_recibida);

    // Validamos que el título no esté vacío
    if (!empty($titulo) && $id_tarea > 0) {
        
        $sql = "UPDATE tareas SET titulo = ?, descripcion = ?, lista_id = ? WHERE id_tarea = ? AND usuario_id = ?";
        
        if ($stmt = $conexion->prepare($sql)) {
            // "ssiii" = String(titulo), String(descripcion), Int(lista_id), Int(id_tarea), Int(usuario_id)
            $stmt->bind_param("ssiii", $titulo, $descripcion, $lista_id_final, $id_tarea, $usuario_id);
            
            if ($stmt->execute()) {
                $_SESSION['alerta_flash'] = "¡Tarea actualizada correctamente!";
            } else {
                $_SESSION['alerta_error'] = "Hubo un error al actualizar: " . $stmt->error;
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