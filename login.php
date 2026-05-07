<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/nav-simple.php'; ?>

<style>
    body {
        display: block !important; 
    }
</style>

<div class="d-flex flex-column justify-content-center align-items-center px-3 w-100" style="min-height: 80vh;">
    
    <div class="card glass-card p-4 w-100 mb-4" style="max-width: 450px;">
        
        <h2 class="text-center mt-4 mb-4" style="font-family: 'Archivo Black', sans-serif; font-size: 2rem;">Iniciar Sesion</h2>

        <?php if (isset($_GET['mensaje'])): ?>
        
            <?php if ($_GET['mensaje'] == 'registrado'): ?>
                <div class="alert alert-glass-success text-center" role="alert">
                    ✅ ¡Cuenta creada! Ahora puedes ingresar.
                </div>
            
            <?php elseif ($_GET['mensaje'] == 'error_login'): ?>
                <div class="alert alert-glass-warning text-center" role="alert">
                    ⚠️ Usuario o contraseña incorrectos.
                </div>

            <?php endif; ?>
        <?php endif; ?>

        <form action="acciones/login_accion.php" method="POST">
            <div class="mb-3 text-start">
                <label class="form-label text-white">Nombre de Usuario</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label text-white">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="button-start mt-4 mb-2 d-block mx-auto px-4 text-nowrap" style="min-width: 200px;">Inicia Sesion</button>
        </form>
        
        <p class="mt-3 text-white text-center mx-2">
            ¿No tienes una cuenta? <a href="register.php" class="link-login">Registrarse</a>
        </p>
    </div>

    <div class="text-center">
        <p class="text-white-50" style="font-size: 0.95rem;">
            Hecho por <a href="https://github.com/whoAlexander" target="_blank" class="text-info text-decoration-none fw-bold">Alex</a>
        </p>
    </div>

</div>

<?php include 'includes/footer.php'; ?>