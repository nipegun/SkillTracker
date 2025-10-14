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

$tab = $_GET['tab'] ?? 'inicio';
$grupoEditar = null;
if ($tab === 'grupos' && isset($_GET['edit_grupo_id'])) {
  $stmt = $pdo->prepare("SELECT * FROM grupos WHERE id = ?");
  $stmt->execute([(int)$_GET['edit_grupo_id']]);
  $grupoEditar = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel del admin | SkillTracker</title>
  <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body class="dashboard-body">
  <div class="top-bar">
    <div class="top-bar-brand">
      <img src="/images/SkillTrackerLogo.png" alt="Logo SkillTracker">
      <span>SkillTracker</span>
    </div>
    <a href="logout.php" class="logout-button">Cerrar sesión</a>
  </div>

  <main class="main-content">
    <header class="page-header">
      <h1>Panel del admin</h1>
      <p>Gestiona grupos, empresas, oficinas, usuarios, habilidades y proyectos desde una sola vista moderna.</p>
    </header>

    <nav class="tabs" aria-label="Secciones del panel">
      <a href="?tab=inicio" class="tab-link <?= $tab === 'inicio' ? 'active' : '' ?>">Inicio</a>
      <a href="?tab=grupos" class="tab-link <?= $tab === 'grupos' ? 'active' : '' ?>">Grupos</a>
      <a href="?tab=empresas" class="tab-link <?= $tab === 'empresas' ? 'active' : '' ?>">Empresas</a>
      <a href="?tab=oficinas" class="tab-link <?= $tab === 'oficinas' ? 'active' : '' ?>">Oficinas</a>
      <a href="?tab=usuarios" class="tab-link <?= $tab === 'usuarios' ? 'active' : '' ?>">Usuarios</a>
      <a href="?tab=habilidades" class="tab-link <?= $tab === 'habilidades' ? 'active' : '' ?>">Habilidades</a>
      <a href="?tab=proyectos" class="tab-link <?= $tab === 'proyectos' ? 'active' : '' ?>">Proyectos</a>
    </nav>

    <div class="tab-content">
      <?php if ($tab === 'inicio'): ?>
        <section class="panel-section">
          <div class="card table-card">
            <div class="section-heading">
              <h2>Resumen general</h2>
              <p>Consulta la cantidad de registros en cada módulo principal.</p>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Entidad</th>
                    <th>Total registrados</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Grupos</td>
                    <td><?= count($grupos) ?></td>
                  </tr>
                  <tr>
                    <td>Empresas</td>
                    <td><?= count($empresas) ?></td>
                  </tr>
                  <tr>
                    <td>Oficinas</td>
                    <td><?= count($oficinas) ?></td>
                  </tr>
                  <tr>
                    <td>Usuarios</td>
                    <td><?= count($usuarios) ?></td>
                  </tr>
                  <tr>
                    <td>Habilidades</td>
                    <td><?= count($habilidades) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </section>

      <?php elseif ($tab === 'grupos'): ?>
        <section class="panel-section">
          <div class="card table-card">
            <div class="section-heading">
              <h2>Grupos registrados</h2>
              <p>Consulta y administra los grupos existentes.</p>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr><th>ID</th><th>Nombre</th><th>Empresas</th><th>Modificar</th><th>Eliminar</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($grupos as $g): ?>
                    <tr>
                      <td><?= htmlspecialchars($g['id']) ?></td>
                      <td><?= htmlspecialchars($g['nombre']) ?></td>
                      <td><?= htmlspecialchars($g['empresas']) ?></td>
                      <td>
                        <form method="GET" action="dashboard_admin.php" class="inline-form">
                          <input type="hidden" name="tab" value="grupos">
                          <input type="hidden" name="edit_grupo_id" value="<?= $g['id'] ?>">
                          <button type="submit" class="ghost-button">Modificar</button>
                        </form>
                      </td>
                      <td>
                        <form action="grupos.php" method="POST" class="inline-form" onsubmit="return confirm('¿Borrar grupo?');">
                          <input type="hidden" name="eliminar_grupo_id" value="<?= $g['id'] ?>">
                          <button type="submit" class="danger-button">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <?php if ($grupoEditar): ?>
          <section class="panel-section">
            <div class="card form-card">
              <div class="section-heading">
                <h3>Editar grupo</h3>
                <p>Actualiza el nombre del grupo seleccionado.</p>
              </div>
              <form action="grupos.php" method="POST" class="form-grid">
                <input type="hidden" name="editar_grupo_id" value="<?= $grupoEditar['id'] ?>">
                <div class="form-field full-width">
                  <label for="nuevo_nombre">Nombre</label>
                  <input type="text" id="nuevo_nombre" name="nuevo_nombre" required value="<?= htmlspecialchars($grupoEditar['nombre']) ?>">
                </div>
                <div class="form-actions">
                  <button type="submit" class="primary-button">Guardar cambios</button>
                  <a href="dashboard_admin.php?tab=grupos" class="ghost-link">Cancelar</a>
                </div>
              </form>
            </div>
          </section>
        <?php endif; ?>

        <section class="panel-section">
          <div class="card form-card">
            <div class="section-heading">
              <h2>Crear nuevo grupo</h2>
              <p>Organiza tus empresas por grupo para facilitar la administración.</p>
            </div>
            <form action="grupos.php" method="POST" class="form-grid">
              <div class="form-field full-width">
                <label for="nombre_grupo">Nombre del grupo</label>
                <input type="text" id="nombre_grupo" name="nombre_grupo" required placeholder="Ej. División Norte">
              </div>
              <button type="submit" class="primary-button">Crear grupo</button>
            </form>
          </div>
        </section>

      <?php elseif ($tab === 'empresas'): ?>
        <section class="panel-section">
          <div class="card form-card">
            <div class="section-heading">
              <h2>Nueva empresa</h2>
              <p>Crea empresas y vincúlalas a un grupo.</p>
            </div>
            <form action="empresas.php" method="POST" class="form-grid">
              <div class="form-field full-width">
                <label for="nombre_empresa">Nombre de la empresa</label>
                <input type="text" id="nombre_empresa" name="nombre_empresa" required placeholder="Ej. Inversiones Atlas">
              </div>
              <div class="form-field full-width">
                <label for="grupo_id">Grupo</label>
                <select name="grupo_id" id="grupo_id">
                  <option value="">Sin grupo</option>
                  <?php foreach ($grupos as $g): ?>
                    <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="primary-button">Crear empresa</button>
            </form>
          </div>
        </section>

        <section class="panel-section">
          <div class="card table-card">
            <div class="section-heading">
              <h2>Empresas registradas</h2>
              <p>Gestiona los datos de las empresas en el sistema.</p>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr><th>ID</th><th>Nombre</th><th>Grupo</th><th>Modificar</th><th>Eliminar</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($empresas as $e): ?>
                    <tr>
                      <td><?= htmlspecialchars($e['id']) ?></td>
                      <td><?= htmlspecialchars($e['nombre']) ?></td>
                      <td><?= htmlspecialchars($e['grupo_nombre'] ?? '-') ?></td>
                      <td>
                        <form method="GET" action="dashboard_admin.php" class="inline-form">
                          <input type="hidden" name="tab" value="empresas">
                          <input type="hidden" name="edit_empresa_id" value="<?= $e['id'] ?>">
                          <button type="submit" class="ghost-button">Modificar</button>
                        </form>
                      </td>
                      <td>
                        <form action="empresas.php" method="POST" class="inline-form" onsubmit="return confirm('¿Borrar empresa?');">
                          <input type="hidden" name="eliminar_empresa_id" value="<?= $e['id'] ?>">
                          <button type="submit" class="danger-button">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <?php if (isset($_GET['edit_empresa_id'])):
              $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_empresa_id']]);
              $empresaEditar = $stmt->fetch();
              if ($empresaEditar): ?>
          <section class="panel-section">
            <div class="card form-card">
              <div class="section-heading">
                <h3>Editar empresa</h3>
                <p>Actualiza la información de la empresa.</p>
              </div>
              <form action="empresas.php" method="POST" class="form-grid">
                <input type="hidden" name="actualizar_empresa_id" value="<?= $empresaEditar['id'] ?>">
                <div class="form-field full-width">
                  <label for="nombre_empresa_edit">Nombre</label>
                  <input type="text" id="nombre_empresa_edit" name="nombre_empresa" required value="<?= htmlspecialchars($empresaEditar['nombre']) ?>">
                </div>
                <div class="form-field full-width">
                  <label for="grupo_id_edit">Grupo</label>
                  <select name="grupo_id" id="grupo_id_edit">
                    <option value="">Sin grupo</option>
                    <?php foreach ($grupos as $g): ?>
                      <option value="<?= $g['id'] ?>" <?= $empresaEditar['grupo_id']==$g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['nombre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-actions">
                  <button type="submit" class="primary-button">Guardar cambios</button>
                  <a href="dashboard_admin.php?tab=empresas" class="ghost-link">Cancelar</a>
                </div>
              </form>
            </div>
          </section>
        <?php endif; endif; ?>

      <?php elseif ($tab === 'oficinas'): ?>
        <section class="panel-section">
          <div class="card form-card">
            <div class="section-heading">
              <h2>Nueva oficina</h2>
              <p>Agrega oficinas y asigna la empresa correspondiente.</p>
            </div>
            <form action="oficinas.php" method="POST" class="form-grid">
              <div class="form-field">
                <label for="nombre_oficina">Nombre</label>
                <input type="text" id="nombre_oficina" name="nombre_oficina" required placeholder="Ej. Oficina Centro">
              </div>
              <div class="form-field">
                <label for="empresa_id">Empresa</label>
                <select name="empresa_id" id="empresa_id" required>
                  <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" required placeholder="Ej. Ciudad de México">
              </div>
              <button type="submit" class="primary-button">Crear oficina</button>
            </form>
          </div>
        </section>

        <section class="panel-section">
          <div class="card table-card">
            <div class="section-heading">
              <h2>Oficinas registradas</h2>
              <p>Revisa las oficinas disponibles y su ubicación.</p>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr><th>ID</th><th>Nombre</th><th>Ciudad</th><th>Empresa</th><th>Modificar</th><th>Eliminar</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($oficinas as $o): ?>
                    <tr>
                      <td><?= htmlspecialchars($o['id']) ?></td>
                      <td><?= htmlspecialchars($o['nombre']) ?></td>
                      <td><?= htmlspecialchars($o['ciudad']) ?></td>
                      <td><?= htmlspecialchars($o['empresa_nombre']) ?></td>
                      <td>
                        <form method="GET" action="dashboard_admin.php" class="inline-form">
                          <input type="hidden" name="tab" value="oficinas">
                          <input type="hidden" name="edit_oficina_id" value="<?= $o['id'] ?>">
                          <button type="submit" class="ghost-button">Modificar</button>
                        </form>
                      </td>
                      <td>
                        <form action="oficinas.php" method="POST" class="inline-form" onsubmit="return confirm('¿Borrar oficina?');">
                          <input type="hidden" name="eliminar_oficina_id" value="<?= $o['id'] ?>">
                          <button type="submit" class="danger-button">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <?php if (isset($_GET['edit_oficina_id'])):
              $stmt = $pdo->prepare("SELECT * FROM oficinas WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_oficina_id']]);
              $oficinaEditar = $stmt->fetch();
              if ($oficinaEditar): ?>
          <section class="panel-section">
            <div class="card form-card">
              <div class="section-heading">
                <h3>Editar oficina</h3>
                <p>Actualiza los datos de la oficina seleccionada.</p>
              </div>
              <form action="oficinas.php" method="POST" class="form-grid">
                <input type="hidden" name="actualizar_oficina_id" value="<?= $oficinaEditar['id'] ?>">
                <div class="form-field">
                  <label for="nombre_oficina_edit">Nombre</label>
                  <input type="text" id="nombre_oficina_edit" name="nombre_oficina" required value="<?= htmlspecialchars($oficinaEditar['nombre']) ?>">
                </div>
                <div class="form-field">
                  <label for="empresa_id_edit">Empresa</label>
                  <select name="empresa_id" id="empresa_id_edit" required>
                    <?php foreach ($empresas as $e): ?>
                      <option value="<?= $e['id'] ?>" <?= $oficinaEditar['empresa_id'] == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-field">
                  <label for="ciudad_edit">Ciudad</label>
                  <input type="text" id="ciudad_edit" name="ciudad" required value="<?= htmlspecialchars($oficinaEditar['ciudad']) ?>">
                </div>
                <div class="form-actions">
                  <button type="submit" class="primary-button">Guardar cambios</button>
                  <a href="dashboard_admin.php?tab=oficinas" class="ghost-link">Cancelar</a>
                </div>
              </form>
            </div>
          </section>
        <?php endif; endif; ?>

      <?php elseif ($tab === 'usuarios'): ?>
        <section class="panel-section">
          <div class="card form-card">
            <div class="section-heading">
              <h2>Registrar nuevo usuario</h2>
              <p>Crea nuevas cuentas para tu organización desde el panel de administración.</p>
            </div>
            <form action="usuarios.php" method="POST" class="form-grid">
              <div class="form-field">
                <label for="nombre_usuario">Nombre</label>
                <input type="text" id="nombre_usuario" name="nombre" required>
              </div>
              <div class="form-field">
                <label for="apellido_paterno">Apellido paterno</label>
                <input type="text" id="apellido_paterno" name="apellido_paterno" required>
              </div>
              <div class="form-field">
                <label for="apellido_materno">Apellido materno</label>
                <input type="text" id="apellido_materno" name="apellido_materno">
              </div>
              <div class="form-field">
                <label for="email_usuario">Email</label>
                <input type="email" id="email_usuario" name="email" required>
              </div>
              <div class="form-field">
                <label for="password_usuario">Contraseña</label>
                <input type="password" id="password_usuario" name="password" required>
              </div>
              <div class="form-field">
                <label for="empresa_usuario">Empresa</label>
                <select name="empresa_id" id="empresa_usuario" required>
                  <option value="" disabled selected>Selecciona una empresa</option>
                  <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label for="oficina_usuario">Oficina</label>
                <select name="oficina_id" id="oficina_usuario" required>
                  <option value="" disabled selected>Selecciona una oficina</option>
                  <?php foreach ($oficinas as $o): ?>
                    <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['nombre']) ?> - <?= htmlspecialchars($o['ciudad']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-field">
                <label for="ciudad_usuario">Ciudad</label>
                <input type="text" id="ciudad_usuario" name="ciudad" required>
              </div>
              <div class="form-field">
                <label class="checkbox-inline">
                  <input type="checkbox" name="es_admin">
                  <span>Conceder privilegios de administrador</span>
                </label>
              </div>
              <div class="form-actions">
                <button type="submit" class="primary-button">Crear usuario</button>
              </div>
            </form>
          </div>
        </section>

        <section class="panel-section">
          <div class="card table-card">
            <div class="section-heading">
              <h2>Usuarios registrados</h2>
              <p>Consulta el listado completo de usuarios y administra sus datos.</p>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>ID</th><th>Nombre</th><th>Apellidos</th><th>Email</th><th>Empresa</th><th>Oficina</th><th>Ciudad</th><th>Rol</th><th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($usuarios as $u): ?>
                    <tr>
                      <td><?= htmlspecialchars($u['id']) ?></td>
                      <td><?= htmlspecialchars($u['nombre']) ?></td>
                      <td><?= htmlspecialchars(trim($u['apellido_paterno'] . ' ' . $u['apellido_materno'])) ?></td>
                      <td><?= htmlspecialchars($u['email']) ?></td>
                      <td><?= htmlspecialchars($u['empresa']) ?></td>
                      <td><?= htmlspecialchars($u['oficina']) ?></td>
                      <td><?= htmlspecialchars($u['ciudad']) ?></td>
                      <td><?= $u['es_admin'] ? 'Admin' : 'Usuario' ?></td>
                      <td class="table-actions">
                        <form method="GET" action="dashboard_admin.php" class="inline-form">
                          <input type="hidden" name="tab" value="usuarios">
                          <input type="hidden" name="edit_usuario_id" value="<?= $u['id'] ?>">
                          <button type="submit" class="ghost-button">Editar</button>
                        </form>
                        <?php if (!$u['es_admin']): ?>
                          <form action="usuarios.php" method="POST" class="inline-form" onsubmit="return confirm('¿Borrar usuario?');">
                            <input type="hidden" name="eliminar_usuario_id" value="<?= $u['id'] ?>">
                            <button type="submit" class="danger-button">Eliminar</button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <?php if (isset($_GET['edit_usuario_id'])):
              $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_usuario_id']]);
              $usuarioEditar = $stmt->fetch();
              if ($usuarioEditar): ?>
          <section class="panel-section">
            <div class="card form-card">
              <div class="section-heading">
                <h3>Editar usuario</h3>
                <p>Actualiza los datos del usuario seleccionado.</p>
              </div>
              <form action="usuarios.php" method="POST" class="form-grid">
                <input type="hidden" name="actualizar_usuario_id" value="<?= $usuarioEditar['id'] ?>">
                <div class="form-field">
                  <label for="nombre_usuario_edit">Nombre</label>
                  <input type="text" id="nombre_usuario_edit" name="nombre" value="<?= htmlspecialchars($usuarioEditar['nombre']) ?>" required>
                </div>
                <div class="form-field">
                  <label for="apellido_paterno_edit">Apellido paterno</label>
                  <input type="text" id="apellido_paterno_edit" name="apellido_paterno" value="<?= htmlspecialchars($usuarioEditar['apellido_paterno']) ?>" required>
                </div>
                <div class="form-field">
                  <label for="apellido_materno_edit">Apellido materno</label>
                  <input type="text" id="apellido_materno_edit" name="apellido_materno" value="<?= htmlspecialchars($usuarioEditar['apellido_materno']) ?>">
                </div>
                <div class="form-field">
                  <label for="email_edit">Email</label>
                  <input type="email" id="email_edit" name="email" value="<?= htmlspecialchars($usuarioEditar['email']) ?>" required>
                </div>
                <div class="form-field">
                  <label for="empresa_usuario_edit">Empresa</label>
                  <select name="empresa_id" id="empresa_usuario_edit" required>
                    <?php foreach ($empresas as $e): ?>
                      <option value="<?= $e['id'] ?>" <?= $usuarioEditar['empresa_id']==$e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-field">
                  <label for="oficina_usuario_edit">Oficina</label>
                  <select name="oficina_id" id="oficina_usuario_edit" required>
                    <?php foreach ($oficinas as $o): ?>
                      <option value="<?= $o['id'] ?>" <?= $usuarioEditar['oficina_id']==$o['id'] ? 'selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?> - <?= htmlspecialchars($o['ciudad']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-field">
                  <label for="ciudad_usuario_edit">Ciudad</label>
                  <input type="text" id="ciudad_usuario_edit" name="ciudad" value="<?= htmlspecialchars($usuarioEditar['ciudad']) ?>" required>
                </div>
                <div class="form-field">
                  <label for="password_edit">Actualizar contraseña</label>
                  <input type="password" id="password_edit" name="password" placeholder="Deja en blanco para mantener la actual">
                </div>
                <div class="form-field">
                  <label class="checkbox-inline">
                    <input type="checkbox" name="es_admin" <?= $usuarioEditar['es_admin'] ? 'checked' : '' ?>>
                    <span>Conceder privilegios de administrador</span>
                  </label>
                </div>
                <div class="form-actions">
                  <button type="submit" class="primary-button">Guardar cambios</button>
                  <a href="dashboard_admin.php?tab=usuarios" class="ghost-link">Cancelar</a>
                </div>
              </form>
            </div>
          </section>
        <?php endif; endif; ?>

      <?php elseif ($tab === 'habilidades'): ?>
        <section class="panel-section">
          <div class="card form-card">
            <div class="section-heading">
              <h2>Nueva habilidad</h2>
              <p>Registra habilidades para asignarlas a los usuarios.</p>
            </div>
            <form action="habilidades.php" method="POST" class="form-grid">
              <div class="form-field full-width">
                <label for="nombre_habilidad">Nombre de la habilidad</label>
                <input type="text" id="nombre_habilidad" name="nombre_habilidad" required placeholder="Ej. Gestión de proyectos">
              </div>
              <button type="submit" class="primary-button">Crear habilidad</button>
            </form>
          </div>
        </section>

        <section class="panel-section">
          <div class="card table-card">
            <div class="section-heading">
              <h2>Habilidades registradas</h2>
              <p>Consulta, renombra o elimina habilidades existentes.</p>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr><th>ID</th><th>Nombre</th><th>Modificar</th><th>Eliminar</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($habilidades as $h): ?>
                    <tr>
                      <td><?= htmlspecialchars($h['id']) ?></td>
                      <td><?= htmlspecialchars($h['nombre']) ?></td>
                      <td>
                        <form action="habilidades.php" method="POST" class="inline-form">
                          <input type="hidden" name="editar_habilidad_id" value="<?= $h['id'] ?>">
                          <input type="text" name="nuevo_nombre" required value="<?= htmlspecialchars($h['nombre']) ?>">
                          <button type="submit" class="ghost-button">Guardar</button>
                        </form>
                      </td>
                      <td>
                        <form action="habilidades.php" method="POST" class="inline-form" onsubmit="return confirm('¿Borrar habilidad?');">
                          <input type="hidden" name="eliminar_habilidad_id" value="<?= $h['id'] ?>">
                          <button type="submit" class="danger-button">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

      <?php elseif ($tab === 'proyectos'): ?>
        <section class="panel-section">
          <div class="card form-card">
            <div class="section-heading">
              <h2>Crear nuevo proyecto</h2>
              <p>Inicia un proyecto y asigna participantes.</p>
            </div>
            <form action="crear_proyecto.php" method="POST" class="form-grid">
              <div class="form-field full-width">
                <label for="nombre_proyecto">Nombre del proyecto</label>
                <input type="text" id="nombre_proyecto" name="nombre_proyecto" required placeholder="Ej. Implementación ERP">
              </div>
              <div class="form-field full-width">
                <label for="descripcion_proyecto">Descripción</label>
                <textarea id="descripcion_proyecto" name="descripcion" rows="3" placeholder="Describe los objetivos principales"></textarea>
              </div>
              <div class="form-field">
                <label for="estado_proyecto">Estado</label>
                <select id="estado_proyecto" name="estado">
                  <option value="No iniciado">No iniciado</option>
                  <option value="En progreso">En progreso</option>
                  <option value="Completado">Completado</option>
                </select>
              </div>
              <div class="form-field full-width">
                <label for="participantes">Participantes</label>
                <select id="participantes" name="usuarios_seleccionados[]" multiple size="5">
                  <?php foreach ($usuarios as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido_paterno']) ?> - <?= htmlspecialchars($u['empresa']) ?></option>
                  <?php endforeach; ?>
                </select>
                <small class="field-hint">Mantén presionada la tecla Ctrl (Cmd en Mac) para seleccionar múltiples usuarios.</small>
              </div>
              <button type="submit" class="primary-button">Crear proyecto</button>
            </form>
          </div>
        </section>

        <section class="panel-section">
          <div class="card table-card">
            <div class="section-heading">
              <h2>Proyectos activos</h2>
              <p>Consulta el estado y los participantes de cada proyecto.</p>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Participantes</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($proyectos as $p): ?>
                    <tr>
                      <td><?= htmlspecialchars($p['id']) ?></td>
                      <td><?= htmlspecialchars($p['nombre']) ?></td>
                      <td><?= htmlspecialchars($p['descripcion']) ?></td>
                      <td><span class="status-pill status-<?= strtolower(str_replace(' ', '-', $p['estado'])) ?>"><?= htmlspecialchars($p['estado']) ?></span></td>
                      <td><?= htmlspecialchars($p['participantes']) ?></td>
                      <td class="table-actions">
                        <form action="exportar_proyecto.php" method="GET" class="inline-form">
                          <input type="hidden" name="proyecto_id" value="<?= $p['id'] ?>">
                          <button type="submit" class="ghost-button">Exportar</button>
                        </form>
                        <form method="GET" action="dashboard_admin.php" class="inline-form">
                          <input type="hidden" name="tab" value="proyectos">
                          <input type="hidden" name="edit_proyecto_id" value="<?= $p['id'] ?>">
                          <button type="submit" class="ghost-button">Editar</button>
                        </form>
                        <form action="crear_proyecto.php" method="POST" class="inline-form" onsubmit="return confirm('¿Borrar proyecto?');">
                          <input type="hidden" name="eliminar_proyecto_id" value="<?= $p['id'] ?>">
                          <button type="submit" class="danger-button">Eliminar</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <?php if (isset($_GET['edit_proyecto_id'])):
              $stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ?");
              $stmt->execute([(int)$_GET['edit_proyecto_id']]);
              $proyectoEditar = $stmt->fetch();
              if ($proyectoEditar):
                $stmtParticipantes = $pdo->prepare("SELECT usuario_id FROM proyecto_usuario WHERE proyecto_id = ?");
                $stmtParticipantes->execute([$proyectoEditar['id']]);
                $participantesSeleccionados = array_column($stmtParticipantes->fetchAll(), 'usuario_id');
        ?>
          <section class="panel-section">
            <div class="card form-card">
              <div class="section-heading">
                <h3>Editar proyecto</h3>
                <p>Actualiza la información y los participantes del proyecto.</p>
              </div>
              <form action="crear_proyecto.php" method="POST" class="form-grid">
                <input type="hidden" name="actualizar_proyecto_id" value="<?= $proyectoEditar['id'] ?>">
                <div class="form-field full-width">
                  <label for="nombre_proyecto_edit">Nombre</label>
                  <input type="text" id="nombre_proyecto_edit" name="nombre_proyecto" required value="<?= htmlspecialchars($proyectoEditar['nombre']) ?>">
                </div>
                <div class="form-field full-width">
                  <label for="descripcion_proyecto_edit">Descripción</label>
                  <textarea id="descripcion_proyecto_edit" name="descripcion" rows="3"><?= htmlspecialchars($proyectoEditar['descripcion']) ?></textarea>
                </div>
                <div class="form-field">
                  <label for="estado_proyecto_edit">Estado</label>
                  <select id="estado_proyecto_edit" name="estado">
                    <option value="No iniciado" <?= $proyectoEditar['estado']==='No iniciado' ? 'selected' : '' ?>>No iniciado</option>
                    <option value="En progreso" <?= $proyectoEditar['estado']==='En progreso' ? 'selected' : '' ?>>En progreso</option>
                    <option value="Completado" <?= $proyectoEditar['estado']==='Completado' ? 'selected' : '' ?>>Completado</option>
                  </select>
                </div>
                <div class="form-field full-width">
                  <label for="participantes_edit">Participantes</label>
                  <select id="participantes_edit" name="usuarios_seleccionados[]" multiple size="5">
                    <?php foreach ($usuarios as $u): ?>
                      <option value="<?= $u['id'] ?>" <?= in_array($u['id'], $participantesSeleccionados) ? 'selected' : '' ?>><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido_paterno']) ?> - <?= htmlspecialchars($u['empresa']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-actions">
                  <button type="submit" class="primary-button">Guardar cambios</button>
                  <a href="dashboard_admin.php?tab=proyectos" class="ghost-link">Cancelar</a>
                </div>
              </form>
            </div>
          </section>
        <?php endif; endif; endif; ?>
    </div>
  </main>
</body>
</html>
