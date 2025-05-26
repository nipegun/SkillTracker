<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");
$passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
$pdo->prepare("INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, password_hash, oficina_id, empresa_id, ciudad, es_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
  ->execute([$_POST['nombre'], $_POST['apellido_paterno'], $_POST['apellido_materno'] ?? '', $_POST['email'], $passwordHash, $_POST['oficina_id'], $_POST['empresa_id'], $_POST['ciudad'], isset($_POST['es_admin']) ? 1 : 0]);
header("Location: panel.php");
?>
