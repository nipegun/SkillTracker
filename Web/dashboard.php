<?php
require_once 'auth.php';

requerirLogin();

if (esSuperAdmin()) {
  header("Location: panel.php");
  exit;
} else {
  header("Location: perfil.php");
  exit;
}
?>
