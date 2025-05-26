<?php
require_once 'includes/db.php';
session_start();

$email = $_POST['email'];
$pass = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($pass, $usuario['password_hash'])) {
  $_SESSION['usuario_id'] = $usuario['id'];
  $_SESSION['es_admin'] = $usuario['es_admin'];
  header("Location: dashboard.php");
} else {
  echo "Credenciales incorrectas";
}
?>
