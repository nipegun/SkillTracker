-- Estructura de base de datos para el sistema de gestión de habilidades y proyectos

CREATE TABLE empresas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE oficinas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  ciudad VARCHAR(100) NOT NULL
);

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  apellido_paterno VARCHAR(50) NOT NULL,
  apellido_materno VARCHAR(50),
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  oficina_id INT NOT NULL,
  empresa_id INT NOT NULL,
  ciudad VARCHAR(100) NOT NULL,
  es_admin BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (oficina_id) REFERENCES oficinas(id),
  FOREIGN KEY (empresa_id) REFERENCES empresas(id)
);

CREATE TABLE habilidades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE usuario_habilidad (
  usuario_id INT NOT NULL,
  habilidad_id INT NOT NULL,
  PRIMARY KEY (usuario_id, habilidad_id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  FOREIGN KEY (habilidad_id) REFERENCES habilidades(id)
);

CREATE TABLE proyectos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  descripcion TEXT,
  creador_id INT NOT NULL,
  fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (creador_id) REFERENCES usuarios(id)
);

CREATE TABLE proyecto_usuario (
  proyecto_id INT NOT NULL,
  usuario_id INT NOT NULL,
  PRIMARY KEY (proyecto_id, usuario_id),
  FOREIGN KEY (proyecto_id) REFERENCES proyectos(id),
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

