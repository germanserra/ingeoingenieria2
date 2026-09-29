<?php
require __DIR__ . '/includes/funciones.php';
$c = cargarContenido();
$hero = $c['hero'];
$servicios = $c['servicios'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($c['sitio']['titulo_pagina']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="css/estilos.css" rel="stylesheet">
</head>
<body>

<div class="px-4 pt-5 my-5 text-center border-bottom">
  <h1 class="display-4 fw-bold text-body-emphasis"><?= e($hero['titulo']) ?></h1>
  <div class="col-lg-6 mx-auto">
    <p class="lead mb-4"><?= nl2br(e($hero['descripcion'])) ?></p>
    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mb-5">
      <button type="button" class="btn btn-primary btn-lg px-4 me-sm-3"><?= e($hero['boton_principal']) ?></button>
      <button type="button" class="btn btn-outline-secondary btn-lg px-4"><?= e($hero['boton_secundario']) ?></button>
    </div>
  </div>
  <div class="overflow-hidden" style="max-height: 60vh;">
    <div class="container px-5">
      <img src="<?= e($hero['imagen']) ?>" class="img-fluid border rounded-3 shadow-lg mb-4" alt="<?= e($hero['imagen_alt']) ?>" width="700" height="500" loading="lazy">
    </div>
  </div>
</div>

<section class="container py-5">
  <h2 class="text-center mb-5"><?= e($c['servicios']['titulo']) ?></h2>
  <div class="row g-4">
    <?php foreach ($c['servicios']['items'] as $item): ?>
      <div class="col-md-4">
        <div class="p-3 border rounded h-100 bg-white shadow-sm">
          <!-- Icono del servicio (asumiendo que mantienes la ruta del icono) -->
          <?php if (!empty($item['icono'])): ?>
            <img src="<?= e($item['icono']) ?>" alt="" class="feature-icon bg-primary bg-opacity-10 p-2 mb-3">
          <?php endif; ?>
          
          <h3 class="h4 mb-3"><?= e($item['titulo']) ?></h3>
          
          <!-- Lista con viñetas -->
          <ul class="list-unstyled mb-3">
            <?php foreach ($item['puntos'] as $punto): ?>
              <li class="mb-1">• <?= e($punto) ?></li>
            <?php endforeach; ?>
          </ul>
          
          <!-- Enlace estilo "Saber más >" -->
          <?php if (!empty($item['enlace_texto'])): ?>
            <a href="<?= e($item['enlace_url']) ?>" class="text-decoration-none">
              <?= e($item['enlace_texto']) ?> &gt;
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<div id="carruselPrincipal" class="carousel slide" data-bs-ride="carousel">
  
  <!-- 1. Indicadores inferiores (rayitas o puntos) generados dinámicamente -->
  <div class="carousel-indicators">
    <?php foreach ($c['carrusel'] as $i => $slide): ?>
      <button type="button" data-bs-target="#carruselPrincipal" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?> aria-label="Slide <?= $i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>

  <!-- 2. Contenedor de las diapositivas (imágenes) -->
  <div class="carousel-inner">
    <?php foreach ($c['carrusel'] as $i => $slide): ?>
      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
        <img src="<?= e($slide['imagen']) ?>" class="d-block w-100" alt="<?= e($slide['alt']) ?>">
        <div class="carousel-caption d-none d-md-block">
          <h5><?= e($slide['alt']) ?></h5>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 3. Botones de navegación (Anterior y Siguiente) -->
  <button class="carousel-control-prev" type="button" data-bs-target="#carruselPrincipal" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Anterior</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#carruselPrincipal" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Siguiente</span>
  </button>
  
</div>
<footer class="bg-dark text-white py-5 mt-5">
  <div class="container">
    
    <!-- Sección de llamada a la acción superior -->
    <div class="mb-5">
      <h3 class="h4 fw-bold text-uppercase mb-2"><?= e($c['footer']['titulo_accion']) ?></h3>
      <p class="text-white-50"><?= e($c['footer']['subtitulo_accion']) ?></p>
    </div>

    <div class="row">
      <!-- Columna de datos de contacto (Izquierda) -->
      <div class="col-lg-8">
        <div class="row">
          
          <!-- Teléfonos -->
          <div class="col-md-6 mb-4">
            <h6 class="text-info fw-bold small text-uppercase mb-2">Teléfono</h6>
            <ul class="list-unstyled text-white-50">
              <?php foreach ($c['footer']['telefonos'] as $tel): ?>
                <li>• <?= e($tel) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- WhatsApp -->
          <div class="col-md-6 mb-4">
            <h6 class="text-info fw-bold small text-uppercase mb-2">WhatsApp</h6>
            <ul class="list-unstyled">
              <?php foreach ($c['footer']['whatsapp'] as $wa): ?>
                <li class="mb-1">
                  • <a href="<?= e($wa['url']) ?>" target="_blank" class="text-white-50 text-decoration-none"><?= e($wa['texto']) ?></a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Correo -->
          <div class="col-md-6 mb-4">
            <h6 class="text-info fw-bold small text-uppercase mb-2">Correo</h6>
            <ul class="list-unstyled">
              <?php foreach ($c['footer']['correos'] as $correo): ?>
                <li class="mb-1">
                  • <a href="mailto:<?= e($correo) ?>" class="text-white-50 text-decoration-none"><?= e($correo) ?></a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <!-- Ubicación -->
          <div class="col-md-6 mb-4">
            <h6 class="text-info fw-bold small text-uppercase mb-2">Ubicación</h6>
            <ul class="list-unstyled text-white-50">
              <li>• <?= e($c['footer']['ubicacion']) ?></li>
            </ul>
          </div>

        </div>
      </div>

      <!-- Columna de logotipo (Derecha) -->
      <div class="col-lg-4 text-lg-end text-start mb-4 d-flex align-items-start justify-content-lg-end justify-content-start">
        <?php if (!empty($c['footer']['logo'])): ?>
          <img src="<?= e($c['footer']['logo']) ?>" alt="InGeo Ingeniería" style="max-height: 150px;">
        <?php else: ?>
          <span class="fw-bold fs-5 text-white">InGeo Ingeniería</span>
        <?php endif; ?>
      </div>
    </div>

    <hr class="border-secondary my-4">

    <!-- Enlaces de categorías de servicios -->
    <div class="text-center text-white-50 small mb-3 text-uppercase">
      <span>Geotecnia</span> · 
      <span>Geofísica</span> · 
      <span>Topografía</span> · 
      <span>Construcción</span> · 
      <span>Mantenimiento</span>
    </div>

    <!-- Contenedor inferior con separación a los extremos -->
    <div class="d-flex justify-content-between align-items-center text-white-50 small pt-2 border-top border-secondary">
      
      <!-- Izquierda: Copyright -->
      <div>
        <p class="mb-0"><?= e($c['footer']['texto_legal']) ?></p>
      </div>

      <!-- Derecha: Enlace de Administración en la esquina exacta -->
      <div>
        <?php if (!empty($c['footer']['admin_url'])): ?>
          <a href="<?= e($c['footer']['admin_url']) ?>" class="text-white-50 text-decoration-none small opacity-75">
            🔒 <?= e($c['footer']['admin_texto'] ?? 'Administración') ?>
          </a>
        <?php endif; ?>
      </div>

    </div>
</footer>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>
