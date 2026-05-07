    const btnMostrar = document.getElementById('btn-mostrar-tarea');
    const btnCancelar = document.getElementById('btn-cancelar-tarea');
    const formulario = document.getElementById('formulario-tarea');
    const inputTarea = formulario.querySelector('input[name="nombre_tarea"]');
    const inputDescripcion = formulario.querySelector('textarea[name="descripcion_tarea"]');
    
    btnMostrar.addEventListener('click', function() {
        formulario.classList.remove('d-none'); // Quitamos la clase que lo oculta
        btnMostrar.classList.add('d-none'); // Ocultamos el botón original temporalmente
        inputTarea.focus(); // Ponemos el cursor automáticamente en el input
    });

    btnCancelar.addEventListener('click', function() {
        formulario.classList.add('d-none'); // Volvemos a ocultar el formulario
        btnMostrar.classList.remove('d-none'); // Volvemos a mostrar el botón original
        inputTarea.value = ''; // Limpiamos lo que el usuario haya escrito
        inputDescripcion.value = '';
    });