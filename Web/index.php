<?php
  session_start();
  $csrf_token = bin2hex(random_bytes(32));
  $_SESSION['csrf_token'] = $csrf_token;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Login | SkillTracker</title>
  <link rel="stylesheet" href="/css/index.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <div class="login-container">
    <img src="/images/SkillTrackerLogo.png" alt="Logo de SkillTracker" class="SkillTrackerLogo">

    <form action="login.php" method="POST" class="login-form">
      <label>Email:<br>
        <input type="email" name="email" required>
      </label><br>

      <label>Contraseña:<br>
        <input type="password" name="password" required>
      </label><br>

      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
      <button type="submit">Ingresar</button>
    </form>
  </div>
</body>
</html>
