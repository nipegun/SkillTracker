<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// Renombrar empresa
if (isset($_POST['editar_empresa_id'], $_POST['nuevo_nombre'])) {
  $empresa_id = (int)$_POST['editar_empresa_id'];
  $nuevo_nombre = trim($_POST['nuevo_nombre']);

  if ($nuevo_nombre === '') {
    exit("El nombre de la empresa no puede estar vacío.");
  }

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE id = ?");
  $stmt->execute([$empresa_id]);
  if ($stmt->fetchColumn() == 0) {
    exit("Empresa no válida.");
  }

  $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE nombre = ? AND id != ?");
  $stmt->execute([$nuevo_nombre, $empresa_id]);
  if ($stmt->fetchColumn() > 0) {
    exit("Ya existe una empresa con ese nombre.");
  }

  $stmt = $pdo->prepare("UPDATE empresas SET nombre = ? WHERE id = ?");
  $stmt->execute([$nuevo_nombre, $empresa_id]);

  header("Location: dashboard_admin.php?tab=empresas");
  exit;
}

// Crear nueva empresa
if (empty($_POST['nombre_empresa']) || empty($_POST['grupo_id'])) {
  exit("Faltan datos obligatorios.");
}

$nombre = trim($_POST['nombre_empresa']);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE nombre = ?");
$stmt->execute([$nombre]);
if ($stmt->fetchColumn() > 0) {
  exit("Ya existe una empresa con ese nombre.");
}

$grupo_id = (int)$_POST['grupo_id'];
$stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE id = ?");
$stmt->execute([$grupo_id]);
if ($stmt->fetchColumn() == 0) {
  exit("Grupo no válido.");
}

$stmt = $pdo->prepare("INSERT INTO empresas (nombre, grupo_id) VALUES (?, ?)");
$stmt->execute([$nombre, $grupo_id]);

header("Location: dashboard_admin.php?tab=empresas");
exit;
?>
