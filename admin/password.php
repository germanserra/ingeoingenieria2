<?php
require __DIR__ . '/../includes/funciones.php';
requerirAdmin();

$error = '';
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $actual = $_POST['actual'] ?? '';
    $nueva = $_POST['nueva'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';

    if (!verificarUsuario($_SESSION['admin'], $actual)) {
        $error = 'La contraseña actual no es correcta.';
    } elseif (strlen($nueva) < 8) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $error = 'La confirmación no coincide.';
    } elseif (!cambiarPassword($_SESSION['admin'], $nueva)) {
        $error = 'No se pudo escribir users/admin.txt. Revisa los permisos del archivo.';
    } else {
        $ok = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>Cambiar contraseña</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="../css/estilos.css" rel="stylesheet">
</head>
<body class="admin-body d-flex align-items-center min-vh-100">
  <main class="card shadow-sm login-card w-100 mx-auto">
    <div class="card-body p-4">
      <h1 class="h4 mb-4 text-center">Cambiar contraseña</h1>

      <?php if ($ok): ?>
        <div class="alert alert-success py-2">Contraseña actualizada.</div>
      <?php elseif ($error): ?>
        <div class="alert alert-danger py-2"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <?= campoCsrf() ?>
        <div class="mb-3">
          <label for="actual" class="form-label">Contraseña actual</label>
          <input type="password" class="form-control" id="actual" name="actual" required>
        </div>
        <div class="mb-3">
          <label for="nueva" class="form-label">Nueva contraseña</label>
          <input type="password" class="form-control" id="nueva" name="nueva" minlength="8" required>
        </div>
        <div class="mb-4">
          <label for="confirmar" class="form-label">Confirmar nueva contraseña</label>
          <input type="password" class="form-control" id="confirmar" name="confirmar" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Guardar</button>
      </form>
      <p class="text-center mt-3 mb-0"><a href="index.php" class="small">← Volver al editor</a></p>
    </div>
  </main>
</body>
</html>
