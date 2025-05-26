<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");
if (!empty($_POST['nombre_oficina']) && !empty($_POST['ciudad'])) {
  $pdo->prepare("INSERT INTO oficinas (nombre, ciudad) VALUES (?, ?)")->execute([$_POST['nombre_oficina'], $_POST['ciudad']]);
}
header("Location: panel.php");
?>
