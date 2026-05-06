<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require 'config/conexion.php';
$usuario_id = $_SESSION['usuario_id'];

// 1. Traemos TODAS las listas para que la barra lateral funcione bien
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

// 2. Lógica del buscador
$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$resultados = [];

if (!empty($busqueda)) {
    // Los % son comodines de SQL para buscar la palabra en cualquier parte del título
    $busqueda_param = "%" . $busqueda . "%";

    // Buscamos en LISTAS
    $sql_buscar_listas = "SELECT nombre_lista AS titulo, 'Lista' AS tipo FROM listas WHERE usuario_id = ? AND nombre_lista LIKE ?";
    if ($stmt = $conexion->prepare($sql_buscar_listas)) {
        $stmt->bind_param("is", $usuario_id, $busqueda_param);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $resultados[] = $fila;
        }
        $stmt->close();
    }

    // Buscamos en TAREAS
    $sql_buscar_tareas = "SELECT titulo, 'Tarea' AS tipo FROM tareas WHERE usuario_id = ? AND titulo LIKE ?";
    if ($stmt = $conexion->prepare($sql_buscar_tareas)) {
        $stmt->bind_param("is", $usuario_id, $busqueda_param);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $resultados[] = $fila;
        }
        $stmt->close();
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    body, main {
        display: block !important; 
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
</style>

<div class="container-fluid px-0 w-100">
    <div class="d-flex" style="height: 100vh;"> 

        <!-- Pega aquí tu barra lateral HTML tal cual la tienes en los otros archivos -->
        <!-- (Si hiciste el includes/sidebar.php, solo pon el include aquí) -->

                <div class="glass-sidebar p-4 d-flex flex-column h-100" style="width: 280px;">
            
            <a href="index.php" class="text-white text-decoration-none mb-4 mt-2">
                <h3 style="font-family: 'Archivo Black', sans-serif; letter-spacing: 1px; text-align: center;">To Do List</h3>
            </a>
            
            <hr class="text-white opacity-25 mt-0 mb-2">

            <ul class="nav flex-column gap-3 mb-auto">
                <li class="nav-item">
                    <a class="nav-link active d-flex align-items-center gap-3 py-1 text-white-50" style="font-size: 0.9rem;" href="buscador.php">
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

        <!-- CONTENIDO PRINCIPAL -->
        <div class="flex-grow-1 p-5 overflow-auto">
            <div class="card glass-card p-5 mx-auto" style="width: 100%; max-width: 800px; text-align: left;">
                
                <h2 class="text-white mb-4 d-flex align-items-center gap-3">
                    <i class="bi bi-search text-white-50"></i> Buscador
                </h2>
                
                <!-- Formulario de Búsqueda -->
                <form action="buscador.php" method="GET" class="mb-5">
                    <div class="input-group input-group-lg shadow-sm">
                        <!-- value="<?php echo htmlspecialchars($busqueda); ?>" mantiene escrita tu búsqueda -->
                        <input type="text" name="q" class="form-control bg-transparent text-white border-secondary shadow-none glass-form-element px-4" placeholder="Escribe un título para buscar..." value="<?php echo htmlspecialchars($busqueda); ?>" autocomplete="off" autofocus>
                        <button class="btn glass-btn-primary px-4" type="submit">Buscar</button>
                    </div>
                </form>

                <!-- Zona de Resultados -->
                <?php if (isset($_GET['q'])): // Solo mostramos si ya se hizo una búsqueda ?>
                    
                    <h5 class="text-white-50 mb-4">Resultados para "<?php echo htmlspecialchars($busqueda); ?>":</h5>
                    
                    <?php if (empty($resultados)): ?>
                        <!-- Si no hay coincidencias -->
                        <div class="p-5 rounded text-center text-white-50" style="background-color: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2);">
                            <i class="bi bi-emoji-frown fs-1 mb-3 d-block"></i>
                            No encontramos ninguna tarea ni lista con ese nombre.
                        </div>
                    <?php else: ?>
                        <!-- Si hay resultados, los dibujamos -->
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($resultados as $item): ?>
                                
                                <!-- Tarjeta de resultado visual (NO clickeable) -->
                                <div class="glass-form-element p-3 px-4 rounded d-flex align-items-center gap-4">
                                    
                                    <!-- Icono dinámico según sea Tarea o Lista -->
                                    <?php if ($item['tipo'] == 'Lista'): ?>
                                        <i class="bi bi-folder2 text-info fs-3 opacity-75"></i>
                                    <?php else: ?>
                                        <i class="bi bi-check2-square text-success fs-3 opacity-75"></i>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex flex-column gap-1">
                                        <!-- Título -->
                                        <h5 class="text-white mb-0" style="font-weight: 500; font-size: 1.15rem;">
                                            <?php echo htmlspecialchars($item['titulo']); ?>
                                        </h5>
                                        <!-- Etiqueta (Badge) pequeñita abajo -->
                                        <div>
                                            <span class="badge rounded-pill" style="background-color: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); font-weight: normal; color: rgba(255,255,255,0.7); font-size: 0.75rem;">
                                                <?php echo $item['tipo']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                </div>
                                
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <!-- Pantalla inicial antes de buscar -->
                    <div class="text-center text-white-50 mt-5 pt-4 opacity-50">
                        <i class="bi bi-search" style="font-size: 4rem;"></i>
                        <p class="mt-3">Busca tus tareas o listas por nombre.</p>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>