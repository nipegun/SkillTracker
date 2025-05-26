<?php
require_once 'auth.php';

requerirLogin();

if (esSuperAdmin()) {
  header("Location: admin/panel.php");
  exit;
} else {
  header("Location: usuario/perfil.php");
  exit;
}
?>
