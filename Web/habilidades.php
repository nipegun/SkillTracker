<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// ---- Actualizar habilidad ----
if (isset($_POST['actualizar_habilidad_id'])) {
    $hid = (int)$_POST['actualizar_habilidad_id'];
    $nombre = trim($_POST['nombre_habilidad']);

    if ($nombre === '') {
        exit("El nombre de la habilidad no puede estar vacío.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM habilidades WHERE id = ?");
    $stmt->execute([$hid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Habilidad no válida.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM habilidades WHERE nombre = ? AND id != ?");
    $stmt->execute([$nombre, $hid]);
    if ($stmt->fetchColumn() > 0) {
        exit("Ya existe una habilidad con ese nombre.");
    }

    $stmt = $pdo->prepare("UPDATE habilidades SET nombre = ? WHERE id = ?");
    $stmt->execute([$nombre, $hid]);

    header("Location: dashboard_admin.php?tab=habilidades");
    exit;
}

// ---- Eliminar habilidad ----
if (isset($_POST['eliminar_habilidad_id'])) {
    $hid = (int)$_POST['eliminar_habilidad_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM habilidades WHERE id = ?");
    $stmt->execute([$hid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Habilidad no válida.");
    }

    $stmt = $pdo->prepare("DELETE FROM habilidades WHERE id = ?");
    $stmt->execute([$hid]);

    header("Location: dashboard_admin.php?tab=habilidades");
    exit;
}

// ---- Renombrar habilidad ----
if (isset($_POST['editar_habilidad_id'], $_POST['nuevo_nombre'])) {
    $hid = (int)$_POST['editar_habilidad_id'];
    $nuevo = trim($_POST['nuevo_nombre']);

    if ($nuevo === '') {
        exit("El nombre de la habilidad no puede estar vacío.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM habilidades WHERE id = ?");
    $stmt->execute([$hid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Habilidad no válida.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM habilidades WHERE nombre = ? AND id != ?");
    $stmt->execute([$nuevo, $hid]);
    if ($stmt->fetchColumn() > 0) {
        exit("Ya existe una habilidad con ese nombre.");
    }

    $stmt = $pdo->prepare("UPDATE habilidades SET nombre = ? WHERE id = ?");
    $stmt->execute([$nuevo, $hid]);

    header("Location: dashboard_admin.php?tab=habilidades");
    exit;
}

// ---- Crear nueva habilidad ----
if (empty($_POST['nombre_habilidad'])) {
    exit("El nombre de la habilidad no puede estar vacío.");
}

$nombre = trim($_POST['nombre_habilidad']);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM habilidades WHERE nombre = ?");
$stmt->execute([$nombre]);
if ($stmt->fetchColumn() > 0) {
    exit("Ya existe una habilidad con ese nombre.");
}

$id = obtenerSiguienteId($pdo, 'habilidades');
$stmt = $pdo->prepare("INSERT INTO habilidades (id, nombre) VALUES (?, ?)");
$stmt->execute([$id, $nombre]);

header("Location: dashboard_admin.php?tab=habilidades");
exit;
?>
