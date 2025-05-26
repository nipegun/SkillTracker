<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// Validaciones mínimas
if (
  empty($_POST['nombre']) ||
  empty($_POST['apellido_paterno']) ||
  empty($_POST['email']) ||
  empty($_POST['password']) ||
  empty($_POST['oficina_id']) ||
  empty($_POST['empresa_id']) ||
  empty($_POST['ciudad'])
) {
  exit("Faltan campos obligatorios.");
}

// Validar email
$email = trim($_POST['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  exit("Email inválido.");
}

// Comprobar si ya existe un usuario con ese email
$stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetchColumn() > 0) {
  exit("Ya existe un usuario con ese email.");
}

// Crear el hash de la contraseña
$passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Insertar usuario
$stmt = $pdo->prepare("INSERT INTO usuarios (
  nombre, apellido_paterno, apellido_materno, email,
  password_hash, oficina_id, empresa_id, ciudad, es_admin
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
  $_POST['nombre'],
  $_POST['apellido_paterno'],
  $_POST['apellido_materno'] ?? '',
  $email,
  $passwordHash,
  $_POST['oficina_id'],
  $_POST['empresa_id'],
  $_POST['ciudad'],
  isset($_POST['es_admin']) ? 1 : 0
]);

header("Location: panel.php");
exit;
?>
