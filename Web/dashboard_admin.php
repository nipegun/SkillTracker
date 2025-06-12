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
          <tr><th>ID</th><th>Nombre</th><th>Empresas</th><th>Modificar</th><th>Eliminar</th></tr>
          <?php foreach ($grupos as $g): ?>
            <tr>
              <td><?= htmlspecialchars($g['id']) ?></td>
              <td><?= htmlspecialchars($g['nombre']) ?></td>
              <td><?= htmlspecialchars($g['empresas']) ?></td>
              <td>
                <form method="GET" action="dashboard_admin.php">
                  <input type="hidden" name="tab" value="grupos">
                  <input type="hidden" name="edit_grupo_id" value="<?= $g['id'] ?>">
                  <button>Modificar</button>
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

        <?php if (isset($_GET['edit_grupo_id'])):
              $stmt = $pdo->prepare("SELECT * FROM grupos WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_grupo_id']]);
              $grupoEditar = $stmt->fetch();
              if ($grupoEditar): ?>
          <h3>Editar grupo</h3>
          <form action="grupos.php" method="POST">
            <input type="hidden" name="editar_grupo_id" value="<?= $grupoEditar['id'] ?>">
            <input type="text" name="nuevo_nombre" required value="<?= htmlspecialchars($grupoEditar['nombre']) ?>">
            <button>Guardar</button>
            <a href="dashboard_admin.php?tab=grupos">Cancelar</a>
          </form>
        <?php endif; endif; ?>

      <?php elseif ($tab === 'empresas'): ?>
        <h2>Nueva empresa</h2>
        <form action="empresas.php" method="POST">
          <input type="text" name="nombre_empresa" required>
          <select name="grupo_id">
            <option value="">-</option>
            <?php foreach ($grupos as $g): ?>
              <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <button>Crear</button>
        </form>

        <h2>Empresas registradas</h2>
        <table>
          <tr><th>ID</th><th>Nombre</th><th>Grupo</th><th>Modificar</th><th>Eliminar</th></tr>
          <?php foreach ($empresas as $e): ?>
            <tr>
              <td><?= htmlspecialchars($e['id']) ?></td>
              <td><?= htmlspecialchars($e['nombre']) ?></td>
              <td><?= htmlspecialchars($e['grupo_nombre'] ?? '-') ?></td>
              <td>
                <form method="GET" action="dashboard_admin.php">
                  <input type="hidden" name="tab" value="empresas">
                  <input type="hidden" name="edit_empresa_id" value="<?= $e['id'] ?>">
                  <button>Modificar</button>
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

        <?php if (isset($_GET['edit_empresa_id'])):
              $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_empresa_id']]);
              $empresaEditar = $stmt->fetch();
              if ($empresaEditar): ?>
          <h3>Editar empresa</h3>
          <form action="empresas.php" method="POST">
            <input type="hidden" name="actualizar_empresa_id" value="<?= $empresaEditar['id'] ?>">
            <input type="text" name="nombre_empresa" required value="<?= htmlspecialchars($empresaEditar['nombre']) ?>">
            <select name="grupo_id">
              <option value="">-</option>
              <?php foreach ($grupos as $g): ?>
                <option value="<?= $g['id'] ?>" <?= $empresaEditar['grupo_id']==$g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
            <button>Guardar</button>
            <a href="dashboard_admin.php?tab=empresas">Cancelar</a>
          </form>
        <?php endif; endif; ?>

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
          <tr><th>ID</th><th>Nombre</th><th>Empresa</th><th>Ciudad</th><th>Modificar</th><th>Eliminar</th></tr>
          <?php foreach ($oficinas as $o): ?>
            <tr>
              <td><?= htmlspecialchars($o['id']) ?></td>
              <td><?= htmlspecialchars($o['nombre']) ?></td>
              <td><?= htmlspecialchars($o['empresa_nombre']) ?></td>
              <td><?= htmlspecialchars($o['ciudad']) ?></td>
              <td>
                <form method="GET" action="dashboard_admin.php">
                  <input type="hidden" name="tab" value="oficinas">
                  <input type="hidden" name="edit_oficina_id" value="<?= $o['id'] ?>">
                  <button>Modificar</button>
                </form>
              </td>
              <td>
                <form action="oficinas.php" method="POST" onsubmit="return confirm('¿Borrar oficina?');">
                  <input type="hidden" name="eliminar_oficina_id" value="<?= $o['id'] ?>">
                  <button>Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>

        <?php if (isset($_GET['edit_oficina_id'])):
              $stmt = $pdo->prepare("SELECT * FROM oficinas WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_oficina_id']]);
              $oficinaEditar = $stmt->fetch();
              if ($oficinaEditar): ?>
          <h3>Editar oficina</h3>
          <form action="oficinas.php" method="POST">
            <input type="hidden" name="actualizar_oficina_id" value="<?= $oficinaEditar['id'] ?>">
            <input type="text" name="nombre_oficina" required value="<?= htmlspecialchars($oficinaEditar['nombre']) ?>">
            <select name="empresa_id" required>
              <?php foreach ($empresas as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $oficinaEditar['empresa_id']==$e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="text" name="ciudad" required value="<?= htmlspecialchars($oficinaEditar['ciudad']) ?>">
            <button>Guardar</button>
            <a href="dashboard_admin.php?tab=oficinas">Cancelar</a>
          </form>
        <?php endif; endif; ?>

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
            <th>Modificar</th>
            <th>Eliminar</th>
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
              <td>
                <form method="GET" action="dashboard_admin.php">
                  <input type="hidden" name="tab" value="usuarios">
                  <input type="hidden" name="edit_usuario_id" value="<?= $u['id'] ?>">
                  <button>Modificar</button>
                </form>
              </td>
              <td>
                <form action="usuarios.php" method="POST" onsubmit="return confirm('¿Borrar usuario?');">
                  <input type="hidden" name="eliminar_usuario_id" value="<?= $u['id'] ?>">
                  <button>Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>

        <?php if (isset($_GET['edit_usuario_id'])):
              $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_usuario_id']]);
              $usuarioEditar = $stmt->fetch();
              if ($usuarioEditar): ?>
          <h3>Editar usuario</h3>
          <form action="usuarios.php" method="POST">
            <input type="hidden" name="actualizar_usuario_id" value="<?= $usuarioEditar['id'] ?>">
            <input name="nombre" required value="<?= htmlspecialchars($usuarioEditar['nombre']) ?>">
            <input name="apellido_paterno" required value="<?= htmlspecialchars($usuarioEditar['apellido_paterno']) ?>">
            <input name="apellido_materno" value="<?= htmlspecialchars($usuarioEditar['apellido_materno']) ?>">
            <br>
            <input name="email" type="email" required value="<?= htmlspecialchars($usuarioEditar['email']) ?>">
            <br>
            <input name="password" type="password" placeholder="Nueva contraseña">
            <select name="empresa_id" required>
              <?php foreach ($empresas as $e): ?>
                <option value="<?= $e['id'] ?>" <?= $usuarioEditar['empresa_id']==$e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
            <select name="oficina_id" required>
              <?php foreach ($oficinas as $o): ?>
                <option value="<?= $o['id'] ?>" <?= $usuarioEditar['oficina_id']==$o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?> (<?= htmlspecialchars($o['ciudad']) ?>)</option>
              <?php endforeach; ?>
            </select>
            <input name="ciudad" required value="<?= htmlspecialchars($usuarioEditar['ciudad']) ?>">
            <br>
            <label><input type="checkbox" name="es_admin" <?= $usuarioEditar['es_admin'] ? 'checked' : '' ?>> Es admin</label>
            <br>
            <button>Guardar</button>
            <a href="dashboard_admin.php?tab=usuarios">Cancelar</a>
          </form>
        <?php endif; endif; ?>

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
            <th>Modificar</th>
            <th>Eliminar</th>
          </tr>
          <?php foreach ($proyectos as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['id']) ?></td>
              <td><?= htmlspecialchars($p['nombre']) ?></td>
              <td><?= htmlspecialchars($p['descripcion']) ?></td>
              <td><?= htmlspecialchars($p['estado']) ?></td>
              <td><?= htmlspecialchars($p['participantes']) ?></td>
              <td>
                <form method="GET" action="dashboard_admin.php">
                  <input type="hidden" name="tab" value="proyectos">
                  <input type="hidden" name="edit_proyecto_id" value="<?= $p['id'] ?>">
                  <button>Modificar</button>
                </form>
              </td>
              <td>
                <form action="crear_proyecto.php" method="POST" onsubmit="return confirm('¿Borrar proyecto?');">
                  <input type="hidden" name="eliminar_proyecto_id" value="<?= $p['id'] ?>">
                  <button>Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>

        <?php if (isset($_GET['edit_proyecto_id'])):
              $stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_proyecto_id']]);
              $proyectoEditar = $stmt->fetch();
              if ($proyectoEditar):
                // obtener participantes actuales
                $stmt = $pdo->prepare("SELECT usuario_id FROM proyecto_usuario WHERE proyecto_id = ?");
                $stmt->execute([$proyectoEditar['id']]);
                $participantesActuales = array_column($stmt->fetchAll(), 'usuario_id'); ?>
          <h3>Editar proyecto</h3>
          <form action="crear_proyecto.php" method="POST">
            <input type="hidden" name="actualizar_proyecto_id" value="<?= $proyectoEditar['id'] ?>">
            <input type="text" name="nombre_proyecto" required value="<?= htmlspecialchars($proyectoEditar['nombre']) ?>">
            <br>
            <textarea name="descripcion"><?= htmlspecialchars($proyectoEditar['descripcion']) ?></textarea>
            <br>
            <label>Estado:
              <select name="estado">
                <option value="No iniciado" <?= $proyectoEditar['estado']=='No iniciado'?'selected':'' ?>>No iniciado</option>
                <option value="Iniciado" <?= $proyectoEditar['estado']=='Iniciado'?'selected':'' ?>>Iniciado</option>
                <option value="Pausado" <?= $proyectoEditar['estado']=='Pausado'?'selected':'' ?>>Pausado</option>
                <option value="Finalizado" <?= $proyectoEditar['estado']=='Finalizado'?'selected':'' ?>>Finalizado</option>
              </select>
            </label>
            <br>
            <h3>Asignar usuarios</h3>
            <?php foreach ($usuarios as $u): ?>
              <label>
                <input type="checkbox" name="usuarios_seleccionados[]" value="<?= $u['id'] ?>" <?= in_array($u['id'], $participantesActuales) ? 'checked' : '' ?>>
                <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido_paterno']) ?> (<?= htmlspecialchars($u['email']) ?>)
              </label><br>
            <?php endforeach; ?>
            <button>Guardar</button>
            <a href="dashboard_admin.php?tab=proyectos">Cancelar</a>
          </form>
        <?php endif; endif; ?>

      <?php elseif ($tab === 'habilidades'): ?>
        <h2>Nueva habilidad</h2>
          <form action="habilidades.php" method="POST">
            <input type="text" name="nombre_habilidad" required placeholder="Nombre de la habilidad">
            <button>Crear</button>
          </form>

        <h2>Habilidades registradas</h2>
          <table>
          <tr><th>ID</th><th>Nombre</th><th>Modificar</th><th>Eliminar</th></tr>
          <?php foreach ($habilidades as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['id']) ?></td>
            <td><?= htmlspecialchars($h['nombre']) ?></td>
            <td>
              <form method="GET" action="dashboard_admin.php">
                <input type="hidden" name="tab" value="habilidades">
                <input type="hidden" name="edit_habilidad_id" value="<?= $h['id'] ?>">
                <button>Modificar</button>
              </form>
            </td>
            <td>
              <form action="habilidades.php" method="POST" onsubmit="return confirm('¿Borrar habilidad?');">
                <input type="hidden" name="eliminar_habilidad_id" value="<?= $h['id'] ?>">
                <button>Eliminar</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>

        <?php if (isset($_GET['edit_habilidad_id'])):
              $stmt = $pdo->prepare("SELECT * FROM habilidades WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_habilidad_id']]);
              $habilidadEditar = $stmt->fetch();
              if ($habilidadEditar): ?>
          <h3>Editar habilidad</h3>
          <form action="habilidades.php" method="POST">
            <input type="hidden" name="actualizar_habilidad_id" value="<?= $habilidadEditar['id'] ?>">
            <input type="text" name="nombre_habilidad" required value="<?= htmlspecialchars($habilidadEditar['nombre']) ?>">
            <button>Guardar</button>
            <a href="dashboard_admin.php?tab=habilidades">Cancelar</a>
          </form>
        <?php endif; endif; ?>

      <?php else: ?>
        <p>Pestaña no válida.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
