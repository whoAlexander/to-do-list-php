    const btnMostrarLista = document.getElementById('btn-mostrar-lista');
    const btnCancelarLista = document.getElementById('btn-cancelar-lista');
    const formularioLista = document.getElementById('formulario-lista');
    const inputLista = formularioLista.querySelector('input[name="nombre_lista"]');

    
    btnMostrarLista.addEventListener('click', function() {
        formularioLista.classList.remove('d-none'); // Quitamos la clase que lo oculta
        btnMostrarLista.classList.add('d-none'); // Ocultamos el botón original temporalmente
        inputLista.focus(); // Ponemos el cursor automáticamente en el input
    });

    btnCancelarLista.addEventListener('click', function() {
        formularioLista.classList.add('d-none'); // Volvemos a ocultar el formulario
        btnMostrarLista.classList.remove('d-none'); // Volvemos a mostrar el botón original
        inputLista.value = ''; // Limpiamos lo que el usuario haya escrito
    });