<?php
require __DIR__ . '/../includes/funciones.php';
iniciarSesion();

if (!empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validarCsrf();
    $minutos = minutosBloqueo($ip);
    if ($minutos > 0) {
        $error = "Demasiados intentos fallidos. Espera $minutos minuto(s).";
    } else {
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        if (verificarUsuario($usuario, $password)) {
            registrarIntento($ip, true);
            session_regenerate_id(true);
            $_SESSION['admin'] = $usuario;
            $_SESSION['ultima_actividad'] = time();
            header('Location: index.php');
            exit;
        }
        registrarIntento($ip, false);
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>Acceso administrador</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link href="../css/estilos.css" rel="stylesheet">
</head>
<body class="admin-body d-flex align-items-center min-vh-100">
  <main class="card shadow-sm login-card w-100 mx-auto">
    <div class="card-body p-4">
      <h1 class="h4 mb-4 text-center">Panel de administración</h1>

      <?php if (isset($_GET['expirada'])): ?>
        <div class="alert alert-warning py-2">Tu sesión expiró. Vuelve a ingresar.</div>
      <?php endif; ?>
      <?php if (isset($_GET['salir'])): ?>
        <div class="alert alert-success py-2">Sesión cerrada.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <?= campoCsrf() ?>
        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="usuario" name="usuario" required autofocus>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
      </form>
      <p class="text-center mt-3 mb-0"><a href="../index.php" class="small">← Volver al sitio</a></p>
    </div>
  </main>
</body>
</html>
