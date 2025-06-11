<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// Validar que los campos estén presentes
if (empty($_POST['nombre_oficina']) || empty($_POST['ciudad']) || empty($_POST['empresa_id'])) {
  exit("Faltan datos obligatorios.");
}

// Limpiar y normalizar entradas
$nombre = trim($_POST['nombre_oficina']);
$ciudad = trim($_POST['ciudad']);
$empresaId = intval($_POST['empresa_id']);

// Validar que no estén vacíos tras limpiar
if ($nombre === '' || $ciudad === '' || $empresaId === 0) {
  exit("Los campos no pueden estar vacíos.");
}

// Comprobar si ya existe una oficina con ese nombre en esa ciudad
$stmt = $pdo->prepare("SELECT COUNT(*) FROM oficinas WHERE nombre = ? AND ciudad = ? AND empresa_id = ?");
$stmt->execute([$nombre, $ciudad, $empresaId]);
if ($stmt->fetchColumn() > 0) {
  exit("Ya existe una oficina con ese nombre en esa ciudad.");
}

// Insertar la oficina
$stmt = $pdo->prepare("INSERT INTO oficinas (nombre, empresa_id, ciudad) VALUES (?, ?, ?)");
$stmt->execute([$nombre, $empresaId, $ciudad]);

header("Location: dashboard_admin.php");
exit;
?>
