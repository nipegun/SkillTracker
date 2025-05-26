<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");
if (!empty($_POST['nombre_empresa'])) {
  $pdo->prepare("INSERT INTO empresas (nombre) VALUES (?)")->execute([$_POST['nombre_empresa']]);
}
header("Location: panel.php");
?>
