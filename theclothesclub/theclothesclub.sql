-- The Clothes Club database schema
CREATE DATABASE IF NOT EXISTS theclothesclub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE theclothesclub;

-- Users table
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT(4) PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL,
    contrasena VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Prendas (garments) table
CREATE TABLE IF NOT EXISTS Prendas (
    id_prenda INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre_prenda VARCHAR(150) NOT NULL,
    foto VARCHAR(255),
    tipo VARCHAR(50) NOT NULL,
    color VARCHAR(50),
    estilo VARCHAR(50),
    temporada VARCHAR(100),
    descripcion TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Etiquetas (tags) table
CREATE TABLE IF NOT EXISTS Etiquetas (
    id_etiqueta INT AUTO_INCREMENT PRIMARY KEY,
    nombre_etiqueta VARCHAR(100) UNIQUE NOT NULL
) ENGINE=InnoDB;

-- Relación prenda‑etiqueta
CREATE TABLE IF NOT EXISTS Prenda_Etiqueta (
    id_prenda INT NOT NULL,
    id_etiqueta INT NOT NULL,
    PRIMARY KEY (id_prenda, id_etiqueta),
    FOREIGN KEY (id_prenda) REFERENCES Prendas(id_prenda) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_etiqueta) REFERENCES Etiquetas(id_etiqueta) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Outfits table
CREATE TABLE IF NOT EXISTS Outfits (
    id_outfit INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Relación outfit‑prenda
CREATE TABLE IF NOT EXISTS Outfit_Garment (
    id_outfit INT NOT NULL,
    id_prenda INT NOT NULL,
    PRIMARY KEY (id_outfit, id_prenda),
    FOREIGN KEY (id_outfit) REFERENCES Outfits(id_outfit) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_prenda) REFERENCES Prendas(id_prenda) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
