<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recibimos el ID de la lista oculta y el nuevo nombre
    $id_lista = intval($_POST['id_lista']);
    $nuevo_nombre = trim($_POST['nuevo_nombre']);
    $usuario_id = $_SESSION['usuario_id'];

    if (!empty($nuevo_nombre) && $id_lista > 0) {
        
        // Actualizamos el nombre, validando que la lista le pertenezca al usuario
        $sql = "UPDATE listas SET nombre_lista = ? WHERE id_lista = ? AND usuario_id = ?";
        
        if ($stmt = $conexion->prepare($sql)) {
            // "sii" = String (nuevo nombre), Integer (id_lista), Integer (usuario_id)
            $stmt->bind_param("sii", $nuevo_nombre, $id_lista, $usuario_id);
            
            if ($stmt->execute()) {
                $_SESSION['alerta_flash'] = "¡Nombre de la lista actualizado!";
            } else {
                $_SESSION['alerta_error'] = "Hubo un error al actualizar la lista.";
            }
            
            $stmt->close();
        }
    } else {
        $_SESSION['alerta_error'] = "El nombre de la lista no puede estar vacío.";
    }
    
    $conexion->close();
    header("Location: ../dashboard.php");
    exit();
    
} else {
    header("Location: ../dashboard.php");
    exit();
}
?>