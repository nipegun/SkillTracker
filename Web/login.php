<?php

require_once 'db.php';
session_start();

// Verificar que se recibieron los datos esperados
if (!isset($_POST['email'], $_POST['password'], $_POST['csrf_token'])) {
  die("Faltan datos del formulario.");
}

// Validar token CSRF
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
  die("Token CSRF inválido.");
}
// Eliminar el token de sesión para que no pueda reutilizarse
unset($_SESSION['csrf_token']);

$email = trim($_POST['email']);
$pass = $_POST['password'];

// Validar el formato del email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  die("Email o contraseña incorrectos."); // Mensaje neutro para evitar enumeración
}

try {
  $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
  $stmt->execute([$email]);
  $usuario = $stmt->fetch();

  if ($usuario && password_verify($pass, $usuario['password_hash'])) {
    // Regenerar ID de sesión para evitar fijación
    session_regenerate_id(true);
    
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['es_admin'] = $usuario['es_admin'];
    header("Location: dashboard.php");
    exit;
  } else {
    // Mensaje neutro
    echo "Email o contraseña incorrectos.";
  }

} catch (PDOException $e) {
  // Registrar error sin exponerlo al usuario
  error_log("Error en login: " . $e->getMessage());
  echo "Error interno. Intenta más tarde.";
}
?>
