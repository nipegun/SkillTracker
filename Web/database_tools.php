<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) {
    http_response_code(403);
    echo 'Acceso denegado';
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'export':
        exportDatabase($pdo);
        break;
    case 'import':
        importDatabase($pdo);
        break;
    default:
        header('Location: dashboard_admin.php?tab=inicio');
        exit;
}

function exportDatabase(PDO $pdo): void
{
    try {
        set_time_limit(0);
        $dump = buildDatabaseDump($pdo);
        $filename = 'skilltracker_backup_' . date('Ymd_His') . '.sql';

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($dump));

        echo $dump;
    } catch (Throwable $e) {
        error_log('Error al exportar la base de datos: ' . $e->getMessage());
        if (!headers_sent()) {
            header('Location: dashboard_admin.php?tab=inicio&db_status=export_error');
        }
    }
    exit;
}

function importDatabase(PDO $pdo): void
{
    if (!isset($_FILES['db_file']) || $_FILES['db_file']['error'] !== UPLOAD_ERR_OK) {
        header('Location: dashboard_admin.php?tab=inicio&db_status=invalid_file');
        exit;
    }

    $fileInfo = $_FILES['db_file'];
    $extension = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
    if ($extension !== 'sql') {
        header('Location: dashboard_admin.php?tab=inicio&db_status=invalid_file');
        exit;
    }

    if ($fileInfo['size'] <= 0) {
        header('Location: dashboard_admin.php?tab=inicio&db_status=invalid_file');
        exit;
    }

    // Limitar el tamaño del archivo a 20 MB para evitar importaciones excesivamente grandes.
    if ($fileInfo['size'] > 20 * 1024 * 1024) {
        header('Location: dashboard_admin.php?tab=inicio&db_status=invalid_file');
        exit;
    }

    $sql = file_get_contents($fileInfo['tmp_name']);
    if ($sql === false) {
        header('Location: dashboard_admin.php?tab=inicio&db_status=invalid_file');
        exit;
    }

    try {
        set_time_limit(0);
        executeSqlDump($pdo, $sql);
        header('Location: dashboard_admin.php?tab=inicio&db_status=import_success');
    } catch (Throwable $e) {
        error_log('Error al importar la base de datos: ' . $e->getMessage());
        header('Location: dashboard_admin.php?tab=inicio&db_status=import_error');
    }
    exit;
}

function buildDatabaseDump(PDO $pdo): string
{
    $dump = '';
    $dump .= "-- SkillTracker database export\n";
    $dump .= '-- Generado el: ' . date('Y-m-d H:i:s') . "\n\n";
    $dump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $dump .= "SET time_zone = '+00:00';\n";
    $dump .= "SET NAMES utf8mb4;\n";
    $dump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $dump .= '-- ---------------------------------------------------------\n';
        $dump .= '-- Estructura para la tabla `' . $table . "`\n";
        $dump .= '-- ---------------------------------------------------------\n\n';

        $dump .= 'DROP TABLE IF EXISTS `' . $table . "`;\n";
        $createStmt = $pdo->query('SHOW CREATE TABLE `' . $table . '`')->fetch(PDO::FETCH_ASSOC);
        if (!isset($createStmt['Create Table'])) {
            throw new RuntimeException('No se pudo obtener la definición de la tabla ' . $table);
        }

        $dump .= $createStmt['Create Table'] . ";\n\n";

        $rows = $pdo->query('SELECT * FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            continue;
        }

        $columns = array_keys($rows[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';

        $dump .= '-- Datos de la tabla `' . $table . "`\n";
        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $column) {
                $value = $row[$column];
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = $pdo->quote($value);
                }
            }
            $dump .= 'INSERT INTO `' . $table . '` (' . $columnList . ') VALUES (' . implode(', ', $values) . ");\n";
        }

        $dump .= "\n";
    }

    $dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    return $dump;
}

function executeSqlDump(PDO $pdo, string $sql): void
{
    $statements = parseSqlStatements($sql);
    if (empty($statements)) {
        throw new RuntimeException('El archivo proporcionado no contiene sentencias SQL válidas.');
    }

    $pdo->beginTransaction();
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($statements as $statement) {
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        try {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        } catch (Throwable $inner) {
            // Ignorar.
        }
        throw $e;
    }
}

function parseSqlStatements(string $sql): array
{
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $lines = explode("\n", $sql);
    $cleanSql = [];
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with_comment($trimmed)) {
            continue;
        }
        $cleanSql[] = $line;
    }
    $sql = implode("\n", $cleanSql);

    $statements = [];
    $current = '';
    $inSingleQuote = false;
    $inDoubleQuote = false;
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];

        if ($char === "'" && !$inDoubleQuote) {
            $escaped = $i > 0 && $sql[$i - 1] === '\\';
            if (!$escaped) {
                $inSingleQuote = !$inSingleQuote;
            }
        } elseif ($char === '"' && !$inSingleQuote) {
            $escaped = $i > 0 && $sql[$i - 1] === '\\';
            if (!$escaped) {
                $inDoubleQuote = !$inDoubleQuote;
            }
        }

        if ($char === ';' && !$inSingleQuote && !$inDoubleQuote) {
            $statement = trim($current);
            if ($statement !== '') {
                $statements[] = $statement;
            }
            $current = '';
        } else {
            $current .= $char;
        }
    }

    $current = trim($current);
    if ($current !== '') {
        $statements[] = $current;
    }

    return $statements;
}

function str_starts_with_comment(string $line): bool
{
    return strpos($line, '--') === 0 || strpos($line, '#') === 0;
}
