<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// Verificar que el nombre no esté vacío
if (empty($_POST['nombre_empresa']) || empty($_POST['grupo_id'])) {
  exit("Faltan datos obligatorios.");
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
$grupo_id = (int)$_POST['grupo_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE id = ?");
$stmt->execute([$grupo_id]);
if ($stmt->fetchColumn() == 0) {
  exit("Grupo no válido.");
}

$stmt = $pdo->prepare("INSERT INTO empresas (nombre, grupo_id) VALUES (?, ?)");
$stmt->execute([$nombre, $grupo_id]);

header("Location: dashboard_admin.php");
exit;
?>
