<?php
require __DIR__ . '/../includes/funciones.php';
requerirAdmin();

// Claves del JSON que no se editan desde el panel (rutas de archivos)
const CLAVES_FIJAS = ['imagen', 'icono'];
const MAX_CARACTERES = 3000;

/**
 * Copia sobre $original solo los textos que vienen en $post y que ya existen
 * en el JSON. Así el formulario no puede agregar claves nuevas ni cambiar rutas.
 */
function aplicarTextos(array $original, $post) {
    foreach ($original as $clave => $valor) {
        if (!is_array($post) || !array_key_exists($clave, $post)) {
            continue;
        }
        if (is_array($valor)) {
            $original[$clave] = aplicarTextos($valor, $post[$clave]);
        } elseif (is_string($valor) && is_string($post[$clave]) && !in_array($clave, CLAVES_FIJAS, true)) {
            $texto = str_replace("\r\n", "\n", trim($post[$clave]));
            // Recorta a MAX_CARACTERES sin partir caracteres UTF-8 (no requiere mbstring)
            // Si el texto no es UTF-8 válido, se conserva el valor anterior
            if (preg_match('/^.{0,' . MAX_CARACTERES . '}/us', $texto, $m)) {
                $original[$clave] = $m[0];
            }
        }
    }
    return $original;
}

/** Dibuja un campo de texto; $nombre es la ruta dentro del JSON, p. ej. hero[titulo]. */
function campo($etiqueta, $nombre, $valor, $multilinea = false) {
    $id = preg_replace('/[^a-z0-9]+/i', '_', $nombre);
    echo '<div class="mb-3">';
    echo '<label class="form-label fw-semibold" for="' . e($id) . '">' . e($etiqueta) . '</label>';
    if ($multilinea) {
        echo '<textarea class="form-control" rows="5" id="' . e($id) . '" name="' . e($nombre) . '" maxlength="' . MAX_CARACTERES . '" data-contador>' . e($valor) . '</textarea>';
    } else {
        echo '<input type="text" class="form-control" id="' . e($id) . '" name="' . e($nombre) . '" value="' . e($valor) . '" maxlength="' . MAX_CARACTERES . '">';
    }
    echo '</div>';
}

$c = cargarContenido();
$mensaje = '';
$tipoMensaje = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $nuevo = aplicarTextos($c, $_POST['c'] ?? []);
    if (guardarContenido($nuevo)) {
        $c = $nuevo;
        $mensaje = 'Cambios guardados. Ya se ven en el sitio.';
    } else {
        $mensaje = 'No se pudo escribir data/contenido.json. Revisa los permisos del archivo.';
        $tipoMensaje = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>Editar contenido</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="../css/estilos.css" rel="stylesheet">
</head>
<body class="admin-body">

<nav class="navbar bg-dark navbar-dark sticky-top">
  <div class="container">
    <span class="navbar-brand">Editor de contenido</span>
    <div class="d-flex align-items-center gap-2">
      <span class="text-white-50 small d-none d-sm-inline"><?= e($_SESSION['admin']) ?></span>
      <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-light">Ver sitio</a>
      <a href="password.php" class="btn btn-sm btn-outline-light">Contraseña</a>
      <form method="post" action="logout.php" class="m-0">
        <?= campoCsrf() ?>
        <button type="submit" class="btn btn-sm btn-light">Salir</button>
      </form>
    </div>
  </div>
</nav>

<main class="container py-4">
  <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show">
      <?= e($mensaje) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <form method="post" id="form-contenido">
    <?= campoCsrf() ?>

    <div class="card mb-4">
      <div class="card-header fw-bold">General</div>
      <div class="card-body">
        <?php campo('Título de la pestaña del navegador', 'c[sitio][titulo_pagina]', $c['sitio']['titulo_pagina']); ?>
        <?php campo('Texto del pie de página', 'c[footer][texto]', $c['footer']['texto']); ?>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header fw-bold">Encabezado principal</div>
      <div class="card-body">
        <?php campo('Título', 'c[hero][titulo]', $c['hero']['titulo']); ?>
        <?php campo('Descripción', 'c[hero][descripcion]', $c['hero']['descripcion'], true); ?>
        <div class="row">
          <div class="col-md-6"><?php campo('Botón principal', 'c[hero][boton_principal]', $c['hero']['boton_principal']); ?></div>
          <div class="col-md-6"><?php campo('Botón secundario', 'c[hero][boton_secundario]', $c['hero']['boton_secundario']); ?></div>
        </div>
        <?php campo('Descripción de la imagen (texto alternativo)', 'c[hero][imagen_alt]', $c['hero']['imagen_alt']); ?>
      </div>
    </div>

   <div class="card mb-4">
      <div class="card-header fw-bold">Servicios</div>
      <div class="card-body">
        <?php campo('Título de la sección', 'c[servicios][titulo]', $c['servicios']['titulo']); ?>
        <div class="row">
          <?php foreach ($c['servicios']['items'] as $i => $item): ?>
          <div class="col-lg-4 mb-4">
            <div class="border rounded p-3 h-100 bg-white">
              <h3 class="h6 text-body-secondary mb-3">Servicio <?= $i + 1 ?></h3>
              <?php campo('Título', "c[servicios][items][$i][titulo]", $item['titulo']); ?>
              
              <!-- Edición de los puntos de la lista -->
              <div class="mb-3">
                <label class="form-label fw-semibold">Puntos de la lista</label>
                <?php foreach ($item['puntos'] as $j => $punto): ?>
                  <input type="text" class="form-control mb-2" name="c[servicios][items][<?= $i ?>][puntos][<?= $j ?>]" value="<?= e($punto) ?>">
                <?php endforeach; ?>
              </div>

              <?php campo('Texto del enlace', "c[servicios][items][$i][enlace_texto]", $item['enlace_texto']); ?>
              <?php campo('URL del enlace', "c[servicios][items][$i][enlace_url]", $item['enlace_url']); ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

   <div class="card mb-4">
      <div class="card-header fw-bold">Carrusel</div>
      <div class="card-body">
        <?php foreach ($c['carrusel'] as $i => $slide): ?>
          <?php campo('Imagen ' . ($i + 1) . ' (' . $slide['imagen'] . ') — texto alternativo', "c[carrusel][$i][alt]", $slide['alt']); ?>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card mb-4">
      <div class="card-header fw-bold">Pie de página (Footer)</div>
      <div class="card-body">
        <?php campo('Título de llamada a la acción', 'c[footer][titulo_accion]', $c['footer']['titulo_accion']); ?>
        <?php campo('Subtítulo de llamada a la acción', 'c[footer][subtitulo_accion]', $c['footer']['subtitulo_accion'], true); ?>
        <?php campo('Ubicación', 'c[footer][ubicacion]', $c['footer']['ubicacion']); ?>
        <?php campo('Texto legal inferior', 'c[footer][texto_legal]', $c['footer']['texto_legal']); ?>
      </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
      <a href="index.php" class="btn btn-outline-secondary">Descartar cambios</a>
      <button type="submit" class="btn btn-primary px-4">Guardar cambios</button>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="../js/admin.js"></script>
  </form>
</main>


</body>
</html>
