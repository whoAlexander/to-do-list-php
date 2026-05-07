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

// 1. Conectamos a la base de datos
require 'config/conexion.php'; 

// 2. Buscamos las listas del usuario logueado
$usuario_id = $_SESSION['usuario_id'];
$listas_usuario = []; // Creamos un arreglo vacío para guardar las listas

$sql_listas = "SELECT id_lista, nombre_lista FROM listas WHERE usuario_id = ? ORDER BY created_at ASC";
if ($stmt_listas = $conexion->prepare($sql_listas)) {
    $stmt_listas->bind_param("i", $usuario_id);
    $stmt_listas->execute();
    $resultado = $stmt_listas->get_result();
    
    // 3. Guardamos cada lista en nuestro arreglo
    while ($fila = $resultado->fetch_assoc()) {
        $listas_usuario[] = $fila;
    }
    $stmt_listas->close();
}
// NO cerramos $conexion->close() todavía porque la usaremos para las tareas más adelante

// 4. Buscamos las tareas pendientes del usuario
    $tareas_usuario = [];
    $sql_tareas = "
        SELECT t.*, l.nombre_lista 
        FROM tareas t 
        LEFT JOIN listas l ON t.lista_id = l.id_lista 
        WHERE t.usuario_id = ? AND t.estado = 1 
        ORDER BY t.created_at DESC
    ";
    
    if ($stmt_tareas = $conexion->prepare($sql_tareas)) {
        $stmt_tareas->bind_param("i", $usuario_id);
        $stmt_tareas->execute();
        $resultado_tareas = $stmt_tareas->get_result();
        
        while ($fila_tarea = $resultado_tareas->fetch_assoc()) {
            $tareas_usuario[] = $fila_tarea;
        }
        $stmt_tareas->close();
    }

?>

<?php include 'includes/header.php'; ?>
<?php include 'acciones/obtener_fecha.php'; ?>

