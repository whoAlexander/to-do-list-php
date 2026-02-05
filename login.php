<?php include 'includes/header.php'; ?>
<?php include 'includes/nav-simple.php'; ?>

<div class="card glass-card p-4" style="width: 450px; margin: auto;"> 
    
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
        <button type="submit" class="button-start w-50 mt-5 d-block mx-auto">Inicia Sesion</button>
    </form>
    
    <p class="mt-3 text-white text-center">
        ¿No tienes una cuenta? <a href="register.php" class="link-login">Registrarse</a>
    </p>

</div>

<?php include 'includes/footer.php'; ?>