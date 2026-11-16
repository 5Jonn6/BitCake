<?php
require 'config.php';

$errores = [];
$v = ['nombre'=>'','apellidos'=>'','correo'=>'','telefono'=>'','direccion'=>'','ciudad'=>'','codigo_postal'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errores[] = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
    }
    foreach ($v as $k => $_) {
        $v[$k] = trim($_POST[$k] ?? '');
    }
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($v['nombre'] === '' || $v['apellidos'] === '') $errores[] = 'Escribe tu nombre y apellidos.';
    if (!filter_var($v['correo'], FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo electrónico no válido.';
    if (!preg_match('/^[0-9+\s-]{8,20}$/', $v['telefono'])) $errores[] = 'Teléfono no válido (obligatorio para coordinar la entrega).';
    if (strlen($pass) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    if ($pass !== $pass2) $errores[] = 'Las contraseñas no coinciden.';
    if (empty($_POST['acepta'])) $errores[] = 'Debes aceptar el aviso de privacidad.';

    if (!$errores) {
        $q = db()->prepare('SELECT id FROM usuarios WHERE correo = ?');
        $q->execute([$v['correo']]);
        if ($q->fetch()) {
            $errores[] = 'Ya existe una cuenta con ese correo.';
        } else {
            $ins = db()->prepare(
                'INSERT INTO usuarios (nombre, apellidos, correo, telefono, direccion, ciudad, codigo_postal, password_hash, acepta_aviso)
                 VALUES (?,?,?,?,?,?,?,?,1)'
            );
            $ins->execute([
                $v['nombre'], $v['apellidos'], $v['correo'], $v['telefono'],
                $v['direccion'] ?: null, $v['ciudad'] ?: null, $v['codigo_postal'] ?: null,
                password_hash($pass, PASSWORD_DEFAULT),
            ]);
            header('Location: login.php?registro=ok');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear cuenta | Bitcake</title>
  <link rel="stylesheet" href="index.css">
</head>
<body class="pagina-auth">
  <main class="auth-card">
    <a class="auth-logo" href="index.html">Bit<span>cake</span></a>
    <h1>Crear cuenta</h1>
    <p class="auth-sub">Un bocado para cada antojo</p>

    <?php if ($errores): ?>
      <div class="alerta alerta-error">
        <?php foreach ($errores as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="fila">
        <label>Nombre<input name="nombre" value="<?= e($v['nombre']) ?>" required></label>
        <label>Apellidos<input name="apellidos" value="<?= e($v['apellidos']) ?>" required></label>
      </div>
      <label>Correo electrónico<input type="email" name="correo" value="<?= e($v['correo']) ?>" required></label>
      <label>Teléfono<input type="tel" name="telefono" value="<?= e($v['telefono']) ?>" required></label>
      <label>Dirección de entrega (opcional)<input name="direccion" value="<?= e($v['direccion']) ?>"></label>
      <div class="fila">
        <label>Ciudad<input name="ciudad" value="<?= e($v['ciudad']) ?>"></label>
        <label>Código postal<input name="codigo_postal" value="<?= e($v['codigo_postal']) ?>"></label>
      </div>
      <div class="fila">
        <label>Contraseña<input type="password" name="password" minlength="8" required></label>
        <label>Repite la contraseña<input type="password" name="password2" minlength="8" required></label>
      </div>
      <label class="check">
        <input type="checkbox" name="acepta" value="1">
        He leído y acepto el <a href="index.html#privacidad">aviso de privacidad</a>.
      </label>
      <button class="btn" type="submit">Crear cuenta</button>
    </form>

    <p class="auth-pie">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
  </main>
</body>
</html>