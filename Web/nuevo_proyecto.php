<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
$id_usuario = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_proyecto'])) {
  $id = obtenerSiguienteId($pdo, 'proyectos');
  $pdo->prepare("INSERT INTO proyectos (id, nombre, descripcion, creador_id) VALUES (?, ?, ?, ?)")
      ->execute([$id, $_POST['nombre_proyecto'], $_POST['descripcion'], $id_usuario]);
  $proyecto_id = $id;
  if (!empty($_POST['usuarios_seleccionados'])) {
    $stmt = $pdo->prepare("INSERT INTO proyecto_usuario (proyecto_id, usuario_id) VALUES (?, ?)");
    foreach ($_POST['usuarios_seleccionados'] as $uid) {
      $stmt->execute([$proyecto_id, $uid]);
    }
  }
  header("Location: proyectos.php");
  exit;
}

$habilidades = $pdo->query("SELECT * FROM habilidades ORDER BY nombre")->fetchAll();
$usuarios_filtrados = [];

if (isset($_GET['habilidades']) && is_array($_GET['habilidades']) && $_GET['habilidades']) {
  $ids = implode(',', array_map('intval', $_GET['habilidades']));
  $sql = "
    SELECT u.id, u.nombre, u.apellido_paterno, u.email
    FROM usuarios u
    JOIN usuario_habilidad uh ON u.id = uh.usuario_id
    WHERE uh.habilidad_id IN ($ids)
    GROUP BY u.id
    HAVING COUNT(DISTINCT uh.habilidad_id) = " . count($_GET['habilidades']);
  $usuarios_filtrados = $pdo->query($sql)->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nuevo proyecto | SkillTracker</title>
  <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body class="dashboard-body">
  <div class="top-bar">
    <div class="top-bar-brand">
      <img src="/images/SkillTrackerLogo.png" alt="Logo SkillTracker">
      <span>SkillTracker</span>
    </div>
    <div class="top-bar-actions">
      <a href="proyectos.php" class="ghost-button">Mis proyectos</a>
      <a href="dashboard.php" class="ghost-button">Volver al panel</a>
      <a href="logout.php" class="logout-button">Cerrar sesión</a>
    </div>
  </div>

  <main class="main-content">
    <header class="page-header">
      <h1>Crear nuevo proyecto</h1>
      <p>Diseña proyectos modernos, asigna participantes y describe sus objetivos en unos cuantos clics.</p>
    </header>

    <section class="panel-section">
      <div class="card form-card">
        <div class="section-heading">
          <h2>Detalles del proyecto</h2>
          <p>Completa la información para lanzar un nuevo proyecto.</p>
        </div>
        <form method="POST" class="form-grid">
          <div class="form-field full-width">
            <label for="nombre_proyecto">Nombre</label>
            <input type="text" id="nombre_proyecto" name="nombre_proyecto" required placeholder="Ej. Plataforma de analítica">
          </div>
          <div class="form-field full-width">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe el propósito y alcance del proyecto"></textarea>
          </div>

          <?php if ($usuarios_filtrados): ?>
            <div class="form-field full-width">
              <label>Selecciona miembros</label>
              <div class="checkbox-grid">
                <?php foreach ($usuarios_filtrados as $u): ?>
                  <label class="checkbox-tile">
                    <input type="checkbox" name="usuarios_seleccionados[]" value="<?= $u['id'] ?>">
                    <span><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido_paterno']) ?> · <?= htmlspecialchars($u['email']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <p class="field-hint">Selecciona las personas adecuadas según las habilidades filtradas.</p>
            </div>
          <?php endif; ?>

          <button type="submit" class="primary-button">Crear proyecto</button>
        </form>
      </div>
    </section>

    <section class="panel-section">
      <div class="card form-card">
        <div class="section-heading">
          <h2>Buscar talento por habilidades</h2>
          <p>Filtra colaboradores según las habilidades que necesitas.</p>
        </div>
        <form method="GET" class="form-grid skill-form">
          <div class="checkbox-grid">
            <?php foreach ($habilidades as $hab): ?>
              <label class="checkbox-tile">
                <input type="checkbox" name="habilidades[]" value="<?= $hab['id'] ?>" <?= isset($_GET['habilidades']) && in_array($hab['id'], $_GET['habilidades']) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars($hab['nombre']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <button type="submit" class="ghost-button">Buscar talento</button>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
