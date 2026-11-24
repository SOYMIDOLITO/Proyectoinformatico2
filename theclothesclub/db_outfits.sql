-- SQL to add Outfits support to theclothesclub database
CREATE TABLE IF NOT EXISTS `Outfits` (
    `id_outfit` INT AUTO_INCREMENT PRIMARY KEY,
    `id_usuario` INT(4) NOT NULL,
    `nombre` VARCHAR(150) NOT NULL,
    `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_outfits_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `Outfit_Garment` (
    `id_outfit` INT NOT NULL,
    `id_prenda` INT NOT NULL,
    PRIMARY KEY (`id_outfit`,`id_prenda`),
    CONSTRAINT `fk_og_outfit`
        FOREIGN KEY (`id_outfit`) REFERENCES `Outfits`(`id_outfit`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_og_prenda`
        FOREIGN KEY (`id_prenda`) REFERENCES `Prendas`(`id_prenda`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;