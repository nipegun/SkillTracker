<?php
require_once 'db.php';
require_once 'auth.php';
require_once '../lib/tcpdf/tcpdf.php';

requerirLogin();

$id_proyecto = $_GET['id'] ?? null;
$id_usuario = $_SESSION['usuario_id'];

// Verifica si el usuario tiene acceso al proyecto
$stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ? AND creador_id = ?");
$stmt->execute([$id_proyecto, $id_usuario]);
$proyecto = $stmt->fetch();

if (!$proyecto) {
  echo "Acceso denegado o proyecto no encontrado.";
  exit;
}

// Obtener participantes
$stmt2 = $pdo->prepare("
  SELECT u.nombre, u.apellido_paterno, u.email
  FROM proyecto_usuario pu
  JOIN usuarios u ON pu.usuario_id = u.id
  WHERE pu.proyecto_id = ?
");
$stmt2->execute([$id_proyecto]);
$participantes = $stmt2->fetchAll();

// Crear PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$pdf->Write(0, "Proyecto: " . $proyecto['nombre']);
$pdf->Ln(10);
$pdf->Write(0, "Descripción: " . $proyecto['descripcion']);
$pdf->Ln(10);
$pdf->Write(0, "Participantes:");
$pdf->Ln(5);

foreach ($participantes as $p) {
  $linea = $p['nombre'] . ' ' . $p['apellido_paterno'] . ' (' . $p['email'] . ')';
  $pdf->Write(0, $linea);
  $pdf->Ln(5);
}

$pdf->Output('proyecto.pdf', 'I');
