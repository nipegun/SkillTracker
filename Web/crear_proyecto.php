<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

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