<style>
    body, main {
        display: block !important; 
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Diseño normal para PC */
    .responsive-sidebar {
        width: 280px;
        position: sticky;
        top: 0;
    }
    
    /* Diseño para Celulares (pantallas menores a 768px) */
    @media (max-width: 768px) {
        .responsive-sidebar {
            width: 100% !important; 
            height: auto !important; 
            position: relative !important; 
            border-right: none !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 2rem !important; 
        }
    }
</style>
<div class="container-fluid px-0 w-100">
    <div class="d-block d-md-flex" style="min-height: 100vh;"> 


        <!-- barra lateral -->
        <div class="glass-sidebar p-4 d-flex flex-column responsive-sidebar">
            
            <a href="index.php" class="text-white text-decoration-none mb-4 mt-2">
                <h3 style="font-family: 'Archivo Black', sans-serif; letter-spacing: 1px; text-align: center;">To Do List</h3>
            </a>
            
            <hr class="text-white opacity-25 mt-0 mb-2">

            <ul class="nav flex-column gap-3 mb-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-3 py-1 text-white-50" style="font-size: 0.9rem;" href="buscador.php">
                        <i class="bi bi-search fs-5"></i>
                        <span>Buscador</span>
                    </a>
                </li>

                <li class="nav-item ">
                    <a class="nav-link d-flex align-items-center gap-3 py-1" style="font-size: 0.9rem;" href="bandejaDeEntrada.php">
                        <i class="bi bi-inbox fs-5 "></i>
                        <span>Bandeja de entrada</span>
                    </a>
                </li>


                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 py-1" style="font-size: 0.9rem;" href="dashboard.php">
                        <i class="bi bi-list-task fs-5 text-info"></i>
                        <span>Listas</span>
                    </a>
                    <!-- === INICIO DEL SUBMENÚ DE LISTAS === -->
                        <!-- ms-4 lo empuja a la derecha. mt-1 le da un mini espacio arriba -->
                        <ul class="nav flex-column ms-4 mt-1 gap-1">
                            
                    <?php if (!empty($listas_usuario)): ?>
                            <!-- Si el usuario tiene listas, las dibujamos una por una -->
                            <?php foreach ($listas_usuario as $lista): ?>
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center gap-2 py-1 text-white-50" style="font-size: 0.9rem;" href="lista.php?id=<?php echo $lista['id_lista']; ?>">
                                        <i class="bi bi-dot"></i>
                                        <!-- Usamos htmlspecialchars por seguridad, para evitar inyección de código si alguien nombra una lista con etiquetas HTML -->
                                        <span><?php echo htmlspecialchars($lista['nombre_lista']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Si no tiene listas, mostramos un mensajito -->
                            <li class="nav-item text-white-50 ms-3" style="font-size: 0.8rem;">No hay listas aún.</li>
                        <?php endif; ?>

                            
                        </ul>
                        <!-- === FIN DEL SUBMENÚ === -->


                </li>
                
                <li class="nav-item">
                    <a class="nav-link active d-flex align-items-center gap-3 py-1" style="font-size: 0.9rem;" href="completados.php">
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

        <div class="flex-grow-1 px-3 py-4 p-md-5 overflow-auto">
            
            <div class="card glass-card p-3 p-md-5 mx-auto" style="width: 100%; max-width: 1000px; text-align: left;">
                <h2 class="text-white mb-4">Hola <?php echo $_SESSION['username']; ?>, buen <?php echo $diaSemana; ?> 👋</h2>
                <hr class="text-white opacity-25 mb-4">

                <div class="d-flex justify-content-between align-items-center">
                    <h3>Completados</h3>
                    <!-- Botón que dispara el modal de vaciar completadas -->
                    <button type="button" class="btn btn-outline-light d-inline-flex align-items-center gap-2 mx-2" data-bs-toggle="modal" data-bs-target="#modalBorrarCompletadas" style="width: fit-content;">
                        <i class="bi bi-trash"></i> Borrar tareas completadas
                    </button>
                </div>

                <!-- === LISTA VERTICAL DE TAREAS COMPLETADAS === -->
                <div class="mt-4 mb-5">
                    <?php if (!empty($tareas_usuario)): ?>
                        <div class="d-flex flex-column gap-3">
                            
                            <?php foreach ($tareas_usuario as $tarea): ?>
                                <!-- Le quitamos la opacidad al contenedor principal -->
                                <div class="glass-form-element p-3 rounded d-flex justify-content-between align-items-start">
                                    
                                    <!-- Y se la aplicamos SOLO al bloque izquierdo (el contenido de la tarea) -->
                                    <div class="d-flex gap-3 flex-grow-1" style="opacity: 0.5;">
                                        
                                        <!-- Checkbox ya marcado (Verde y relleno) -->
                                        <div class="mt-1">
                                            <!-- Enlace para "Desmarcar" o restaurar la tarea -->
                                            <a href="acciones/restaurar_tarea.php?id=<?php echo $tarea['id_tarea']; ?>" class="text-decoration-none" title="Desmarcar y volver a pendientes">
                                                <i class="bi bi-check-circle-fill text-success fs-5" style="cursor: pointer;"></i>
                                            </a>
                                        </div>
                                        
                                        <div class="d-flex flex-column gap-1 w-100">
                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                <!-- Título tachado (text-decoration-line-through) -->
                                                <h5 class="text-white-50 mb-0 text-decoration-line-through" style="font-weight: 500; font-size: 1.1rem;">
                                                    <?php echo htmlspecialchars($tarea['titulo']); ?>
                                                </h5>
                                                
                                                <!-- Badge de la lista (opcional, para saber de dónde venía) -->
                                                <?php if (!empty($tarea['nombre_lista'])): ?>
                                                    <span class="badge rounded-pill" style="background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.1); font-weight: normal; color: rgba(255,255,255,0.5);">
                                                        <i class="bi bi-folder2 me-1"></i> <?php echo htmlspecialchars($tarea['nombre_lista']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill" style="background-color: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); font-weight: normal; color: rgba(255,255,255,0.5);">
                                                        <i class="bi bi-inbox me-1"></i> Bandeja de entrada
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Descripción también tachada -->
                                            <?php if (!empty($tarea['descripcion'])): ?>
                                                <p class="text-white-50 mb-0 mt-1 small text-decoration-line-through" style="line-height: 1.4;">
                                                    <?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Menú de 3 puntitos -->
                                    <div class="dropdown ms-2">
                                        <button class="btn btn-sm text-white-50 p-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots fs-5"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg" style="background-color: rgba(30, 20, 35, 0.95); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                                            <li>
                                                <!-- Opción para restaurar (en vez de editar) -->
                                                <a class="dropdown-item d-flex align-items-center gap-2" href="acciones/restaurar_tarea.php?id=<?php echo $tarea['id_tarea']; ?>">
                                                    <i class="bi bi-arrow-counterclockwise text-info"></i> Restaurar 
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                                            <li>
                                                <button class="dropdown-item d-flex align-items-center gap-2 text-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarTarea<?php echo $tarea['id_tarea']; ?>">
                                                    <i class="bi bi-trash"></i> Eliminar definitivamente
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    
                                </div>
                            <?php endforeach; ?>
                            
                        </div>
                    <?php else: ?>
                        <!-- Estado Vacío para Completados -->
                        <div class="p-4 rounded text-center text-white-50" style="background-color: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2);">
                            <i class="bi bi-award fs-2 mb-2 d-block text-white-50"></i>
                            Aún no has completado ninguna tarea. ¡Ve por ellas!
                        </div>
                    <?php endif; ?>
                </div>
                <!-- ========================================================== -->
            </div>
        </div>
    </div>
</div>

                <!-- ========================================================== -->
                <!-- ZONA SEGURA DE MODALES (Fuera de los contenedores flex/overflow) -->
                            <!-- MODALES PARA TAREAS -->
                            <?php if (!empty($tareas_usuario)): ?>
                                <?php foreach ($tareas_usuario as $tarea): ?>
                                    
                                    <!-- 1. Modal Editar Tarea -->
                                    <div class="modal fade" id="modalEditarTarea<?php echo $tarea['id_tarea']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content shadow-lg" style="background-color: rgba(40, 25, 45, 0.95); border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(15px);">
                                                
                                                <div class="modal-header border-bottom border-secondary border-opacity-25">
                                                    <h5 class="modal-title text-white" style="font-weight: 500;">Editar Tarea</h5>
                                                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                
                                                <form action="acciones/editar_tarea.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_tarea" value="<?php echo $tarea['id_tarea']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label text-white-50 small">Título</label>
                                                            <input type="text" name="titulo" class="form-control bg-transparent text-white border-secondary shadow-none glass-form-element" value="<?php echo htmlspecialchars($tarea['titulo']); ?>" required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label text-white-50 small">Descripción (opcional)</label>
                                                            <textarea name="descripcion" class="form-control bg-transparent text-white border-secondary shadow-none glass-form-element" rows="3" style="resize: none;"><?php echo htmlspecialchars($tarea['descripcion']); ?></textarea>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label text-white-50 small">Mover a lista</label>
                                                            <select name="lista_id" class="form-select bg-transparent text-white border-secondary shadow-none glass-form-element">
                                                                <!-- Verificamos si la lista_id es NULL para marcar Bandeja de entrada como selected -->
                                                                <option value="0" <?php echo is_null($tarea['lista_id']) ? 'selected' : ''; ?> class="text-dark">Bandeja de entrada</option>
                                                                
                                                                <?php foreach ($listas_usuario as $lista): ?>
                                                                    <option value="<?php echo $lista['id_lista']; ?>" class="text-dark" <?php echo ($tarea['lista_id'] == $lista['id_lista']) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($lista['nombre_lista']); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="modal-footer border-top border-secondary border-opacity-25">
                                                        <button type="button" class="btn btn-sm glass-form-element" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-sm glass-btn-primary">Guardar cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Modal Eliminar Tarea -->
                                    <div class="modal fade" id="modalEliminarTarea<?php echo $tarea['id_tarea']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content shadow-lg" style="background-color: rgba(45, 20, 25, 0.95); border: 1px solid rgba(220, 53, 69, 0.3); backdrop-filter: blur(15px);">
                                                
                                                <div class="modal-header border-bottom border-danger border-opacity-25">
                                                    <h5 class="modal-title text-danger" style="font-weight: 500;">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Tarea
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                
                                                <div class="modal-body">
                                                    <p class="text-white mb-0">¿Estás seguro de que deseas eliminar permanentemente la tarea <strong>"<?php echo htmlspecialchars($tarea['titulo']); ?>"</strong>?</p>
                                                </div>
                                                
                                                <div class="modal-footer border-top border-danger border-opacity-25">
                                                    <button type="button" class="btn btn-sm glass-form-element" data-bs-dismiss="modal">Cancelar</button>
                                                    <a href="acciones/borrar_tarea.php?id=<?php echo $tarea['id_tarea']; ?>" class="btn btn-sm border-0 text-white" style="background-color: rgba(220, 53, 69, 0.8);">Sí, eliminar</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; ?>
                            <?php endif; ?>
                            <!-- FIN DE MODALES PARA TAREAS -->
                            <!-- ========================================================== -->


                            <!-- === MODAL PARA VACIAR TODAS LAS TAREAS COMPLETADAS === -->
                            <div class="modal fade" id="modalBorrarCompletadas" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow-lg" style="background-color: rgba(45, 20, 25, 0.95); border: 1px solid rgba(220, 53, 69, 0.3); backdrop-filter: blur(15px);">
                                        
                                        <div class="modal-header border-bottom border-danger border-opacity-25">
                                            <h5 class="modal-title text-danger" style="font-weight: 500;">
                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Vaciar Completadas
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        
                                        <div class="modal-body">
                                            <p class="text-white mb-0">¿Estás seguro de que deseas eliminar permanentemente <strong>todas</strong> las tareas completadas? Esta acción no se puede deshacer.</p>
                                        </div>
                                        
                                        <div class="modal-footer border-top border-danger border-opacity-25">
                                            <button type="button" class="btn btn-sm glass-form-element" data-bs-dismiss="modal">Cancelar</button>
                                            <!-- Este enlace llama al archivo PHP que crearemos ahora -->
                                            <a href="acciones/borrar_completados.php" class="btn btn-sm border-0 text-white" style="background-color: rgba(220, 53, 69, 0.8);">Sí, vaciar todo</a>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            <!-- ========================================================== -->


                <!-- FIN DE ZONA DE MODALES -->
                <!-- ========================================================== -->


<script src="assets/js/mostrarFormularioTarea.js"></script>
<?php include 'includes/footer.php'; ?>