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
        WHERE t.usuario_id = ? AND t.estado = 0 
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
                    <a class="nav-link active d-flex align-items-center gap-2 py-1" style="font-size: 0.9rem;" href="dashboard.php">
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


        <!-- contenido central -->
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
                                
                                <!-- Opción por defecto (Bandeja de entrada), su valor es 0 -->
                                <option value="0">Añadir a Lista</option>
                                
                                <!-- Imprimimos las listas reales del usuario -->
                                <?php foreach ($listas_usuario as $lista): ?>
                                    <option value="<?php echo $lista['id_lista']; ?>">
                                        <?php echo htmlspecialchars($lista['nombre_lista']); ?>
                                    </option>
                                <?php endforeach; ?>
                                
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

                <!-- === LISTA VERTICAL DE TAREAS === -->
                <div class="mt-4 mb-5">
                    <?php if (!empty($tareas_usuario)): ?>
                        <div class="d-flex flex-column gap-3">
                            
                            <?php foreach ($tareas_usuario as $tarea): ?>
                                <!-- Tarjeta horizontal para la tarea -->
                                <div class="glass-form-element p-3 rounded d-flex justify-content-between align-items-start">
                                    
                                    <!-- Lado Izquierdo: Checkbox, Título, Badge y Descripción -->
                                    <div class="d-flex gap-3 flex-grow-1">
                                        
                                        <!-- Checkbox interactivo -->
                                        <div class="mt-1">
                                            <a href="acciones/completar_tarea.php?id=<?php echo $tarea['id_tarea']; ?>" class="text-decoration-none" title="Marcar como completada">
                                                <i class="bi bi-circle text-white-50 fs-5" onmouseover="this.classList.remove('bi-circle'); this.classList.add('bi-check-circle', 'text-success');" onmouseout="this.classList.remove('bi-check-circle', 'text-success'); this.classList.add('bi-circle');" style="cursor: pointer; transition: all 0.2s;"></i>
                                            </a>
                                        </div>
                                        
                                        <div class="d-flex flex-column gap-1 w-100">
                                            <!-- Fila del título y la píldora -->
                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                <h5 class="text-white mb-0" style="font-weight: 500; font-size: 1.1rem;">
                                                    <?php echo htmlspecialchars($tarea['titulo']); ?>
                                                </h5>
                                                
                                                <!-- Badge de la lista -->
                                                <?php if (!empty($tarea['nombre_lista'])): ?>
                                                    <span class="badge rounded-pill" style="background-color: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); font-weight: normal;">
                                                        <i class="bi bi-folder2 text-white-50 me-1"></i> <?php echo htmlspecialchars($tarea['nombre_lista']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill" style="background-color: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1); font-weight: normal; color: rgba(255,255,255,0.7);">
                                                        <i class="bi bi-inbox me-1"></i> Bandeja de entrada
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Descripción (si existe) -->
                                            <?php if (!empty($tarea['descripcion'])): ?>
                                                <!-- nl2br respeta los saltos de línea que el usuario hizo con Enter -->
                                                <p class="text-white-50 mb-0 mt-1 small" style="line-height: 1.4;">
                                                    <?php echo nl2br(htmlspecialchars($tarea['descripcion'])); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Lado Derecho: Menú de 3 puntitos -->
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
                        <!-- Estado Vacío -->
                        <div class="p-4 rounded text-center text-white-50" style="background-color: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2);">
                            <i class="bi bi-check2-square fs-2 mb-2 d-block"></i>
                            Aún no tienes tareas pendientes. ¡Añade una nueva tarea para empezar!
                        </div>
                    <?php endif; ?>
                </div>
                <!-- ========================================== -->

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

                <!-- === MOSTRAR LAS LISTAS EN FORMATO TARJETAS DE CRISTAL === -->
                <div class="row mt-4">
                    <?php if (!empty($listas_usuario)): ?>
                        
                <?php foreach ($listas_usuario as $lista): ?>
                    <div class="col-md-4 col-sm-6 mb-3">
                        
                        <div class="glass-form-element d-flex justify-content-between align-items-center p-3 rounded w-100 h-100">
                            
                            <a href="lista.php?id=<?php echo $lista['id_lista']; ?>" class="d-flex align-items-center text-decoration-none flex-grow-1 text-truncate" style="color: inherit;">
                                <i class="bi bi-folder2-open text-white-50 fs-4 me-3"></i>
                                <span class="text-white text-truncate" style="font-weight: 500;">
                                    <?php echo htmlspecialchars($lista['nombre_lista']); ?>
                                </span>
                            </a>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm text-white-50 p-0 shadow-none" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                </button>
                                
                                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg" style="background-color: rgba(30, 20, 35, 0.95); border: 1px solid rgba(255,255,255,0.1); backdrop-filter: blur(10px);">
                                    <li>
                                        <!-- CAMBIO AQUÍ: Ahora es un botón que dispara el Modal específico de esta lista -->
                                        <button class="dropdown-item d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalEditarLista<?php echo $lista['id_lista']; ?>">
                                            <i class="bi bi-pencil-square text-info"></i> Editar nombre
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider border-secondary opacity-25"></li>

                                    <li>
                                        <!-- Ahora es un botón que dispara el Modal de Eliminar -->
                                        <button class="dropdown-item d-flex align-items-center gap-2 text-danger" data-bs-toggle="modal" data-bs-target="#modalEliminarLista<?php echo $lista['id_lista']; ?>">
                                            <i class="bi bi-trash"></i> Eliminar lista
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            
                    <?php else: ?>
                        <!-- Diseño de "Estado vacío" -->
                        <div class="col-12">
                            <div class="p-4 rounded text-center text-white-50" style="background-color: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2);">
                                <i class="bi bi-inbox fs-2 mb-2 d-block"></i>
                                Aún no tienes listas. ¡Crea una para empezar a organizar tus tareas!
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- ========================================================== -->
                <hr class="text-white opacity-25 mb-4">
            </div>
        </div>

    </div>
