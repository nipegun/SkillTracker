<?php
session_start();

/**
 * Devuelve true si el usuario ha iniciado sesión.
 */
function usuarioAutenticado() {
  return isset($_SESSION['usuario_id']);
}

/**
 * Devuelve true si el usuario es superadministrador.
 */
function esSuperAdmin() {
  return isset($_SESSION['es_admin']) && $_SESSION['es_admin'];
}

/**
 * Redirige a la página de login si no está autenticado.
 */
function requerirLogin() {
  if (!usuarioAutenticado()) {
    header("Location: /index.php");
    exit;
  }
}
