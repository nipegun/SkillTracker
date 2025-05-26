<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// Validar que los campos estén presentes
if (empty($_POST['nombre_oficina']) || empty($_POST['ciudad'])) {
  exit("Faltan datos obligatorios.");
}

// Limpiar y normalizar entradas
$nombre = trim($_POST['nombre_oficina']);
$ciudad = trim($_POST['ciudad']);

// Validar que no estén vacíos tras limpiar
if ($nombre === '' || $ciudad === '') {
  exit("Los campos no pueden estar vacíos.");
}

// Comprobar si ya existe una oficina con ese nombre en esa ciudad
$stmt = $pdo->prepare("SELECT COUNT(*) FROM oficinas WHERE nombre = ? AND ciudad = ?");
$stmt->execute([$nombre, $ciudad]);
if ($stmt->fetchColumn() > 0) {
  exit("Ya existe una oficina con ese nombre en esa ciudad.");
}

// Insertar la oficina
$stmt = $pdo->prepare("INSERT INTO oficinas (nombre, ciudad) VALUES (?, ?)");
$stmt->execute([$nombre, $ciudad]);

header("Location: panel.php");
exit;
?>
