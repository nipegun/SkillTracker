<?php
session_start();
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
?>

<center>
  <p><img src="/images/SkillTrackerLogo.png" alt="Logo de SkillTracker" class="SkillTrackerLogo"></p>
  <form action="login.php" method="POST">
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Contraseña: <input type="password" name="password" required></label><br>
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
    <button type="submit">Ingresar</button>
  </form>
</center>
