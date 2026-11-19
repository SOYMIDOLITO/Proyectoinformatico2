-- ========================================================
-- BASE DE DATOS: The Clothes Club
-- ========================================================

CREATE DATABASE IF NOT EXISTS `theclothesclub`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `theclothesclub`;

-- 1. Tabla de Usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id_usuario` INT(4) NOT NULL,
    `nombre_usuario` VARCHAR(100) NOT NULL,
    `contrasena` VARCHAR(255) NOT NULL,
    `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Prendas
CREATE TABLE IF NOT EXISTS `Prendas` (
    `id_prenda` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT(4) NOT NULL,
    `nombre_prenda` VARCHAR(150) NOT NULL,
    `foto` VARCHAR(255) DEFAULT NULL,
    `tipo` VARCHAR(50) NOT NULL,
    `color` VARCHAR(50) DEFAULT NULL,
    `estilo` VARCHAR(50) DEFAULT NULL,
    `temporada` VARCHAR(50) DEFAULT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_usuario` (`id_usuario`),
    CONSTRAINT `fk_prendas_usuario` 
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Etiquetas
CREATE TABLE IF NOT EXISTS `Etiquetas` (
    `id_etiqueta` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_etiqueta` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla Intermedia Prenda_Etiqueta
CREATE TABLE IF NOT EXISTS `Prenda_Etiqueta` (
    `id_prenda` INT NOT NULL,
    `id_etiqueta` INT NOT NULL,
    PRIMARY KEY (`id_prenda`, `id_etiqueta`),
    INDEX `idx_pe_etiqueta` (`id_etiqueta`),
    CONSTRAINT `fk_pe_prenda` 
        FOREIGN KEY (`id_prenda`) REFERENCES `Prendas` (`id_prenda`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pe_etiqueta` 
        FOREIGN KEY (`id_etiqueta`) REFERENCES `Etiquetas` (`id_etiqueta`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
