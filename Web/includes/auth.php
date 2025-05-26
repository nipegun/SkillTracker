<?php
session_start();

function usuarioAutenticado() {
  return isset($_SESSION['usuario_id']);
}

function esSuperAdmin() {
  return isset($_SESSION['es_admin']) && $_SESSION['es_admin'];
}

function requerirLogin() {
  if (!usuarioAutenticado()) {
    header("Location: /index.php");
    exit;
  }
}
?>
