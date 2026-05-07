<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require 'config/conexion.php';
$usuario_id = $_SESSION['usuario_id'];

// 1. Validamos que venga un ID en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}
$id_lista_actual = intval($_GET['id']);

// 2. Buscamos el nombre de esta lista para el Título
$lista_actual = null;
$sql_lista = "SELECT nombre_lista FROM listas WHERE id_lista = ? AND usuario_id = ?";
if ($stmt = $conexion->prepare($sql_lista)) {
    $stmt->bind_param("ii", $id_lista_actual, $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $lista_actual = $resultado->fetch_assoc();
    } else {
        // Si alguien inventa un ID falso en la URL o intenta ver la lista de otro, lo pateamos al dashboard
        header("Location: dashboard.php");
        exit();
    }
    $stmt->close();
}

// 3. Traemos TODAS las listas (las necesitamos para el sidebar y los modales)
$listas_usuario = [];
$sql_todas_listas = "SELECT id_lista, nombre_lista FROM listas WHERE usuario_id = ? ORDER BY created_at ASC";
if ($stmt = $conexion->prepare($sql_todas_listas)) {
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($fila = $res->fetch_assoc()) {
        $listas_usuario[] = $fila;
    }
    $stmt->close();
}

// 4. Traemos SOLO las tareas pendientes que pertenecen a ESTA lista
$tareas_usuario = [];
$sql_tareas = "SELECT * FROM tareas WHERE lista_id = ? AND usuario_id = ? AND estado = 0 ORDER BY created_at DESC";
if ($stmt_t = $conexion->prepare($sql_tareas)) {
    $stmt_t->bind_param("ii", $id_lista_actual, $usuario_id);
    $stmt_t->execute();
    $res_t = $stmt_t->get_result();
    while ($fila_tarea = $res_t->fetch_assoc()) {
        // Le agregamos manualmente el nombre de la lista al arreglo para reciclar tu HTML anterior
        $fila_tarea['nombre_lista'] = $lista_actual['nombre_lista'];
        $tareas_usuario[] = $fila_tarea;
    }


// 5. Traemos las tareas de la Bandeja de Entrada (lista_id IS NULL) para el Modal
$tareas_inbox = [];
$sql_inbox = "SELECT id_tarea, titulo FROM tareas WHERE usuario_id = ? AND lista_id IS NULL AND estado = 0 ORDER BY created_at DESC";
if ($stmt_in = $conexion->prepare($sql_inbox)) {
    $stmt_in->bind_param("i", $usuario_id);
    $stmt_in->execute();
    $res_in = $stmt_in->get_result();
    while ($fila_in = $res_in->fetch_assoc()) {
        $tareas_inbox[] = $fila_in;
    }
    $stmt_in->close();
}
}

// (Opcional) Aquí haríamos una consulta extra para buscar las tareas de la Bandeja de Entrada 
// y poder mostrarlas en el modal de "Añadir tareas existentes".
?>

