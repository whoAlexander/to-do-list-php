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
<?php include 'acciones/obtener_fecha.php'; ?>

<style>
    /* Esto obliga al body a no centrar, usar el 100% y dejar espacio para el nav */
    body, main {
        display: block !important; 
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }p
</style>
<div class="container-fluid px-0 w-100">
    <div class="d-flex" style="height: 100vh;"> 


        <!-- barra lateral -->
        <div class="glass-sidebar p-4 d-flex flex-column h-100" style="width: 280px;">
            
            <a href="index.php" class="text-white text-decoration-none mb-4 mt-2">
                <h3 style="font-family: 'Archivo Black', sans-serif; letter-spacing: 1px; text-align: center;">To Do List</h3>
            </a>
            
            <hr class="text-white opacity-25 mt-0 mb-2">

            <ul class="nav flex-column gap-3 mb-auto">
                <li class="nav-item ">
                    <a class="nav-link d-flex align-items-center gap-3 py-1" style="font-size: 0.9rem;" href="dashboard.php">
                        <i class="bi bi-search fs-5 "></i>
                        <span>Buscador</span>
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link active d-flex align-items-center gap-2 py-1" style="font-size: 0.9rem;" href="dashboard.php">
                        <i class="bi bi-list-task fs-5 text-info"></i>
                        <span>Listas</span>
                    </a>
                    <!-- === INICIO DEL SUBMENÚ DE LISTAS === -->
                        <!-- ms-4 lo empuja a la derecha. mt-1 le da un mini espacio arriba -->
                        <ul class="nav flex-column ms-4 mt-1 gap-1">
                            
                            <!-- ESTO ES UN EJEMPLO DE CÓMO SE VERÍA UNA LISTA -->
                            <li class="nav-item">
                                <!-- py-1 lo hace más delgadito. text-white-50 lo hace un poco grisáceo para que no compita con el menú principal -->
                                <a class="nav-link d-flex align-items-center gap-2 py-1 text-white-50" style="font-size: 0.9rem;" href="dashboard.php?lista=1">
                                    <i class="bi bi-dot"></i> <!-- Un puntito como ícono -->
                                     <span>Universidad</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center gap-2 py-1 text-white-50" style="font-size: 0.9rem;" href="dashboard.php?lista=2">
                                    <i class="bi bi-dot"></i>
                                    <span>Supermercado</span>
                                </a>
                            </li>
                            
                        </ul>
                        <!-- === FIN DEL SUBMENÚ === -->


                </li>
                
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 py-1" style="font-size: 0.9rem;" href="completados.php">
                        <i class="bi bi-check2-circle fs-5 text-success "></i>
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
                <h2 class="text-white mb-4">Hola <?php echo $_SESSION['username']; ?>, buen <?php echo $diaSemana; ?> 👋</h2>
                <hr class="text-white opacity-25 mb-4">

                <div class="d-flex justify-content-between align-items-center">
                    <h3>Mis Tareas</h3>
                    <button id="btn-mostrar-tarea" class="btn btn-outline-light d-inline-flex align-items-center gap-2 mx-2" style="width: fit-content;">
                        <i class="bi bi-plus-lg"></i> Añadir nueva tarea
                    </button>
                </div>

                 <!-- === FORMULARIO OCULTO PARA AÑADIR TAREA === -->
                <!-- d-none lo oculta por defecto. Le damos un fondo semi-transparente para que resalte --> 
                <div id="formulario-tarea" class="d-none mt-3 p-3 rounded" style="background-color: rgba(0, 0, 0, 0.15); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <form action="acciones/crear_tarea.php" method="POST">
                        
                         <!-- Input para el nombre de la tarea --> 
                        <input type="text" name="nombre_tarea" class="form-control bg-transparent text-white border-secondary mb-3 shadow-none" placeholder="Escribe el nombre de la tarea..." required>
                        <textarea name="descripcion_tarea" class="form-control bg-transparent text-white border-secondary mb-3 shadow-none" placeholder="Descripción (opcional)..." rows="2" style="font-size: 0.9rem; resize: none;"></textarea>


                    <div class="d-flex justify-content-between align-items-center">
                        
                         <!-- Selector de Lista --> 
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-inbox text-white-50"></i>
                            <!-- Agregamos la clase 'glass-form-element' -->
                            <select name="lista_id" class="form-select form-select-sm glass-form-element shadow-none" style="width: auto; cursor: pointer;">
                                <option value="0">Añadir a Lista</option>
                                <option value="1">Universidad</option>
                                <option value="2">Supermercado</option>
                            </select>
                        </div>

                         <!-- Botones de Cancelar y Añadir --> 
                        <div class="d-flex gap-2">
                             <!-- Agregamos 'glass-form-element' al botón cancelar --> 
                            <button type="button" id="btn-cancelar-tarea" class="btn btn-sm glass-form-element">Cancelar</button>
                            
                            <!-- Agregamos 'glass-btn-primary' al botón de añadir --> 
                            <button type="submit" class="btn btn-sm glass-btn-primary">Añadir tarea</button>
                        </div>
                        
                    </div>
                    </form>
                </div>
                 <!-- ============================================ --> 




                <hr class="text-white opacity-25 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h3>Mis Listas</h3>
                    <button id="btn-mostrar-lista" class="btn btn-outline-light d-inline-flex align-items-center gap-2 mx-2" style="width: fit-content;">
                        <i class="bi bi-plus-lg"></i> Añadir nueva lista
                    </button>
                </div>

                 <!-- === FORMULARIO OCULTO PARA AÑADIR LISTA === -->
                <!-- d-none lo oculta por defecto. Le damos un fondo semi-transparente para que resalte --> 
                <div id="formulario-lista" class="d-none mt-3 p-3 rounded" style="background-color: rgba(0, 0, 0, 0.15); border: 1px solid rgba(255, 255, 255, 0.1);">
                    <form action="acciones/crear_lista.php" method="POST">                        
                         <!-- Input para el nombre de la lista --> 
                        <input type="text" name="nombre_lista" class="form-control bg-transparent text-white border-secondary mb-3 shadow-none" placeholder="Escribe el nombre de la lista..." required>                     

                    <div class="d-flex justify-content-between align-items-center">

                         <!-- Botones de Cancelar y Añadir --> 
                        <div class="d-flex gap-2">              
                            <button type="button" id="btn-cancelar-lista" class="btn btn-sm glass-form-element">Cancelar</button>
                            <button type="submit" class="btn btn-sm glass-btn-primary">Añadir lista</button>
                        </div>
                    </div>
                    </form>
                </div>
                <!-- ============================================ --> 




                <hr class="text-white opacity-25 mb-4">
            </div>
        </div>

    </div>
</div>
<script src="assets/js/mostrarFormularioTarea.js"></script>
<script src="assets/js/mostrarFormularioLista.js"></script>
<?php include 'includes/footer.php'; ?>