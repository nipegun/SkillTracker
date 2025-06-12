<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// ---- Eliminar oficina ----
if (isset($_POST['eliminar_oficina_id'])) {
    $oficina_id = (int)$_POST['eliminar_oficina_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM oficinas WHERE id = ?");
    $stmt->execute([$oficina_id]);
    if ($stmt->fetchColumn() == 0) {
        exit("Oficina no válida.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE oficina_id = ?");
    $stmt->execute([$oficina_id]);
    if ($stmt->fetchColumn() > 0) {
        exit("No se puede eliminar la oficina porque tiene usuarios asociados.");
    }

    $stmt = $pdo->prepare("DELETE FROM oficinas WHERE id = ?");
    $stmt->execute([$oficina_id]);

    header("Location: dashboard_admin.php?tab=oficinas");
    exit;
}

// ---- Renombrar oficina ----
if (isset($_POST['editar_oficina_id'], $_POST['nuevo_nombre'])) {
    $oficina_id = (int)$_POST['editar_oficina_id'];
    $nuevo_nombre = trim($_POST['nuevo_nombre']);

    if ($nuevo_nombre === '') {
        exit("El nombre de la oficina no puede estar vacío.");
    }

    $stmt = $pdo->prepare("SELECT * FROM oficinas WHERE id = ?");
    $stmt->execute([$oficina_id]);
    $oficina = $stmt->fetch();
    if (!$oficina) {
        exit("Oficina no válida.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM oficinas WHERE nombre = ? AND ciudad = ? AND empresa_id = ? AND id != ?");
    $stmt->execute([$nuevo_nombre, $oficina['ciudad'], $oficina['empresa_id'], $oficina_id]);
    if ($stmt->fetchColumn() > 0) {
        exit("Ya existe una oficina con ese nombre en esa ciudad.");
    }

    $stmt = $pdo->prepare("UPDATE oficinas SET nombre = ? WHERE id = ?");
    $stmt->execute([$nuevo_nombre, $oficina_id]);

    header("Location: dashboard_admin.php?tab=oficinas");
    exit;
}

// ---- Crear nueva oficina ----
if (empty($_POST['nombre_oficina']) || empty($_POST['ciudad']) || empty($_POST['empresa_id'])) {
    exit("Faltan datos obligatorios.");
}

$nombre = trim($_POST['nombre_oficina']);
$ciudad = trim($_POST['ciudad']);
$empresaId = intval($_POST['empresa_id']);

if ($nombre === '' || $ciudad === '' || $empresaId === 0) {
    exit("Los campos no pueden estar vacíos.");
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM oficinas WHERE nombre = ? AND ciudad = ? AND empresa_id = ?");
$stmt->execute([$nombre, $ciudad, $empresaId]);
if ($stmt->fetchColumn() > 0) {
    exit("Ya existe una oficina con ese nombre en esa ciudad.");
}

$id = obtenerSiguienteId($pdo, 'oficinas');
$stmt = $pdo->prepare("INSERT INTO oficinas (id, nombre, empresa_id, ciudad) VALUES (?, ?, ?, ?)");
$stmt->execute([$id, $nombre, $empresaId, $ciudad]);

header("Location: dashboard_admin.php?tab=oficinas");
exit;
?>
