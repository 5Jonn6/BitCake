<?php
require 'config.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$q = db()->prepare('SELECT nombre, apellidos, correo, telefono, direccion, ciudad, codigo_postal, creado_en FROM usuarios WHERE id = ?');
$q->execute([$_SESSION['usuario_id']]);
$u = $q->fetch();

if (!$u) { // usuario eliminado
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi cuenta | Bitcake</title>
  <link rel="stylesheet" href="index.css">
</head>
<body class="pagina-auth">
  <main class="auth-card">
    <a class="auth-logo" href="index.html">Bit<span>cake</span></a>
    <h1>Hola, <?= e($u['nombre']) ?> 👋</h1>
    <p class="auth-sub">Estos son los datos de tu cuenta</p>

    <dl class="datos">
      <dt>Nombre</dt><dd><?= e($u['nombre'] . ' ' . $u['apellidos']) ?></dd>
      <dt>Correo</dt><dd><?= e($u['correo']) ?></dd>
      <dt>Teléfono</dt><dd><?= e($u['telefono']) ?></dd>
      <dt>Dirección</dt><dd><?= e($u['direccion'] ?: '—') ?></dd>
      <dt>Ciudad / C.P.</dt><dd><?= e(trim(($u['ciudad'] ?? '') . ' ' . ($u['codigo_postal'] ?? '')) ?: '—') ?></dd>
      <dt>Cliente desde</dt><dd><?= e(date('d/m/Y', strtotime($u['creado_en']))) ?></dd>
    </dl>

    <a class="btn" href="index.html#tienda">Ir a la tienda</a>
    <a class="btn btn-sec" href="logout.php">Cerrar sesión</a>
  </main>
</body>
</html>