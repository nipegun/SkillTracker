<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();

$idUsuario = $_SESSION['usuario_id'];
$mensajeExito = '';
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $accion = $_POST['action'] ?? '';

  if ($accion === 'update') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidoPaterno = trim($_POST['apellido_paterno'] ?? '');
    $apellidoMaterno = trim($_POST['apellido_materno'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $oficinaId = isset($_POST['oficina_id']) ? (int)$_POST['oficina_id'] : 0;
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $apellidoPaterno === '' || $email === '' || $ciudad === '' || $oficinaId <= 0) {
      $mensajeError = 'Por favor, completa todos los campos obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $mensajeError = 'Ingresa un correo electrónico válido.';
    } else {
      $actualizarPassword = false;
      $passwordHash = null;

      if ($password !== '') {
        if (strlen($password) < 8) {
          $mensajeError = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } else {
          $actualizarPassword = true;
          $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }
      }

      if ($mensajeError === '') {
        try {
          $pdo->beginTransaction();

          $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ? AND id != ?');
          $stmt->execute([$email, $idUsuario]);
          if ($stmt->fetchColumn() > 0) {
            $mensajeError = 'Ya existe una cuenta registrada con ese correo electrónico.';
            $pdo->rollBack();
          } else {
            $stmt = $pdo->prepare('SELECT id, empresa_id FROM oficinas WHERE id = ?');
            $stmt->execute([$oficinaId]);
            $oficina = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$oficina) {
              $mensajeError = 'Selecciona una oficina válida.';
              $pdo->rollBack();
            } else {
              $query = 'UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, ciudad = ?, oficina_id = ?, empresa_id = ?';
              $params = [$nombre, $apellidoPaterno, $apellidoMaterno, $email, $ciudad, $oficinaId, $oficina['empresa_id']];

              if ($actualizarPassword) {
                $query .= ', password_hash = ?';
                $params[] = $passwordHash;
              }

              $query .= ' WHERE id = ?';
              $params[] = $idUsuario;

              $stmt = $pdo->prepare($query);
              $stmt->execute($params);

              $pdo->commit();
              $mensajeExito = 'Tus datos se han actualizado correctamente.';
            }
          }
        } catch (PDOException $e) {
          if ($pdo->inTransaction()) {
            $pdo->rollBack();
          }
          error_log('Error al actualizar la cuenta: ' . $e->getMessage());
          $mensajeError = 'No se pudieron guardar los cambios. Inténtalo de nuevo más tarde.';
        }
      }
    }
  } elseif ($accion === 'delete') {
    if (!isset($_POST['confirm_delete'])) {
      $mensajeError = 'Debes confirmar que deseas eliminar tu cuenta.';
    } else {
      if (esSuperAdmin()) {
        $stmt = $pdo->query('SELECT COUNT(*) FROM usuarios WHERE es_admin = 1');
        if ((int)$stmt->fetchColumn() <= 1) {
          $mensajeError = 'No es posible eliminar la única cuenta administradora activa.';
        }
      }

      if ($mensajeError === '') {
        try {
          $pdo->beginTransaction();
          $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
          $stmt->execute([$idUsuario]);
          $pdo->commit();

          session_unset();
          session_destroy();
          header('Location: /index.php?cuenta_eliminada=1');
          exit;
        } catch (PDOException $e) {
          if ($pdo->inTransaction()) {
            $pdo->rollBack();
          }
          error_log('Error al eliminar la cuenta: ' . $e->getMessage());
          $mensajeError = 'No se pudo eliminar la cuenta. Inténtalo de nuevo más tarde.';
        }
      }
    }
  }
}

$stmt = $pdo->prepare('SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.ciudad, u.oficina_id, u.empresa_id, u.es_admin, e.nombre AS empresa_nombre, o.nombre AS oficina_nombre, o.ciudad AS oficina_ciudad FROM usuarios u LEFT JOIN empresas e ON u.empresa_id = e.id LEFT JOIN oficinas o ON u.oficina_id = o.id WHERE u.id = ?');
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
  session_unset();
  session_destroy();
  header('Location: /index.php');
  exit;
}

