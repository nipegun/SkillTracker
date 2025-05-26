<?php
$pdo = new PDO('mysql:host=localhost;dbname=tu_base_datos', 'usuario', 'clave');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
