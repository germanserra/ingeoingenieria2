// Panel de administración: contador de caracteres y aviso de cambios sin guardar.
(function () {
  var form = document.getElementById('form-contenido');
  if (!form) return;

  // Contador bajo cada textarea
  form.querySelectorAll('textarea[data-contador]').forEach(function (area) {
    var contador = document.createElement('div');
    contador.className = 'contador text-body-secondary text-end mt-1';
    area.after(contador);
    var actualizar = function () {
      contador.textContent = area.value.length + ' / ' + area.maxLength + ' caracteres';
    };
    area.addEventListener('input', actualizar);
    actualizar();
  });

  // Avisar si se intenta salir con cambios sin guardar
  var sinGuardar = false;
  form.addEventListener('input', function () { sinGuardar = true; });
  form.addEventListener('submit', function () { sinGuardar = false; });
  window.addEventListener('beforeunload', function (ev) {
    if (sinGuardar) {
      ev.preventDefault();
      ev.returnValue = '';
    }
  });
})();
