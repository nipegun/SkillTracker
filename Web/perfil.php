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
  header("Location: perfil.php");
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
<html>
<head>
  <meta charset="utf-8">
  <title>SkillSelector - Panel del usuario</title>
</head>
<body>
  <div class="logout-link">
    <a href="logout.php">Cerrar sesión</a>
  </div>
  <h1>Panel del usuario</h1>
  <form method="POST">
    <?php foreach ($habilidades as $hab): ?>
      <label>
        <input type="checkbox" name="habilidades[]" value="<?= $hab['id'] ?>"
          <?= in_array($hab['id'], $habilidades_usuario_ids) ? 'checked' : '' ?>>
        <?= htmlspecialchars($hab['nombre']) ?>
      </label><br>
    <?php endforeach; ?>
    <button type="submit">Actualizar</button>
  </form>
</body>
</html>