<!-- Aquí empieza tu HTML, los <head>, el menú lateral, etc. -->


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

        <?php
            // Detectamos el nombre del archivo en el que estamos parados
            $pagina_actual = basename($_SERVER['PHP_SELF']);
            // Si estamos en una lista, guardamos su ID para saber cuál pintar de 'active'
            $id_url_actual = isset($_GET['id']) ? intval($_GET['id']) : 0;
        ?>
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

                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'bandejaDeEntrada.php') ? 'active text-white' : 'text-white-50'; ?> d-flex align-items-center gap-3 py-1" style="font-size: 0.9rem;" href="bandejaDeEntrada.php">
                        <i class="bi bi-inbox fs-5"></i>
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
                                    <?php 
                                    // Comprobamos si esta lista es la que estamos viendo actualmente
                                    $es_lista_activa = ($pagina_actual == 'lista.php' && $id_url_actual == $lista['id_lista']);
                                    ?>
                                    <li class="nav-item">
                                        <a class="nav-link d-flex align-items-center gap-2 py-1 <?php echo $es_lista_activa ? 'active text-white' : 'text-white-50'; ?>" style="font-size: 0.9rem;" href="lista.php?id=<?php echo $lista['id_lista']; ?>">
                                            <i class="bi bi-dot"></i>
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
                    <a class="nav-link <?php echo ($pagina_actual == 'completados.php') ? 'active text-white' : 'text-white-50'; ?> d-flex align-items-center gap-3 py-1" style="font-size: 0.9rem;" href="completados.php">
                        <i class="bi bi-check2-circle fs-5 <?php echo ($pagina_actual == 'completados.php') ? 'text-white' : 'text-success'; ?>"></i>
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
        
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-center mb-4 gap-3">
            
            <h2 class="text-white mb-0 d-flex align-items-center justify-content-center gap-3 text-center text-md-start">
                <i class="bi bi-folder2-open text-white-50"></i>
                <?php echo htmlspecialchars($lista_actual['nombre_lista']); ?>
            </h2>
            
            <div class="d-flex gap-2 flex-wrap justify-content-center">
                <button class="btn btn-outline-light d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAñadirExistentes">
                    <i class="bi bi-box-arrow-in-down"></i> Añadir tareas existentes
                </button>
                
                <button id="btn-mostrar-tarea" class="btn glass-btn-primary d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i> Nueva tarea
                </button>
            </div>
            
        </div>
        
        <hr class="text-white opacity-25 mb-4">

        <!-- === FORMULARIO OCULTO PARA AÑADIR NUEVA TAREA === -->
        <div id="formulario-tarea" class="d-none mt-3 p-3 rounded" style="background-color: rgba(0, 0, 0, 0.15); border: 1px solid rgba(255, 255, 255, 0.1);">
            <form action="acciones/crear_tarea.php" method="POST">
                <input type="text" name="nombre_tarea" class="form-control bg-transparent text-white border-secondary mb-3 shadow-none" placeholder="Escribe el nombre de la tarea..." required>
                <textarea name="descripcion_tarea" class="form-control bg-transparent text-white border-secondary mb-3 shadow-none" placeholder="Descripción (opcional)..." rows="2" style="resize: none;"></textarea>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-folder text-white-50"></i>
                        <select name="lista_id" class="form-select form-select-sm glass-form-element shadow-none" style="width: auto; cursor: pointer;">
                            <option value="<?php echo $id_lista_actual; ?>" selected>
                                <?php echo htmlspecialchars($lista_actual['nombre_lista']); ?>
                            </option>
                            <option value="0">Bandeja de entrada</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2 ms-auto">
                        <button type="button" id="btn-cancelar-tarea" class="btn btn-sm glass-form-element">Cancelar</button>
                        <button type="submit" class="btn btn-sm glass-btn-primary">Añadir tarea</button>
                    </div>
                    
                </div>
            </form>
        </div>
        <!-- ============================================ --> 

        <!-- Pega justo aquí tu bucle HTML de LISTA VERTICAL DE TAREAS que ya tienes, funciona sin modificarle nada -->
        <!-- === LISTA VERTICAL DE TAREAS === -->
            <div class="mt-4 mb-5">
                <?php if (!empty($tareas_usuario)): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($tareas_usuario as $tarea): ?>
                            <div class="glass-form-element p-3 rounded d-flex justify-content-between align-items-start">
                                
                                <div class="d-flex gap-3 flex-grow-1">
                                    <div class="mt-1">
                                        <a href="acciones/completar_tarea.php?id=<?php echo $tarea['id_tarea']; ?>" class="text-decoration-none" title="Marcar como completada">
                                            <i class="bi bi-circle text-white-50 fs-5" onmouseover="this.classList.remove('bi-circle'); this.classList.add('bi-check-circle', 'text-success');" onmouseout="this.classList.remove('bi-check-circle', 'text-success'); this.classList.add('bi-circle');" style="cursor: pointer; transition: all 0.2s;"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="d-flex flex-column gap-1 w-100">
                                        <h5 class="text-white mb-0" style="font-weight: 500; font-size: 1.1rem;">
                                            <?php echo htmlspecialchars($tarea['titulo']); ?>
                                        </h5>
                                        <?php if (!empty($tarea['descripcion'])): ?>
                                            <p class="text-white-50 mb-0 mt-1 small" style="line-height: 1.4;">
                                                <?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="dropdown ms-2">
                                    <button class="btn btn-sm text-white-50 p-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg" style="background-color: rgba(30, 20, 35, 0.95); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                                        <li>
                                            <button class="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalEditarTarea<?php echo $tarea['id_tarea']; ?>">
                                                <i class="bi bi-pencil-square text-info"></i> Editar tarea
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                                        <li>
                                            <button class="dropdown-item d-flex align-items-center gap-2 text-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarTarea<?php echo $tarea['id_tarea']; ?>">
                                                <i class="bi bi-trash"></i> Eliminar tarea
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                                
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-4 rounded text-center text-white-50" style="background-color: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2);">
                        <i class="bi bi-card-checklist fs-2 mb-2 d-block"></i>
                        Esta lista está vacía. ¡Añade una tarea para empezar!
                    </div>
                <?php endif; ?>
            </div>

    </div>
