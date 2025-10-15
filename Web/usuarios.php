<?php
require_once 'db.php';
require_once 'auth.php';

requerirLogin();
if (!esSuperAdmin()) exit("Acceso denegado");

// ---- Actualizar usuario completo ----
if (isset($_POST['actualizar_usuario_id'])) {
    $uid = (int)$_POST['actualizar_usuario_id'];

    $nombre = trim($_POST['nombre'] ?? '');
    $apPat = trim($_POST['apellido_paterno'] ?? '');
    $apMat = trim($_POST['apellido_materno'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $oficinaId = isset($_POST['oficina_id']) && $_POST['oficina_id'] !== '' ? (int)$_POST['oficina_id'] : null;
    $empresaId = isset($_POST['empresa_id']) && $_POST['empresa_id'] !== '' ? (int)$_POST['empresa_id'] : null;
    $esAdmin = isset($_POST['es_admin']) ? 1 : 0;

    if ($email === '') {
        exit("El email es obligatorio.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("Email inválido.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Usuario no válido.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? AND id != ?");
    $stmt->execute([$email, $uid]);
    if ($stmt->fetchColumn() > 0) {
        exit("Ya existe un usuario con ese email.");
    }

    if ($oficinaId !== null) {
        $stmt = $pdo->prepare("SELECT empresa_id, ciudad FROM oficinas WHERE id = ?");
        $stmt->execute([$oficinaId]);
        $oficina = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$oficina) {
            exit("Selecciona una oficina válida.");
        }
        if ($empresaId === null) {
            $empresaId = (int)$oficina['empresa_id'];
        }
        if ($ciudad === '' && !empty($oficina['ciudad'])) {
            $ciudad = $oficina['ciudad'];
        }
    }

    if ($empresaId !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE id = ?");
        $stmt->execute([$empresaId]);
        if ($stmt->fetchColumn() == 0) {
            exit("Selecciona una empresa válida.");
        }
    }

    $nombre = $nombre === '' ? null : $nombre;
    $apPat = $apPat === '' ? null : $apPat;
    $apMat = $apMat === '' ? null : $apMat;
    $ciudad = $ciudad === '' ? null : $ciudad;

    $passwordHash = null;
    if (!empty($_POST['password'])) {
        $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $query = "UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, oficina_id = ?, empresa_id = ?, ciudad = ?, es_admin = ?";
    $params = [$nombre, $apPat, $apMat, $email, $oficinaId, $empresaId, $ciudad, $esAdmin];
    if ($passwordHash !== null) {
        $query .= ", password_hash = ?";
        $params[] = $passwordHash;
    }
    $query .= " WHERE id = ?";
    $params[] = $uid;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    header("Location: dashboard_admin.php?tab=usuarios");
    exit;
}

// ---- Eliminar usuario ----
if (isset($_POST['eliminar_usuario_id'])) {
    $uid = (int)$_POST['eliminar_usuario_id'];

    $stmt = $pdo->prepare("SELECT es_admin FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        exit("Usuario no válido.");
    }

    if (!empty($usuario['es_admin'])) {
        exit("No se puede eliminar el usuario administrador.");
    }

    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);

    header("Location: dashboard_admin.php?tab=usuarios");
    exit;
}

// ---- Renombrar usuario (solo nombre) ----
if (isset($_POST['editar_usuario_id'], $_POST['nuevo_nombre'])) {
    $uid = (int)$_POST['editar_usuario_id'];
    $nuevo = trim($_POST['nuevo_nombre']);

    if ($nuevo === '') {
        exit("El nombre no puede estar vacío.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id = ?");
    $stmt->execute([$uid]);
    if ($stmt->fetchColumn() == 0) {
        exit("Usuario no válido.");
    }

    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
    $stmt->execute([$nuevo, $uid]);

    header("Location: dashboard_admin.php?tab=usuarios");
    exit;
}

// ---- Crear nuevo usuario ----
if (($_POST['accion'] ?? '') === 'crear_usuario') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apPat = trim($_POST['apellido_paterno'] ?? '');
    $apMat = trim($_POST['apellido_materno'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $ciudad = trim($_POST['ciudad'] ?? '');
    $oficinaId = isset($_POST['oficina_id']) && $_POST['oficina_id'] !== '' ? (int)$_POST['oficina_id'] : null;
    $empresaId = isset($_POST['empresa_id']) && $_POST['empresa_id'] !== '' ? (int)$_POST['empresa_id'] : null;
    $esAdmin = isset($_POST['es_admin']) ? 1 : 0;

    if ($email === '' || $password === '') {
        exit("El email y la contraseña son obligatorios.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit("Email inválido.");
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        exit("Ya existe un usuario con ese email.");
    }

    if ($oficinaId !== null) {
        $stmt = $pdo->prepare("SELECT empresa_id, ciudad FROM oficinas WHERE id = ?");
        $stmt->execute([$oficinaId]);
        $oficina = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$oficina) {
            exit("Selecciona una oficina válida.");
        }
        if ($empresaId === null) {
            $empresaId = (int)$oficina['empresa_id'];
        }
        if ($ciudad === '' && !empty($oficina['ciudad'])) {
            $ciudad = $oficina['ciudad'];
        }
    }

    if ($empresaId !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM empresas WHERE id = ?");
        $stmt->execute([$empresaId]);
        if ($stmt->fetchColumn() == 0) {
            exit("Selecciona una empresa válida.");
        }
    }

    $nombre = $nombre === '' ? null : $nombre;
    $apPat = $apPat === '' ? null : $apPat;
    $apMat = $apMat === '' ? null : $apMat;
    $ciudad = $ciudad === '' ? null : $ciudad;

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $id = obtenerSiguienteId($pdo, 'usuarios');
    $stmt = $pdo->prepare("INSERT INTO usuarios (
      id, nombre, apellido_paterno, apellido_materno, email,
      password_hash, oficina_id, empresa_id, ciudad, es_admin
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
      $id,
      $nombre,
      $apPat,
      $apMat,
      $email,
      $passwordHash,
      $oficinaId,
      $empresaId,
      $ciudad,
      $esAdmin
    ]);

    header("Location: dashboard_admin.php?tab=usuarios");
    exit;
}
?>
