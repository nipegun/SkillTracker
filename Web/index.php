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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/css/index.css">
</head>
<body class="auth-body">
  <div class="auth-background" aria-hidden="true"></div>
  <main class="auth-main">
    <section class="auth-card" aria-labelledby="auth-title">
      <div class="brand">
        <img src="/images/SkillTrackerLogo.png" alt="Logo de SkillTracker" class="SkillTrackerLogo">
        <h1 id="auth-title">SkillTracker</h1>
        <p class="brand-tagline">Gestiona habilidades y proyectos con una experiencia moderna.</p>
      </div>

      <form action="login.php" method="POST" class="auth-form">
        <label class="input-group">
          <span>Email</span>
          <input type="email" name="email" autocomplete="email" required>
        </label>

        <label class="input-group">
          <span>Contraseña</span>
          <input type="password" name="password" autocomplete="current-password" required>
        </label>

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <button type="submit" class="primary-button">Ingresar</button>
      </form>
    </section>
  </main>
</body>
</html>
