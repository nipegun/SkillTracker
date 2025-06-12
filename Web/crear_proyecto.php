<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// ---- Eliminar proyecto ----
if (isset($_POST['eliminar_proyecto_id'])) {
    $pid = (int)$_POST['eliminar_proyecto_id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proyectos WHERE id = ?");
    $stmt->execute([$pid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Proyecto no válido.");
    }

    $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = ?");
    $stmt->execute([$pid]);

    header("Location: dashboard_admin.php?tab=proyectos");
    exit;
}

// ---- Renombrar proyecto ----
if (isset($_POST['editar_proyecto_id'], $_POST['nuevo_nombre'])) {
    $pid = (int)$_POST['editar_proyecto_id'];
    $nuevo = trim($_POST['nuevo_nombre']);

    if ($nuevo === '') {
        exit("El nombre del proyecto no puede estar vacío.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proyectos WHERE id = ?");
    $stmt->execute([$pid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Proyecto no válido.");
    }

    $stmt = $pdo->prepare("UPDATE proyectos SET nombre = ? WHERE id = ?");
    $stmt->execute([$nuevo, $pid]);

    header("Location: dashboard_admin.php?tab=proyectos");
    exit;
}

// ---- Crear nuevo proyecto ----
if (empty($_POST['nombre_proyecto'])) {
    exit("El nombre del proyecto es obligatorio.");
}

$nombre = trim($_POST['nombre_proyecto']);
$descripcion = trim($_POST['descripcion'] ?? '');
$estado = $_POST['estado'] ?? 'No iniciado';
$creador = $_SESSION['usuario_id'];

$id = obtenerSiguienteId($pdo, 'proyectos');
$pdo->prepare("INSERT INTO proyectos (id, nombre, descripcion, estado, creador_id) VALUES (?, ?, ?, ?, ?)")
    ->execute([$id, $nombre, $descripcion, $estado, $creador]);
$proyecto_id = $id;

if (!empty($_POST['usuarios_seleccionados'])) {
    $stmt = $pdo->prepare("INSERT INTO proyecto_usuario (proyecto_id, usuario_id) VALUES (?, ?)");
    foreach ($_POST['usuarios_seleccionados'] as $uid) {
        $stmt->execute([$proyecto_id, $uid]);
    }
}

header("Location: dashboard_admin.php?tab=proyectos");
exit;
?>