</div>

<!-- En tu Zona Segura de Modales al final del archivo, pon tus modales de Editar, Eliminar y Completar -->
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

                            <!-- === MODAL AÑADIR TAREAS EXISTENTES === -->
                            <div class="modal fade" id="modalAñadirExistentes" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content shadow-lg" style="background-color: rgba(40, 25, 45, 0.95); border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(15px);">
                                        
                                        <div class="modal-header border-bottom border-secondary border-opacity-25">
                                            <h5 class="modal-title text-white" style="font-weight: 500;">Añadir desde Bandeja de entrada</h5>
                                            <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                                        </div>
                                        
                                        <form action="acciones/mover_tareas_multiples.php" method="POST">
                                            <div class="modal-body">
                                                <!-- Enviamos el ID de la lista en la que estamos parados -->
                                                <input type="hidden" name="id_lista_destino" value="<?php echo $id_lista_actual; ?>">
                                                
                                                <?php if (!empty($tareas_inbox)): ?>
                                                    <p class="text-white-50 small mb-3">Selecciona las tareas que deseas mover a "<?php echo htmlspecialchars($lista_actual['nombre_lista']); ?>":</p>
                                                    
                                                    <div class="d-flex flex-column gap-2">
                                                        <?php foreach ($tareas_inbox as $t_inbox): ?>
                                                            <!-- Diseño tipo lista clickeable -->
                                                            <label class="glass-form-element p-2 rounded d-flex align-items-center gap-3 text-white m-0" style="cursor: pointer;">
                                                                <!-- El name="tareas_seleccionadas[]" con corchetes permite enviar múltiples IDs en un arreglo -->
                                                                <input class="form-check-input bg-transparent border-secondary shadow-none m-0" type="checkbox" name="tareas_seleccionadas[]" value="<?php echo $t_inbox['id_tarea']; ?>">
                                                                <span class="text-truncate"><?php echo htmlspecialchars($t_inbox['titulo']); ?></span>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center text-white-50 py-4">
                                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                                        No tienes tareas sueltas en tu Bandeja de entrada.
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="modal-footer border-top border-secondary border-opacity-25">
                                                <button type="button" class="btn btn-sm glass-form-element" data-bs-dismiss="modal">Cancelar</button>
                                                <!-- Si no hay tareas, el botón se deshabilita -->
                                                <button type="submit" class="btn btn-sm glass-btn-primary" <?php echo empty($tareas_inbox) ? 'disabled' : ''; ?>>Mover seleccionadas</button>
                                            </div>
                                        </form>
                                        
                                    </div>
                                </div>
                            </div>
                            <!-- ============================================== -->



                <!-- FIN DE ZONA DE MODALES -->
                <!-- ========================================================== -->


<script src="assets/js/mostrarFormularioTarea.js"></script>
<?php include 'includes/footer.php'; ?>