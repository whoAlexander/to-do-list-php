<?php require 'config/conexion.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/nav-simple.php'; ?>
 
<div class="card glass-card p-4" style="width: 450px; margin: auto;"> 
    
    <h2 class="text-center mt-4 mb-4" style="font-family: 'Archivo Black', sans-serif; font-size: 2rem;">Crear Cuenta</h2>

    <?php if (isset($_GET['mensaje'])): ?>
    
    <?php if ($_GET['mensaje'] == 'duplicado'): ?>
        <div class="alert alert-glass-warning text-center" role="alert">
            ⚠️ Ese usuario o correo ya está registrado.
        </div>
    
    <?php elseif ($_GET['mensaje'] == 'error'): ?>
        <div class="alert alert-glass-warning text-center" role="alert">
            ❌ Ocurrió un error inesperado. Intenta de nuevo.
        </div>
        
    <?php endif; ?>
    <?php endif; ?>

    <form action="acciones/register_accion.php" method="POST">
        <div class="mb-3 text-start">
            <label class="form-label text-white">Nombre de Usuario</label>
            <input type="text" name="username" class="form-control" required placeholder="Juan Perez">
        </div>

        <div class="mb-3 text-start">
            <label class="form-label text-white">Correo Electrónico</label>
            <input type="email" name="email" class="form-control" required placeholder="juan@perez.com">
        </div>

        <div class="mb-3 text-start">
            <label class="form-label text-white">Contraseña</label>
            <input type="password" name="password" class="form-control" required placeholder="******">
        </div>
        <button type="submit" class="button-start w-50 mt-5 d-block mx-auto">Registrarse</button>
    </form>
    
    <p class="mt-3 text-white text-center">
        ¿Ya tienes cuenta? <a href="login.php" class="link-login">Inicia Sesión</a>
    </p>

</div>

<?php include 'includes/footer.php'; ?>