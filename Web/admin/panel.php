<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requerirLogin();
if (!esSuperAdmin()) {
  echo "Acceso denegado";
  exit;
}

$empresas = $pdo->query("SELECT * FROM empresas")->fetchAll();
$oficinas = $pdo->query("SELECT * FROM oficinas")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Panel Admin</title>
</head>
<body>
  <h1>Panel del Superadmin</h1>

  <!-- Formulario para crear nueva empresa -->
  <form action="empresas.php" method="POST">
    <h2>Nueva empresa</h2>
    <input type="text" name="nombre_empresa" required>
    <button>Crear</button>
  </form>

  <!-- Formulario para crear nueva oficina -->
  <form action="oficinas.php" method="POST">
    <h2>Nueva oficina</h2>
    <input type="text" name="nombre_oficina" required>
    <input type="text" name="ciudad" required>
    <button>Crear</button>
  </form>

  <!-- Formulario para crear nuevo usuario -->
  <form action="usuarios.php" method="POST">
    <h2>Nuevo usuario</h2>
    <input name="nombre" placeholder="Nombre" required>
    <input name="apellido_paterno" placeholder="Apellido paterno" required>
    <input name="apellido_materno" placeholder="Apellido materno">
    <input name="email" type="email" required placeholder="Email">
    <input name="password" type="password" required placeholder="Contraseña">

    <select name="empresa_id">
      <?php foreach ($empresas as $e): ?>
        <option value="<?= $e['id'] ?>">
          <?= htmlspecialchars($e['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select name="oficina_id">
      <?php foreach ($oficinas as $o): ?>
        <option value="<?= $o['id'] ?>">
          <?= htmlspecialchars($o['nombre']) ?> (<?= htmlspecialchars($o['ciudad']) ?>)
        </option>
      <?php endforeach; ?>
    </select>

    <input name="ciudad" placeholder="Ciudad" required>
    <label><input type="checkbox" name="es_admin"> Es admin</label>
    <button>Crear</button>
  </form>
</body>
</html>
