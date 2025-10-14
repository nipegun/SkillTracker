<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
$id_usuario = $_SESSION['usuario_id'];

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
  header("Location: dashboard_user.php");
  exit;
}

// Obtener todas las habilidades
$habilidades = $pdo->query("SELECT * FROM habilidades ORDER BY nombre")->fetchAll();

// Obtener las habilidades del usuario
$stmt = $pdo->prepare("SELECT habilidad_id FROM usuario_habilidad WHERE usuario_id = ?");
$stmt->execute([$id_usuario]);
$habilidades_usuario_ids = array_column($stmt->fetchAll(), 'habilidad_id');
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
    <a href="logout.php" class="logout-button">Cerrar sesión</a>
  </div>

  <main class="main-content">
    <header class="page-header">
      <h1>Panel del usuario</h1>
      <p>Selecciona las habilidades que dominas para mantener tu perfil actualizado.</p>
    </header>

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
  </main>
</body>
</html>
