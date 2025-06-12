<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

if (isset($_POST['eliminar_grupo_id'])) {
    $grupo_id = (int)$_POST['eliminar_grupo_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE id = ?");
    $stmt->execute([$grupo_id]);
    if ($stmt->fetchColumn() == 0) {
        exit("Grupo no válido.");
    }

    $stmt = $pdo->prepare("DELETE FROM grupos WHERE id = ?");
    $stmt->execute([$grupo_id]);

    header("Location: dashboard_admin.php?tab=grupos");
    exit;
}

if (isset($_POST['nombre_grupo'])) {
    $nombre = trim($_POST['nombre_grupo']);
    if ($nombre === '') {
        exit("El nombre del grupo no puede estar vacío.");
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE nombre = ?");
    $stmt->execute([$nombre]);
    if ($stmt->fetchColumn() > 0) {
        exit("Ya existe un grupo con ese nombre.");
    }
    $id = obtenerSiguienteId($pdo, 'grupos');
    $stmt = $pdo->prepare("INSERT INTO grupos (id, nombre) VALUES (?, ?)");
    $stmt->execute([$id, $nombre]);
    header("Location: dashboard_admin.php?tab=grupos");
    exit;
}

if (isset($_POST['editar_grupo_id'], $_POST['nuevo_nombre'])) {
    $grupo_id = (int)$_POST['editar_grupo_id'];
    $nuevo_nombre = trim($_POST['nuevo_nombre']);
    if ($nuevo_nombre === '') {
        exit("El nombre del grupo no puede estar vacío.");
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE id = ?");
    $stmt->execute([$grupo_id]);
    if ($stmt->fetchColumn() == 0) {
        exit("Grupo no válido.");
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE nombre = ? AND id != ?");
    $stmt->execute([$nuevo_nombre, $grupo_id]);
    if ($stmt->fetchColumn() > 0) {
        exit("Ya existe un grupo con ese nombre.");
    }
    $stmt = $pdo->prepare("UPDATE grupos SET nombre = ? WHERE id = ?");
    $stmt->execute([$nuevo_nombre, $grupo_id]);
    header("Location: dashboard_admin.php?tab=grupos");
    exit;
}

if (isset($_POST['grupo_id'], $_POST['empresa_id'])) {
    $grupo_id = (int)$_POST['grupo_id'];
    $empresa_id = (int)$_POST['empresa_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM grupos WHERE id = ?");
    $stmt->execute([$grupo_id]);
    if ($stmt->fetchColumn() == 0) {
        exit("Grupo no válido.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE id = ?");
    $stmt->execute([$empresa_id]);
    if ($stmt->fetchColumn() == 0) {
        exit("Empresa no válida.");
    }

    $stmt = $pdo->prepare("UPDATE empresas SET grupo_id = ? WHERE id = ?");
    $stmt->execute([$grupo_id, $empresa_id]);
    header("Location: dashboard_admin.php?tab=grupos");
    exit;
}

header("Location: dashboard_admin.php");
exit;
?>
