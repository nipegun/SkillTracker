<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requerirLogin();
$id_usuario = $_SESSION['usuario_id'];

// Si se ha enviado un nuevo proyecto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_proyecto'])) {
  $pdo->prepare("INSERT INTO proyectos (nombre, descripcion, creador_id) VALUES (?, ?, ?)")
      ->execute([$_POST['nombre_proyecto'], $_POST['descripcion'], $id_usuario]);

  $proyecto_id = $pdo->lastInsertId();

  // Añadir personas al proyecto
  if (!empty($_POST['usuarios_seleccionados'])) {
    $stmt = $pdo->prepare("INSERT INTO proyecto_usuario (proyecto_id, usuario_id) VALUES (?, ?)");
    foreach ($_POST['usuarios_seleccionados'] as $uid) {
      $stmt->execute([$proyecto_id, $uid]);
    }
  }

  header("Location: proyectos.php");
  exit;
}

// Mostrar formulario de búsqueda
$habilidades = $pdo->query("SELECT * FROM habilidades")->fetchAll();
$usuarios_filtrados = [];

if (isset($_GET['habilidades'])) {
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
<html>
<head>
  <meta charset="utf-8">
  <title>Nuevo Proyecto</title>
<script>
function validarProyecto() {
  const nombre = document.querySelector('input[name="nombre_proyecto"]').value.trim();
  if (nombre.length < 3) {
    alert("El nombre del proyecto debe tener al menos 3 caracteres.");
    return false;
  }
  return true;
}
</script>
</head>
<body>
  <h1>Crear nuevo proyecto</h1>

  <form method="POST" onsubmit="return validarProyecto()">
    <label>Nombre del proyecto: <input type="text" name="nombre_proyecto" required></label><br>
    <label>Descripción:<br><textarea name="descripcion" rows="4" cols="50"></textarea></label><br>

    <?php if ($usuarios_filtrados): ?>
      <h3>Selecciona miembros para el proyecto:</h3>
      <?php foreach ($usuarios_filtrados as $u): ?>
        <label>
          <input type="checkbox" name="usuarios_seleccionados[]" value="<?= $u['id'] ?>">
          <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido_paterno']) ?> - <?= $u['email'] ?>
        </label><br>
      <?php endforeach; ?>
    <?php endif; ?>

    <button type="submit">Crear proyecto</button>
  </form>

  <h3>Buscar usuarios por habilidades</h3>
  <form method="GET">
    <?php foreach ($habilidades as $hab): ?>
      <label>
        <input type="checkbox" name="habilidades[]" value="<?= $hab['id'] ?>"
          <?= isset($_GET['habilidades']) && in_array($hab['id'], $_GET['habilidades']) ? 'checked' : '' ?>>
        <?= htmlspecialchars($hab['nombre']) ?>
      </label><br>
    <?php endforeach; ?>
    <button type="submit">Buscar</button>
  </form>

</body>
</html>
