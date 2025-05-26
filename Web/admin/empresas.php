<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// Verificar que el nombre no esté vacío
if (empty($_POST['nombre_empresa'])) {
  exit("El nombre de la empresa no puede estar vacío.");
}

// Limpiar y normalizar
$nombre = trim($_POST['nombre_empresa']);

// Comprobar si ya existe una empresa con ese nombre
$stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE nombre = ?");
$stmt->execute([$nombre]);
if ($stmt->fetchColumn() > 0) {
  exit("Ya existe una empresa con ese nombre.");
}

// Insertar la empresa
$stmt = $pdo->prepare("INSERT INTO empresas (nombre) VALUES (?)");
$stmt->execute([$nombre]);

header("Location: panel.php");
exit;
?>
