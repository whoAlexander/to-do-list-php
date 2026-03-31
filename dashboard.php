<?php
// === 1. EL PATOVICA (Protección de la página) ===
// DEBE ser lo primero en el archivo, antes de cualquier HTML
session_start();

// Si NO existe la variable de sesión (el usuario no se logueó)...
if (!isset($_SESSION['usuario_id'])) {
    // Lo mandamos de vuelta al login
    header("Location: login.php");
    exit(); // Detenemos el código para que no se siga leyendo hacia abajo
}
// ================================================
?>

<?php include 'includes/header.php'; ?>

<style>
    /* Esto obliga al body a no centrar, usar el 100% y dejar espacio para el nav */
    body, main {
        display: block !important; 
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
</style>
<div class="container-fluid px-0 w-100">
    <div class="d-flex" style="height: 100vh;"> 

        <div class="glass-sidebar p-4 d-flex flex-column h-100" style="width: 280px;">
            
            <a href="index.php" class="text-white text-decoration-none mb-4 mt-2">
                <h3 style="font-family: 'Archivo Black', sans-serif; letter-spacing: 1px; text-align: center;">To Do List</h3>
            </a>
            
            <hr class="text-white opacity-25 mt-0 mb-4">

            <ul class="nav flex-column gap-3 mb-auto">
                <li class="nav-item">
                    <a class="nav-link active d-flex align-items-center gap-2" href="dashboard.php">
                        <i class="bi bi-list-task fs-5 text-info"></i>
                        <span>Listas</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="completados.php">
                        <i class="bi bi-check2-circle fs-5 text-success"></i>
                        <span>Completados</span>
                    </a>
                </li>
            </ul>

            <hr class="text-white opacity-25">

            <a href="acciones/logout.php" class="nav-link d-flex align-items-center gap-3 text-danger">
                <i class="bi bi-box-arrow-right fs-5"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>

        <div class="flex-grow-1 p-5 overflow-auto ">
            
            <div class="card glass-card p-5" style="width: 100%; max-width: 1000px; text-align: left;">
                
                <h2 class="text-white mb-4">Hola, <?php echo $_SESSION['username']; ?> 👋</h2>
                <hr class="text-white opacity-25 mb-4">
                
                <p class="text-white-50 mb-4">Aquí aparecerán tus tareas pendientes...</p>
                
                <button class="btn btn-outline-light d-inline-flex align-items-center gap-2" style="width: fit-content;">
                    <i class="bi bi-plus-lg"></i> Añadir nueva tarea
                </button>
                
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>