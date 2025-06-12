<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// ---- Actualizar usuario completo ----
if (isset($_POST['actualizar_usuario_id'])) {
    $uid = (int)$_POST['actualizar_usuario_id'];

    if (
        empty($_POST['nombre']) ||
        empty($_POST['apellido_paterno']) ||
        empty($_POST['email']) ||
        empty($_POST['oficina_id']) ||
        empty($_POST['empresa_id']) ||
        empty($_POST['ciudad'])
    ) {
        exit("Faltan campos obligatorios.");
    }

    $nombre = trim($_POST['nombre']);
    $apPat = trim($_POST['apellido_paterno']);
    $apMat = trim($_POST['apellido_materno'] ?? '');
    $email = trim($_POST['email']);
    $oficinaId = (int)$_POST['oficina_id'];
    $empresaId = (int)$_POST['empresa_id'];
    $ciudad = trim($_POST['ciudad']);
    $esAdmin = isset($_POST['es_admin']) ? 1 : 0;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("Email inválido.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Usuario no válido.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? AND id != ?");
    $stmt->execute([$email, $uid]);
    if ($stmt->fetchColumn() > 0) {
        exit("Ya existe un usuario con ese email.");
    }

    $passwordHash = null;
    if (!empty($_POST['password'])) {
        $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $query = "UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, oficina_id = ?, empresa_id = ?, ciudad = ?, es_admin = ?";
    $params = [$nombre, $apPat, $apMat, $email, $oficinaId, $empresaId, $ciudad, $esAdmin];
    if ($passwordHash !== null) {
        $query .= ", password_hash = ?";
        $params[] = $passwordHash;
    }
    $query .= " WHERE id = ?";
    $params[] = $uid;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    header("Location: dashboard_admin.php?tab=usuarios");
    exit;
}

// ---- Eliminar usuario ----
if (isset($_POST['eliminar_usuario_id'])) {
    $uid = (int)$_POST['eliminar_usuario_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Usuario no válido.");
    }

    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);

    header("Location: dashboard_admin.php?tab=usuarios");
    exit;
}

// ---- Renombrar usuario (solo nombre) ----
if (isset($_POST['editar_usuario_id'], $_POST['nuevo_nombre'])) {
    $uid = (int)$_POST['editar_usuario_id'];
    $nuevo = trim($_POST['nuevo_nombre']);

    if ($nuevo === '') {
        exit("El nombre no puede estar vacío.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Usuario no válido.");
    }

    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
    $stmt->execute([$nuevo, $uid]);

    header("Location: dashboard_admin.php?tab=usuarios");
    exit;
}

// ---- Crear nuevo usuario ----
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
$id = obtenerSiguienteId($pdo, 'usuarios');
$stmt = $pdo->prepare("INSERT INTO usuarios (
  id, nombre, apellido_paterno, apellido_materno, email,
  password_hash, oficina_id, empresa_id, ciudad, es_admin
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
  $id,
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

header("Location: dashboard_admin.php?tab=usuarios");
exit;
?>
