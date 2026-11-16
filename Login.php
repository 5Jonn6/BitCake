<?php
require 'config.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: cuenta.php');
    exit;
}

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $pass   = $_POST['password'] ?? '';

    // Límite simple de intentos por sesión
    $_SESSION['intentos'] = $_SESSION['intentos'] ?? 0;
    $_SESSION['bloqueo']  = $_SESSION['bloqueo'] ?? 0;

    if (!csrf_check()) {
        $error = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
    } elseif (time() < $_SESSION['bloqueo']) {
        $error = 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.';
    } else {
        $q = db()->prepare('SELECT * FROM usuarios WHERE correo = ?');
        $q->execute([$correo]);
        $u = $q->fetch();

        if ($u && password_verify($pass, $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$u['id'];
            $_SESSION['nombre'] = $u['nombre'];
            $_SESSION['intentos'] = 0;
            db()->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?')->execute([$u['id']]);
            header('Location: cuenta.php');
            exit;
        }
        $_SESSION['intentos']++;
        if ($_SESSION['intentos'] >= 5) {
            $_SESSION['bloqueo'] = time() + 60;
            $_SESSION['intentos'] = 0;
        }
        $error = 'Correo o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión | Bitcake</title>
  <link rel="stylesheet" href="index.css">
</head>
<body class="pagina-auth">
  <main class="auth-card">
    <a class="auth-logo" href="index.html">Bit<span>cake</span></a>
    <h1>Iniciar sesión</h1>
    <p class="auth-sub">Un bocado para cada antojo</p>

    <?php if (($_GET['registro'] ?? '') === 'ok'): ?>
      <div class="alerta alerta-ok"><p>Cuenta creada. Ya puedes iniciar sesión.</p></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alerta alerta-error"><p><?= e($error) ?></p></div>
    <?php endif; ?>

    <form method="post">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label>Correo electrónico<input type="email" name="correo" value="<?= e($correo) ?>" required autofocus></label>
      <label>Contraseña<input type="password" name="password" required></label>
      <button class="btn" type="submit">Entrar</button>
    </form>

    <p class="auth-pie">¿Aún no tienes cuenta? <a href="registro.php">Regístrate</a></p>
  </main>
</body>
</html>