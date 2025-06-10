<?php
require_once 'auth.php';

requerirLogin();

if (esSuperAdmin()) {
  header("Location: dashboard_admin.php");
  exit;
} else {
  header("Location: dashboard_user.php");
  exit;
}
?>
