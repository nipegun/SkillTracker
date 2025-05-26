<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) {
  echo "Acceso denegado";
  exit;
}

$empresas = $pdo->query("SELECT * FROM empresas")->fetchAll();
$oficinas = $pdo->query("SELECT * FROM oficinas")->fetchAll();

$tab = $_GET['tab'] ?? 'empresas';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Panel del admin</title>
  <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
  <div class="logout-link">
    <a href="logout.php">Cerrar sesión</a>
  </div>
  <h1>Panel del admin</h1>

  <div class="tabs">
    <a href="?tab=empresas" class="<?= $tab === 'empresas' ? 'active' : '' ?>">Empresas</a>
    <a href="?tab=oficinas" class="<?= $tab === 'oficinas' ? 'active' : '' ?>">Oficinas</a>
    <a href="?tab=usuarios" class="<?= $tab === 'usuarios' ? 'active' : '' ?>">Usuarios</a>
  </div>

  <div class="tab-content">
    <?php if ($tab === 'empresas'): ?>
      <h2>Nueva empresa</h2>
      <form action="empresas.php" method="POST">
        <input type="text" name="nombre_empresa" required>
        <button>Crear</button>
      </form>

    <?php elseif ($tab === 'oficinas'): ?>
      <h2>Nueva oficina</h2>
      <form action="oficinas.php" method="POST">
        <input type="text" name="nombre_oficina" required placeholder="Nombre de oficina">
        <input type="text" name="ciudad" required placeholder="Ciudad">
        <button>Crear</button>
      </form>

    <?php elseif ($tab === 'usuarios'): ?>
      <h2>Nuevo usuario</h2>
      <form action="usuarios.php" method="POST">
        <input name="nombre" placeholder="Nombre" required>
        <input name="apellido_paterno" placeholder="Apellido paterno" required>
        <input name="apellido_materno" placeholder="Apellido materno">
        <input name="email" type="email" required placeholder="Email">
        <input name="password" type="password" required placeholder="Contraseña">

        <select name="empresa_id" required>
          <?php foreach ($empresas as $e): ?>
            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
          <?php endforeach; ?>
        </select>

        <select name="oficina_id" required>
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

    <?php else: ?>
      <p>Pestaña no válida.</p>
    <?php endif; ?>
  </div>
</body>
</html>
