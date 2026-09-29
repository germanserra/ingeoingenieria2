<?php
/**
 * Funciones compartidas del sitio.
 *
 * Usuarios: users/admin.txt, una línea por usuario con el mismo formato que TheApes:
 *     usuario:hash_bcrypt:estado      (estado = approved para poder entrar)
 * Contenido: data/contenido.json, lo lee index.php y lo edita admin/index.php.
 */

define('RAIZ', dirname(__DIR__));
define('ARCHIVO_USUARIOS', RAIZ . '/users/admin.txt');
define('ARCHIVO_CONTENIDO', RAIZ . '/data/contenido.json');
define('ARCHIVO_INTENTOS', RAIZ . '/data/intentos.json');

define('MAX_INTENTOS', 5);          // intentos fallidos antes de bloquear
define('MINUTOS_BLOQUEO', 15);
define('MINUTOS_SESION', 30);       // inactividad máxima en el panel

/** Escapa texto para imprimirlo en HTML. */
function e($texto) {
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/* ------------------------------------------------------------------ */
/* Contenido                                                            */
/* ------------------------------------------------------------------ */

function cargarContenido() {
    $json = @file_get_contents(ARCHIVO_CONTENIDO);
    $datos = $json === false ? null : json_decode($json, true);
    if (!is_array($datos)) {
        http_response_code(500);
        exit('No se pudo leer data/contenido.json');
    }
    return $datos;
}

/** Guarda el contenido dejando un respaldo de la versión anterior. */
function guardarContenido(array $datos) {
    $json = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        return false;
    }
    if (file_exists(ARCHIVO_CONTENIDO)) {
        copy(ARCHIVO_CONTENIDO, RAIZ . '/data/contenido.bak.json');
    }
    return file_put_contents(ARCHIVO_CONTENIDO, $json . "\n", LOCK_EX) !== false;
}

/* ------------------------------------------------------------------ */
/* Sesión y autenticación                                               */
/* ------------------------------------------------------------------ */

function iniciarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
    }
}

/** Busca el usuario en users/admin.txt y valida la contraseña (igual que TheApes). */
function verificarUsuario($usuario, $password) {
    $lineas = @file(ARCHIVO_USUARIOS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lineas) {
        return false;
    }
    foreach ($lineas as $linea) {
        $partes = explode(':', trim($linea));
        if (count($partes) < 3) {
            continue;
        }
        list($u, $hash, $estado) = $partes;
        if (hash_equals($u, $usuario)) {
            return $estado === 'approved' && password_verify($password, $hash);
        }
    }
    // Mismo costo de tiempo aunque el usuario no exista
    password_verify($password, '$2y$12$wqMB0XONjN8u0ptLMRYXmej70m6H67J0yoyLJLLL9pUQ.ygDbglcm');
    return false;
}

/** Reemplaza el hash de la contraseña de un usuario en users/admin.txt. */
function cambiarPassword($usuario, $nuevaPassword) {
    $lineas = @file(ARCHIVO_USUARIOS, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lineas) {
        return false;
    }
    $encontrado = false;
    foreach ($lineas as $i => $linea) {
        $partes = explode(':', trim($linea));
        if (count($partes) >= 3 && $partes[0] === $usuario) {
            $partes[1] = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $lineas[$i] = implode(':', $partes);
            $encontrado = true;
        }
    }
    return $encontrado && file_put_contents(ARCHIVO_USUARIOS, implode("\n", $lineas) . "\n", LOCK_EX) !== false;
}

/** Redirige al login si no hay sesión de admin válida. */
function requerirAdmin() {
    iniciarSesion();
    $vencida = isset($_SESSION['ultima_actividad'])
        && time() - $_SESSION['ultima_actividad'] > MINUTOS_SESION * 60;
    if (empty($_SESSION['admin']) || $vencida) {
        $_SESSION = [];
        header('Location: login.php' . ($vencida ? '?expirada=1' : ''));
        exit;
    }
    $_SESSION['ultima_actividad'] = time();
}

/* ------------------------------------------------------------------ */
/* CSRF                                                                 */
/* ------------------------------------------------------------------ */

function tokenCsrf() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function campoCsrf() {
    return '<input type="hidden" name="csrf" value="' . e(tokenCsrf()) . '">';
}

function validarCsrf() {
    if (empty($_POST['csrf']) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(400);
        exit('Solicitud no válida. Recarga la página e inténtalo de nuevo.');
    }
}

/* ------------------------------------------------------------------ */
/* Límite de intentos de login por IP                                   */
/* ------------------------------------------------------------------ */

function leerIntentos() {
    $datos = json_decode((string) @file_get_contents(ARCHIVO_INTENTOS), true);
    return is_array($datos) ? $datos : [];
}

/** Devuelve los minutos que faltan de bloqueo para la IP, o 0 si puede intentar. */
function minutosBloqueo($ip) {
    $intentos = leerIntentos();
    if (empty($intentos[$ip]) || $intentos[$ip]['n'] < MAX_INTENTOS) {
        return 0;
    }
    $restante = $intentos[$ip]['t'] + MINUTOS_BLOQUEO * 60 - time();
    return $restante > 0 ? (int) ceil($restante / 60) : 0;
}

function registrarIntento($ip, $exitoso) {
    $intentos = leerIntentos();
    if ($exitoso) {
        unset($intentos[$ip]);
    } else {
        $previo = $intentos[$ip] ?? ['n' => 0, 't' => time()];
        // Si ya pasó el bloqueo, se reinicia el conteo
        if (time() - $previo['t'] > MINUTOS_BLOQUEO * 60) {
            $previo['n'] = 0;
        }
        $intentos[$ip] = ['n' => $previo['n'] + 1, 't' => time()];
    }
    file_put_contents(ARCHIVO_INTENTOS, json_encode($intentos), LOCK_EX);
}
