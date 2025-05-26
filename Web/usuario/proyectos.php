<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
$id_usuario = $_SESSION['usuario_id'];

$stmt1 = $pdo->prepare("SELECT * FROM proyectos WHERE creador_id = ?");
$stmt1->execute([$id_usuario]);
$proyectos_creados = $stmt1->fetchAll();

$stmt2 = $pdo->prepare("
  SELECT p.* FROM proyectos p
  JOIN proyecto_usuario pu ON pu.proyecto_id = p.id
  WHERE pu.usuario_id = ? AND p.creador_id != ?
");
$stmt2->execute([$id_usuario, $id_usuario]);
$proyectos_participa = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Mis Proyectos</title></head>
<body>
<h1>Proyectos creados</h1>
<ul>
<?php foreach ($proyectos_creados as $p): ?>
  <li><strong><?= $p['nombre'] ?></strong><br><?= $p['descripcion'] ?></li>
<?php endforeach; ?>
</ul>
<h1>Proyectos donde participas</h1>
<ul>
<?php foreach ($proyectos_participa as $p): ?>
  <li><strong><?= $p['nombre'] ?></strong><br><?= $p['descripcion'] ?></li>
<?php endforeach; ?>
</ul>
</body></html>
