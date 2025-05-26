<?php
require_once 'includes/db.php';
session_start();

// Validar que se hayan recibido los datos esperados
if (!isset($_POST['email'], $_POST['password'])) {
  die("Faltan datos del formulario.");
}

$email = trim($_POST['email']);
$pass = $_POST['password'];

// Validar el formato del email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  die("Email inválido.");
}

try {
  $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
  $stmt->execute([$email]);
  $usuario = $stmt->fetch();

  if ($usuario && password_verify($pass, $usuario['password_hash'])) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['es_admin'] = $usuario['es_admin'];
    header("Location: dashboard.php");
    exit;
  } else {
    echo "Credenciales incorrectas.";
  }
} catch (PDOException $e) {
  // No mostrar el error real al usuario, registrar en logs si hace falta
  error_log("Error en login: " . $e->getMessage());
  echo "Error interno. Intenta más tarde.";
}
?>