$oficinasStmt = $pdo->query('SELECT o.id, o.nombre, o.ciudad, e.nombre AS empresa_nombre FROM oficinas o JOIN empresas e ON o.empresa_id = e.id ORDER BY e.nombre, o.nombre');
$oficinas = $oficinasStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mi cuenta | SkillTracker</title>
  <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body class="dashboard-body">
  <div class="top-bar">
    <div class="top-bar-brand">
      <img src="/images/SkillTrackerLogo.png" alt="Logo SkillTracker">
      <span>SkillTracker</span>
    </div>
    <div class="top-bar-actions">
      <a href="dashboard.php" class="ghost-button">Volver al panel</a>
      <a href="logout.php" class="logout-button">Cerrar sesión</a>
    </div>
  </div>

  <?php if ($mensajeExito !== ''): ?>
    <div class="feedback-message success" role="status">
      <?= htmlspecialchars($mensajeExito) ?>
    </div>
  <?php endif; ?>

  <?php if ($mensajeError !== ''): ?>
    <div class="feedback-message error" role="alert">
      <?= htmlspecialchars($mensajeError) ?>
    </div>
  <?php endif; ?>

  <main class="main-content">
    <header class="page-header">
      <h1>Mi cuenta</h1>
      <p>Actualiza tus datos personales, gestiona tu acceso y controla la información asociada a tu perfil.</p>
    </header>

    <section class="panel-section">
      <div class="card form-card">
        <div class="section-heading">
          <h2>Datos personales</h2>
          <p>Los cambios se aplicarán inmediatamente a tu perfil de SkillTracker.</p>
        </div>

        <form method="POST" class="form-grid">
          <input type="hidden" name="action" value="update">

          <div class="form-field">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
          </div>

          <div class="form-field">
            <label for="apellido_paterno">Apellido paterno</label>
            <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?= htmlspecialchars($usuario['apellido_paterno']) ?>" required>
          </div>

          <div class="form-field">
            <label for="apellido_materno">Apellido materno</label>
            <input type="text" id="apellido_materno" name="apellido_materno" value="<?= htmlspecialchars($usuario['apellido_materno']) ?>">
          </div>

          <div class="form-field">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
          </div>

          <div class="form-field">
            <label for="ciudad">Ciudad</label>
            <input type="text" id="ciudad" name="ciudad" value="<?= htmlspecialchars($usuario['ciudad']) ?>" required>
          </div>

          <div class="form-field full-width">
            <label for="oficina_id">Oficina</label>
            <select id="oficina_id" name="oficina_id" required>
              <?php foreach ($oficinas as $oficina): ?>
                <option value="<?= $oficina['id'] ?>" <?= (int)$usuario['oficina_id'] === (int)$oficina['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($oficina['nombre'] . ' · ' . $oficina['ciudad'] . ' (' . $oficina['empresa_nombre'] . ')') ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p class="field-hint">La empresa se actualiza automáticamente al seleccionar una oficina.</p>
          </div>

          <div class="form-field full-width">
            <label for="password">Nueva contraseña</label>
            <input type="password" id="password" name="password" placeholder="Déjalo vacío para mantener la actual">
            <p class="field-hint">Debe contener al menos 8 caracteres.</p>
          </div>

          <div class="form-actions">
            <button type="submit" class="primary-button">Guardar cambios</button>
          </div>
        </form>
      </div>
    </section>

    <section class="panel-section">
      <div class="card danger-zone">
        <div class="section-heading">
          <h2>Eliminar cuenta</h2>
          <p>Esta acción es irreversible y eliminará tus proyectos y asignaciones relacionadas.</p>
        </div>

        <form method="POST" class="form-grid">
          <input type="hidden" name="action" value="delete">
          <div class="form-field full-width">
            <label class="checkbox-inline">
              <input type="checkbox" name="confirm_delete" value="1" required>
              Entiendo que esta acción no se puede deshacer.
            </label>
          </div>
          <div class="form-actions">
            <button type="submit" class="danger-button">Eliminar mi cuenta</button>
          </div>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
