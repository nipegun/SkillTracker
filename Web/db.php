<?php
require_once 'config.php';

$pdo = new PDO(
  'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
  DB_USER,
  DB_PASS
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/**
 * Devuelve el ID más pequeño no utilizado en una tabla.
 */
function obtenerSiguienteId(PDO $pdo, string $tabla): int {
    $stmt = $pdo->query("SELECT id FROM {$tabla} ORDER BY id");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    $nextId = 1;
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id === $nextId) {
            $nextId++;
        } elseif ($id > $nextId) {
            break;
        }
    }
    return $nextId;
}
