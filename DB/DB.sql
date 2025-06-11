-- Crear base de datos
CREATE DATABASE IF NOT EXISTS SkillTracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE SkillTracker;

-- Tabla de grupos
CREATE TABLE grupos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL UNIQUE
);

-- Tabla de empresas
CREATE TABLE empresas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL UNIQUE,
  grupo_id INT DEFAULT NULL,
  FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Tabla de oficinas
CREATE TABLE oficinas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  ciudad VARCHAR(255) NOT NULL,
  UNIQUE(nombre, ciudad)
);

-- Tabla de usuarios
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido_paterno VARCHAR(100) NOT NULL,
  apellido_materno VARCHAR(100),
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  oficina_id INT NOT NULL,
  empresa_id INT NOT NULL,
  ciudad VARCHAR(255) NOT NULL,
  es_admin BOOLEAN NOT NULL DEFAULT 0,
  FOREIGN KEY (oficina_id) REFERENCES oficinas(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla de habilidades
CREATE TABLE habilidades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL UNIQUE
);

-- Tabla de relación usuarios ↔ habilidades
CREATE TABLE usuario_habilidad (
  usuario_id INT NOT NULL,
  habilidad_id INT NOT NULL,
  PRIMARY KEY (usuario_id, habilidad_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (habilidad_id) REFERENCES habilidades(id) ON DELETE CASCADE
);

-- Tabla de proyectos
CREATE TABLE proyectos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  descripcion TEXT,
  estado ENUM('No iniciado', 'Iniciado', 'Pausado', 'Finalizado') NOT NULL DEFAULT 'No iniciado',
  creador_id INT NOT NULL,
  FOREIGN KEY (creador_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla de relación proyectos ↔ usuarios
CREATE TABLE proyecto_usuario (
  proyecto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  PRIMARY KEY (proyecto_id, usuario_id),
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- === Datos iniciales ===

-- Grupo inicial
INSERT INTO grupos (nombre) VALUES ('Grupo Principal');

-- Empresa inicial
INSERT INTO empresas (nombre, grupo_id) VALUES ('Empresa Principal', 1);

-- Oficina inicial
INSERT INTO oficinas (nombre, ciudad) VALUES ('Oficina Central', 'Madrid');

-- Usuario superadmin (contraseña: admin123)
INSERT INTO usuarios (
  nombre,
  apellido_paterno,
  apellido_materno,
  email,
  password_hash,
  oficina_id,
  empresa_id,
  ciudad,
  es_admin
) VALUES (
  'Admin',
  'General',
  '',
  'admin@ejemplo.com',
  '$2y$10$7ndm8FUOwRw5ZLgh42HWDuhS79hclcEY2Fcs5SMT26rV5yHulU98y', -- ← hash de "admin123"
  1,
  1,
  'Madrid',
  1
);

-- Algunas habilidades iniciales
INSERT INTO habilidades (nombre) VALUES 
  ('PHP'),
  ('Python'),
  ('HTML'),
  ('CSS'),
  ('JavaScript'),
  ('SQL'),
  ('Docker'),
  ('Git');
