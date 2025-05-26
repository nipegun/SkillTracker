<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requerirLogin();
$id_usuario = $_SESSION['usuario_id'];

// Proyectos creados por el usuario
$stmt1 = $pdo->prepare("SELECT * FROM proyectos WHERE creador_id = ?");
$stmt1->execute([$id_usuario]);
$proyectos_creados = $stmt1->fetchAll();

// Proyectos en los que participa
$stmt2 = $pdo->prepare("
  SELECT p.* FROM proyectos p
  JOIN proyecto_usuario pu ON pu.proyecto_id = p.id
  WHERE pu.usuario_id = ? AND p.creador_id != ?
");
$stmt2->execute([$id_usuario, $id_usuario]);
$proyectos_participa = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Mis Proyectos</title>
</head>
<body>
  <h1>Proyectos que has creado</h1>
  <ul>
    <?php if ($proyectos_creados): ?>
      <?php foreach ($proyectos_creados as $p): ?>
        <li>
          <strong><?= htmlspecialchars($p['nombre']) ?></strong><br>
          <?= nl2br(htmlspecialchars($p['descripcion'])) ?>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <li>No has creado ningún proyecto.</li>
    <?php endif; ?>
  </ul>

  <h1>Proyectos donde participas</h1>
  <ul>
    <?php if ($proyectos_participa): ?>
      <?php foreach ($proyectos_participa as $p): ?>
        <li>
          <strong><?= htmlspecialchars($p['nombre']) ?></strong><br>
          <?= nl2br(htmlspecialchars($p['descripcion'])) ?>
        </li>
      <?php endforeach; ?>
    <?php else: ?>
      <li>No estás participando en ningún proyecto.</li>
    <?php endif; ?>
  </ul>

  <p><a href="perfil.php">Volver al perfil</a></p>
</body>
</html>