</div>

                <!-- ========================================================== -->
                <!-- ZONA SEGURA DE MODALES (Fuera de los contenedores flex/overflow) -->
                <?php if (!empty($listas_usuario)): ?>
                    <?php foreach ($listas_usuario as $lista): ?>
                        
                        <!-- Modal para editar la lista ID: <?php echo $lista['id_lista']; ?> -->
                        <div class="modal fade" id="modalEditarLista<?php echo $lista['id_lista']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                
                                <div class="modal-content shadow-lg" style="background-color: rgba(40, 25, 45, 0.95); border: 1px solid rgba(255,255,255,0.15); backdrop-filter: blur(15px);">
                                    
                                    <div class="modal-header border-bottom border-secondary border-opacity-25">
                                        <h5 class="modal-title text-white" style="font-weight: 500;">Editar Lista</h5>
                                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    
                                    <form action="acciones/editar_lista.php" method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_lista" value="<?php echo $lista['id_lista']; ?>">
                                            
                                            <label class="form-label text-white-50 small">Nombre de la lista</label>
                                            <input type="text" name="nuevo_nombre" class="form-control bg-transparent text-white border-secondary shadow-none glass-form-element" value="<?php echo htmlspecialchars($lista['nombre_lista']); ?>" required>
                                        </div>
                                        
                                        <div class="modal-footer border-top border-secondary border-opacity-25">
                                            <button type="button" class="btn btn-sm glass-form-element" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-sm glass-btn-primary">Guardar cambios</button>
                                        </div>
                                    </form>
                                    
                                </div>
                            </div>
                        </div>


                        <!-- === MODAL PARA ELIMINAR LISTA === -->
                                <div class="modal fade" id="modalEliminarLista<?php echo $lista['id_lista']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        
                                        <div class="modal-content shadow-lg" style="background-color: rgba(45, 20, 25, 0.95); border: 1px solid rgba(220, 53, 69, 0.3); backdrop-filter: blur(15px);">
                                            
                                            <div class="modal-header border-bottom border-danger border-opacity-25">
                                                <h5 class="modal-title text-danger" style="font-weight: 500;">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Eliminar Lista
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            
                                            <div class="modal-body">
                                                <p class="text-white mb-2">¿Estás seguro de que deseas eliminar la lista <strong>"<?php echo htmlspecialchars($lista['nombre_lista']); ?>"</strong>?</p>
                                                <p class="text-white-50 small mb-0">
                                                    <i class="bi bi-info-circle me-1"></i>Las tareas dentro de esta lista no se borrarán, volverán a la Bandeja de entrada.
                                                </p>
                                            </div>
                                            
                                            <div class="modal-footer border-top border-danger border-opacity-25">
                                                <button type="button" class="btn btn-sm glass-form-element" data-bs-dismiss="modal">Cancelar</button>
                                                <!-- Este es el enlace real que ejecuta el borrado -->
                                                <a href="acciones/borrar_lista.php?id=<?php echo $lista['id_lista']; ?>" class="btn btn-sm border-0 text-white" style="background-color: rgba(220, 53, 69, 0.8);">Sí, eliminar lista</a>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                                <!-- ============================================== -->

                                <!-- ========================================================== -->
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



                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- FIN DE ZONA DE MODALES -->
                <!-- ========================================================== -->


<script src="assets/js/mostrarFormularioTarea.js"></script>
<script src="assets/js/mostrarFormularioLista.js"></script>
<?php include 'includes/footer.php'; ?>