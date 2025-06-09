<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// Verificar que el nombre no esté vacío
if (empty($_POST['nombre_habilidad'])) {
  exit("El nombre de la habilidad no puede estar vacío.");
}

// Limpiar y normalizar
$nombre = trim($_POST['nombre_habilidad']);

// Comprobar si ya existe una empresa con ese nombre
$stmt = $pdo->prepare("SELECT COUNT(*) FROM habilidades WHERE nombre = ?");
$stmt->execute([$nombre]);
if ($stmt->fetchColumn() > 0) {
  exit("Ya existe una habilidad con ese nombre.");
}

// Insertar la empresa
$stmt = $pdo->prepare("INSERT INTO habilidades (nombre) VALUES (?)");
$stmt->execute([$nombre]);

header("Location: panel.php");
exit;
?>
