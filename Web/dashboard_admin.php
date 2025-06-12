<?php

#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
#error_reporting(E_ALL);

require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) {
  echo "Acceso denegado";
  exit;
}

$grupos = $pdo->query(
  "SELECT g.*, GROUP_CONCAT(e.nombre ORDER BY e.id ASC SEPARATOR ', ') AS empresas
   FROM grupos g
   LEFT JOIN empresas e ON e.grupo_id = g.id
   GROUP BY g.id
   ORDER BY g.id ASC"
)->fetchAll();
$empresas = $pdo->query(
  "SELECT empresas.*, grupos.nombre AS grupo_nombre
   FROM empresas
   LEFT JOIN grupos ON empresas.grupo_id = grupos.id
   ORDER BY empresas.id ASC"
)->fetchAll();
$oficinas = $pdo->query(
  "SELECT o.id, o.nombre, o.ciudad, e.nombre AS empresa_nombre
   FROM oficinas o
   JOIN empresas e ON o.empresa_id = e.id
   ORDER BY o.id ASC"
)->fetchAll();
$usuarios = $pdo->query("
  SELECT u.*, e.nombre AS empresa, o.nombre AS oficina, o.ciudad AS oficina_ciudad
  FROM usuarios u
  JOIN empresas e ON u.empresa_id = e.id
  JOIN oficinas o ON u.oficina_id = o.id 
ORDER BY id ASC")->fetchAll();
$habilidades = $pdo->query("SELECT * FROM habilidades ORDER BY id ASC")->fetchAll();

// Obtener los proyectos con sus participantes
$proyectos = $pdo->query("
  SELECT p.id,
         p.nombre,
         p.descripcion,
         p.estado,
         GROUP_CONCAT(CONCAT(u.nombre, ' ', u.apellido_paterno) SEPARATOR ', ') AS participantes
  FROM proyectos p
  LEFT JOIN proyecto_usuario pu ON p.id = pu.proyecto_id
  LEFT JOIN usuarios u ON pu.usuario_id = u.id
  GROUP BY p.id
  ORDER BY p.id ASC
")->fetchAll();

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
      <a href="?tab=grupos" class="<?= $tab === 'grupos' ? 'active' : '' ?>">Grupos</a>
      <a href="?tab=empresas" class="<?= $tab === 'empresas' ? 'active' : '' ?>">Empresas</a>
      <a href="?tab=oficinas" class="<?= $tab === 'oficinas' ? 'active' : '' ?>">Oficinas</a>
      <a href="?tab=usuarios" class="<?= $tab === 'usuarios' ? 'active' : '' ?>">Usuarios</a>
      <a href="?tab=habilidades" class="<?= $tab === 'habilidades' ? 'active' : '' ?>">Habilidades</a>
      <a href="?tab=proyectos" class="<?= $tab === 'proyectos' ? 'active' : '' ?>">Proyectos</a>
    </div>

    <div class="tab-content">
      <?php if ($tab === 'grupos'): ?>
        <h2>Nuevo grupo</h2>
        <form action="grupos.php" method="POST">
          <input type="text" name="nombre_grupo" required>
          <button>Crear</button>
        </form>

        <h2>Grupos registrados</h2>
        <table>
          <tr><th>ID</th><th>Nombre</th><th>Empresas</th><th>Renombrar</th><th>Eliminar</th></tr>
          <?php foreach ($grupos as $g): ?>
            <tr>
              <td><?= htmlspecialchars($g['id']) ?></td>
              <td><?= htmlspecialchars($g['nombre']) ?></td>
              <td><?= htmlspecialchars($g['empresas']) ?></td>
              <td>
                <form action="grupos.php" method="POST">
                  <input type="hidden" name="editar_grupo_id" value="<?= $g['id'] ?>">
                  <input type="text" name="nuevo_nombre" required placeholder="Nuevo nombre">
                  <button>Cambiar</button>
                </form>
              </td>
              <td>
                <form action="grupos.php" method="POST" onsubmit="return confirm('¿Borrar grupo?');">
                  <input type="hidden" name="eliminar_grupo_id" value="<?= $g['id'] ?>">
                  <button>Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>

      <?php elseif ($tab === 'empresas'): ?>
        <h2>Nueva empresa</h2>
        <form action="empresas.php" method="POST">
          <input type="text" name="nombre_empresa" required>
          <select name="grupo_id" required>
            <?php foreach ($grupos as $g): ?>
              <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <button>Crear</button>
        </form>

        <h2>Empresas registradas</h2>
        <table>
          <tr><th>ID</th><th>Nombre</th><th>Grupo</th><th>Renombrar</th><th>Eliminar</th></tr>
          <?php foreach ($empresas as $e): ?>
            <tr>
              <td><?= htmlspecialchars($e['id']) ?></td>
              <td><?= htmlspecialchars($e['nombre']) ?></td>
              <td><?= htmlspecialchars($e['grupo_nombre']) ?></td>
              <td>
                <form action="empresas.php" method="POST">
                  <input type="hidden" name="editar_empresa_id" value="<?= $e['id'] ?>">
                  <input type="text" name="nuevo_nombre" required placeholder="Nuevo nombre">
                  <button>Cambiar</button>
                </form>
              </td>
              <td>
                <form action="empresas.php" method="POST" onsubmit="return confirm('¿Borrar empresa?');">
                  <input type="hidden" name="eliminar_empresa_id" value="<?= $e['id'] ?>">
                  <button>Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>

      <?php elseif ($tab === 'oficinas'): ?>
        <h2>Nueva oficina</h2>
        <form action="oficinas.php" method="POST">
          <input type="text" name="nombre_oficina" required placeholder="Nombre de oficina">
          <select name="empresa_id" required>
            <?php foreach ($empresas as $e): ?>
              <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="ciudad" required placeholder="Ciudad">
          <button>Crear</button>
        </form>

        <h2>Oficinas registradas</h2>
        <table>
          <tr><th>ID</th><th>Nombre</th><th>Empresa</th><th>Ciudad</th></tr>
          <?php foreach ($oficinas as $o): ?>
            <tr>
              <td><?= htmlspecialchars($o['id']) ?></td>
              <td><?= htmlspecialchars($o['nombre']) ?></td>
              <td><?= htmlspecialchars($o['empresa_nombre']) ?></td>
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
          <br>
          <input name="email" type="email" required placeholder="Email">
          <br>
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
          <br>
          <label><input type="checkbox" name="es_admin"> Es admin</label>
          <br>
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

      <?php elseif ($tab === 'proyectos'): ?>
        <h2>Nuevo proyecto</h2>
        <form action="crear_proyecto.php" method="POST">
          <input type="text" name="nombre_proyecto" required placeholder="Nombre del proyecto">
          <br>
          <textarea name="descripcion" placeholder="Descripci&oacute;n"></textarea>
          <br>
          <label>Estado:
            <select name="estado">
              <option value="No iniciado">No iniciado</option>
              <option value="Iniciado">Iniciado</option>
              <option value="Pausado">Pausado</option>
              <option value="Finalizado">Finalizado</option>
            </select>
          </label>
          <br>
          <h3>Asignar usuarios</h3>
          <?php foreach ($usuarios as $u): ?>
            <label>
              <input type="checkbox" name="usuarios_seleccionados[]" value="<?= $u['id'] ?>">
              <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido_paterno']) ?> (<?= htmlspecialchars($u['email']) ?>)
            </label><br>
          <?php endforeach; ?>
          <button>Crear</button>
        </form>

        <h2>Proyectos registrados</h2>
        <table>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripci&oacute;n</th>
            <th>Estado</th>
            <th>Participantes</th>
          </tr>
          <?php foreach ($proyectos as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['id']) ?></td>
              <td><?= htmlspecialchars($p['nombre']) ?></td>
              <td><?= htmlspecialchars($p['descripcion']) ?></td>
              <td><?= htmlspecialchars($p['estado']) ?></td>
              <td><?= htmlspecialchars($p['participantes']) ?></td>
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
