<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
$id_usuario = $_SESSION['usuario_id'];

// Obtener información del usuario y sus asignaciones
$stmt = $pdo->prepare("
  SELECT u.nombre,
         u.apellido_paterno,
         u.apellido_materno,
         u.ciudad AS ciudad_usuario,
         e.nombre AS empresa_nombre,
         o.nombre AS oficina_nombre,
         o.ciudad AS oficina_ciudad,
         g.nombre AS grupo_nombre
  FROM usuarios u
  LEFT JOIN empresas e ON u.empresa_id = e.id
  LEFT JOIN oficinas o ON u.oficina_id = o.id
  LEFT JOIN grupos g ON e.grupo_id = g.id
  WHERE u.id = ?
");
$stmt->execute([$id_usuario]);
$usuario_info = $stmt->fetch(PDO::FETCH_ASSOC);
$ciudad_asignada = null;
if ($usuario_info) {
  $ciudad_asignada = $usuario_info['oficina_ciudad'] ?: $usuario_info['ciudad_usuario'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Eliminar habilidades anteriores
  $pdo->prepare("DELETE FROM usuario_habilidad WHERE usuario_id = ?")->execute([$id_usuario]);

  // Insertar habilidades seleccionadas (si hay)
  if (!empty($_POST['habilidades']) && is_array($_POST['habilidades'])) {
    $stmt = $pdo->prepare("INSERT INTO usuario_habilidad (usuario_id, habilidad_id) VALUES (?, ?)");
    foreach ($_POST['habilidades'] as $habilidad_id) {
      if (is_numeric($habilidad_id)) {
        $stmt->execute([$id_usuario, (int)$habilidad_id]);
      }
    }
  }

  // Redirigir para evitar reenvío del formulario
  header("Location: dashboard_user.php?tab=habilidades");
  exit;
}

// Obtener todas las habilidades
$habilidades = $pdo->query("SELECT * FROM habilidades ORDER BY nombre")->fetchAll();

// Obtener las habilidades del usuario
$stmt = $pdo->prepare("SELECT habilidad_id FROM usuario_habilidad WHERE usuario_id = ?");
$stmt->execute([$id_usuario]);
$habilidades_usuario_ids = array_column($stmt->fetchAll(), 'habilidad_id');

// Proyectos que lidera y donde participa
$stmt = $pdo->prepare("SELECT * FROM proyectos WHERE creador_id = ? ORDER BY id DESC");
$stmt->execute([$id_usuario]);
$proyectos_creados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
  SELECT p.*
  FROM proyectos p
  INNER JOIN proyecto_usuario pu ON pu.proyecto_id = p.id
  WHERE pu.usuario_id = ? AND p.creador_id != ?
  ORDER BY p.id DESC
");
$stmt->execute([$id_usuario, $id_usuario]);
$proyectos_participa = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tab = $_GET['tab'] ?? 'inicio';
$tab = in_array($tab, ['inicio', 'proyectos', 'habilidades'], true) ? $tab : 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SkillTracker - Panel del usuario</title>
  <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body class="dashboard-body">
  <div class="top-bar">
    <div class="top-bar-brand">
      <img src="/images/SkillTrackerLogo.png" alt="Logo SkillTracker">
      <span>SkillTracker</span>
    </div>
    <div class="top-bar-actions">
      <a href="cuenta.php" class="ghost-button">Cuenta</a>
      <a href="logout.php" class="logout-button">Cerrar sesión</a>
    </div>
  </div>

  <main class="main-content">
    <header class="page-header">
      <h1>Panel del usuario</h1>
      <p>Consulta tu información asignada, revisa tus proyectos y mantén tus habilidades al día.</p>
    </header>

    <nav class="tabs" aria-label="Secciones del panel del usuario">
      <a href="?tab=inicio" class="tab-link <?= $tab === 'inicio' ? 'active' : '' ?>">Inicio</a>
      <a href="?tab=proyectos" class="tab-link <?= $tab === 'proyectos' ? 'active' : '' ?>">Proyectos</a>
      <a href="?tab=habilidades" class="tab-link <?= $tab === 'habilidades' ? 'active' : '' ?>">Habilidades</a>
    </nav>

    <?php if ($tab === 'inicio'): ?>
    <section class="panel-section">
      <div class="card info-card">
        <div class="section-heading">
          <h2>Tu asignación en la organización</h2>
          <p>Verifica la estructura a la que perteneces dentro de SkillTracker.</p>
        </div>
        <div class="info-grid">
          <div class="info-grid-item">
            <span class="info-label">Grupo</span>
            <span class="info-value">
              <?= $usuario_info && $usuario_info['grupo_nombre'] ? htmlspecialchars($usuario_info['grupo_nombre']) : 'Sin grupo asignado' ?>
            </span>
          </div>
          <div class="info-grid-item">
            <span class="info-label">Empresa</span>
            <span class="info-value">
              <?= $usuario_info && $usuario_info['empresa_nombre'] ? htmlspecialchars($usuario_info['empresa_nombre']) : 'Sin empresa asignada' ?>
            </span>
          </div>
          <div class="info-grid-item">
            <span class="info-label">Oficina</span>
            <span class="info-value">
              <?= $usuario_info && $usuario_info['oficina_nombre'] ? htmlspecialchars($usuario_info['oficina_nombre']) : 'Sin oficina asignada' ?>
            </span>
          </div>
          <div class="info-grid-item">
            <span class="info-label">Ciudad</span>
            <span class="info-value">
              <?= $usuario_info && $ciudad_asignada ? htmlspecialchars($ciudad_asignada) : 'Sin ciudad asignada' ?>
            </span>
          </div>
        </div>
      </div>
    </section>
    <?php elseif ($tab === 'proyectos'): ?>
    <section class="panel-section">
      <div class="card list-card">
        <div class="section-heading">
          <h2>Proyectos asociados</h2>
          <p>Un resumen de los proyectos que lideras y en los que colaboras.</p>
        </div>
        <div class="projects-grid">
          <div class="project-column">
            <h3 class="column-title">Proyectos que lideras</h3>
            <?php if ($proyectos_creados): ?>
              <ul class="project-list">
                <?php foreach ($proyectos_creados as $p): ?>
                  <li class="project-item">
                    <h4><?= htmlspecialchars($p['nombre']) ?></h4>
                    <?php if (!empty($p['descripcion'])): ?>
                      <p><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
                    <?php else: ?>
                      <p class="empty-state">Sin descripción disponible.</p>
                    <?php endif; ?>
                    <div class="project-meta">
                      <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $p['estado'] ?? 'No definido')) ?>">
                        <?= htmlspecialchars($p['estado'] ?? 'Sin estado') ?>
                      </span>
                      <span>ID #<?= htmlspecialchars($p['id']) ?></span>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="empty-state">Todavía no lideras proyectos. ¡Inicia uno nuevo!</p>
            <?php endif; ?>
          </div>
          <div class="project-column">
            <h3 class="column-title">Proyectos donde colaboras</h3>
            <?php if ($proyectos_participa): ?>
              <ul class="project-list">
                <?php foreach ($proyectos_participa as $p): ?>
                  <li class="project-item">
                    <h4><?= htmlspecialchars($p['nombre']) ?></h4>
                    <?php if (!empty($p['descripcion'])): ?>
                      <p><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
                    <?php else: ?>
                      <p class="empty-state">Sin descripción disponible.</p>
                    <?php endif; ?>
                    <div class="project-meta">
                      <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $p['estado'] ?? 'No definido')) ?>">
                        <?= htmlspecialchars($p['estado'] ?? 'Sin estado') ?>
                      </span>
                      <span>ID #<?= htmlspecialchars($p['id']) ?></span>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p class="empty-state">Aún no participas en otros proyectos.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="panel-section">
      <div class="card action-card">
        <div class="section-heading">
          <h2>Crear un nuevo proyecto</h2>
          <p>Lanza nuevas iniciativas y asigna al equipo ideal.</p>
        </div>
        <div class="action-content">
          <a class="primary-button" href="nuevo_proyecto.php">Nuevo proyecto</a>
          <p class="field-hint">Podrás seleccionar las habilidades necesarias del catálogo completo disponible en la base de datos.</p>
        </div>
      </div>
    </section>
    <?php else: ?>
    <section class="panel-section">
      <div class="card form-card">
        <form method="POST" class="form-grid skill-form">
          <div class="checkbox-grid">
            <?php foreach ($habilidades as $hab): ?>
              <label class="checkbox-tile">
                <input type="checkbox" name="habilidades[]" value="<?= $hab['id'] ?>"
                  <?= in_array($hab['id'], $habilidades_usuario_ids) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars($hab['nombre']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <button type="submit" class="primary-button">Actualizar habilidades</button>
        </form>
      </div>
    </section>
    <?php endif; ?>
  </main>
</body>
</html>
