<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
$id_usuario = $_SESSION['usuario_id'];

$stmt1 = $pdo->prepare("SELECT * FROM proyectos WHERE creador_id = ? ORDER BY id DESC");
$stmt1->execute([$id_usuario]);
$proyectos_creados = $stmt1->fetchAll();

$stmt2 = $pdo->prepare("
  SELECT p.* FROM proyectos p
  JOIN proyecto_usuario pu ON pu.proyecto_id = p.id
  WHERE pu.usuario_id = ? AND p.creador_id != ?
  ORDER BY p.id DESC
");
$stmt2->execute([$id_usuario, $id_usuario]);
$proyectos_participa = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mis proyectos | SkillTracker</title>
  <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body class="dashboard-body">
  <div class="top-bar">
    <div class="top-bar-brand">
      <img src="/images/SkillTrackerLogo.png" alt="Logo SkillTracker">
      <span>SkillTracker</span>
    </div>
    <div class="top-bar-actions">
      <a href="nuevo_proyecto.php" class="primary-button">Nuevo proyecto</a>
      <a href="dashboard.php" class="ghost-button">Volver al panel</a>
      <a href="logout.php" class="logout-button">Cerrar sesión</a>
    </div>
  </div>

  <main class="main-content">
    <header class="page-header">
      <h1>Mis proyectos</h1>
      <p>Consulta los proyectos que lideras y aquellos en los que colaboras.</p>
    </header>

    <section class="panel-section">
      <div class="card list-card">
        <div class="section-heading">
          <h2>Proyectos que lideras</h2>
          <p>Tienes el control de estas iniciativas.</p>
        </div>
        <?php if ($proyectos_creados): ?>
          <ul class="project-list">
            <?php foreach ($proyectos_creados as $p): ?>
              <li class="project-item">
                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                <?php if ($p['descripcion']): ?>
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
          <p class="empty-state">Aún no has creado proyectos. Inicia uno nuevo para comenzar.</p>
        <?php endif; ?>
      </div>
    </section>

    <section class="panel-section">
      <div class="card list-card">
        <div class="section-heading">
          <h2>Proyectos donde colaboras</h2>
          <p>Apoya al equipo con tus habilidades en estas iniciativas.</p>
        </div>
        <?php if ($proyectos_participa): ?>
          <ul class="project-list">
            <?php foreach ($proyectos_participa as $p): ?>
              <li class="project-item">
                <h3><?= htmlspecialchars($p['nombre']) ?></h3>
                <?php if ($p['descripcion']): ?>
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
          <p class="empty-state">Todavía no participas en otros proyectos. Busca uno y únete al equipo.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>
