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
$usuarios = $pdo->query("
  SELECT u.*, e.nombre AS empresa, o.nombre AS oficina, o.ciudad AS oficina_ciudad
  FROM usuarios u
  JOIN empresas e ON u.empresa_id = e.id
  JOIN oficinas o ON u.oficina_id = o.id
")->fetchAll();
$habilidades = $pdo->query("SELECT * FROM habilidades ORDER BY id ASC")->fetchAll();

$tab = $_GET['tab'] ?? 'empresas';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Panel del admin</title>
  <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>
  <div class="top-bar">
    <img src="/images/SkillTrackerLogo.png" alt="Logo SkillTracker">
    <a href="logout.php" class="logout-button">Cerrar sesión</a>
  </div>

  <div class="main-content">
    <h1>Panel del admin</h1>

    <div class="tabs">
      <a href="?tab=empresas" class="<?= $tab === 'empresas' ? 'active' : '' ?>">Empresas</a>
      <a href="?tab=oficinas" class="<?= $tab === 'oficinas' ? 'active' : '' ?>">Oficinas</a>
      <a href="?tab=usuarios" class="<?= $tab === 'usuarios' ? 'active' : '' ?>">Usuarios</a>
      <a href="?tab=habilidades" class="<?= $tab === 'habilidades' ? 'active' : '' ?>">Habilidades</a>
    </div>

    <div class="tab-content">
      <?php if ($tab === 'empresas'): ?>
        <h2>Nueva empresa</h2>
        <form action="empresas.php" method="POST">
          <input type="text" name="nombre_empresa" required>
          <button>Crear</button>
        </form>

        <h2>Empresas registradas</h2>
        <table>
          <tr><th>ID</th><th>Nombre</th></tr>
          <?php foreach ($empresas as $e): ?>
            <tr>
              <td><?= htmlspecialchars($e['id']) ?></td>
              <td><?= htmlspecialchars($e['nombre']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>

      <?php elseif ($tab === 'oficinas'): ?>
        <h2>Nueva oficina</h2>
        <form action="oficinas.php" method="POST">
          <input type="text" name="nombre_oficina" required placeholder="Nombre de oficina">
          <input type="text" name="ciudad" required placeholder="Ciudad">
          <button>Crear</button>
        </form>

        <h2>Oficinas registradas</h2>
        <table>
          <tr><th>ID</th><th>Nombre</th><th>Ciudad</th></tr>
          <?php foreach ($oficinas as $o): ?>
            <tr>
              <td><?= htmlspecialchars($o['id']) ?></td>
              <td><?= htmlspecialchars($o['nombre']) ?></td>
              <td><?= htmlspecialchars($o['ciudad']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>

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

        <h2>Usuarios registrados</h2>
        <table>
          <tr>
            <th>ID</th>
            <th>Nombre completo</th>
            <th>Email</th>
            <th>Empresa</th>
            <th>Oficina</th>
            <th>Ciudad</th>
            <th>Es admin</th>
          </tr>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['id']) ?></td>
              <td><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido_paterno'] . ' ' . $u['apellido_materno']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= htmlspecialchars($u['empresa']) ?></td>
              <td><?= htmlspecialchars($u['oficina']) ?></td>
              <td><?= htmlspecialchars($u['ciudad']) ?></td>
              <td><?= $u['es_admin'] ? 'Sí' : 'No' ?></td>
            </tr>
          <?php endforeach; ?>
        </table>

      <?php elseif ($tab === 'habilidades'): ?>
        <h2>Nueva habilidad</h2>
          <form action="habilidades.php" method="POST">
            <input type="text" name="nombre_habilidad" required placeholder="Nombre de la habilidad">
            <button>Crear</button>
          </form>

        <h2>Habilidades registradas</h2>
        <table>
          <tr><th>ID</th><th>Nombre</th></tr>
          <?php foreach ($habilidades as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['id']) ?></td>
            <td><?= htmlspecialchars($h['nombre']) ?></td>
          </tr>
          <?php endforeach; ?>
        </table>

      <?php else: ?>
        <p>Pestaña no válida.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
