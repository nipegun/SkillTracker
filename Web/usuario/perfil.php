<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requerirLogin();
$id_usuario = $_SESSION['usuario_id'];

// Procesar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Limpiar habilidades actuales
  $pdo->prepare("DELETE FROM usuario_habilidad WHERE usuario_id = ?")->execute([$id_usuario]);

  // Añadir habilidades seleccionadas
  if (!empty($_POST['habilidades'])) {
    $stmt = $pdo->prepare("INSERT INTO usuario_habilidad (usuario_id, habilidad_id) VALUES (?, ?)");
    foreach ($_POST['habilidades'] as $habilidad_id) {
      $stmt->execute([$id_usuario, $habilidad_id]);
    }
  }
  header("Location: perfil.php");
  exit;
}

// Obtener todas las habilidades
$habilidades = $pdo->query("SELECT * FROM habilidades")->fetchAll();

// Obtener habilidades del usuario
$habilidades_usuario = $pdo->prepare("SELECT habilidad_id FROM usuario_habilidad WHERE usuario_id = ?");
$habilidades_usuario->execute([$id_usuario]);
$habilidades_usuario_ids = array_column($habilidades_usuario->fetchAll(), 'habilidad_id');
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Mis habilidades</title>
</head>
<body>
  <h1>Mis habilidades</h1>
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
  <p><a href="nuevo_proyecto.php">Crear nuevo proyecto</a></p>
</body>
</html>
