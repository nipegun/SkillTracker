CREATE DATABASE IF NOT EXISTS SkillSelector CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE SkillSelector;

-- Tabla de empresas
CREATE TABLE empresas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL UNIQUE
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
