USE theclothesclub;

-- Insertar usuario catalogo si no existe
INSERT IGNORE INTO usuarios (id_usuario, nombre_usuario, contrasena) VALUES (2, 'catalogo', 'catalogo');

-- Limpiar relaciones y prendas para demo limpio
DELETE FROM prenda_etiqueta;
DELETE FROM Prendas WHERE id_usuario IN (1, 2);

-- ============ ARMARIO usuario 1 ============
INSERT INTO Prendas (id_prenda, id_usuario, nombre_prenda, foto, tipo, color, estilo, temporada, descripcion) VALUES
(1,  1, 'Remera Blanca Basica',       '', 'remera',   'blanco',      'casual',    'verano',       'Remera blanca simple de algodon'),
(2,  1, 'Jean Azul Clasico',          '', 'pantalon', 'azul',        'casual',    'todo-el-año',  'Jean de corte recto azul clasico'),
(3,  1, 'Campera de Cuero Negra',     '', 'abrigo',   'negro',       'urbano',    'invierno',     'Campera de cuero negro con cierre'),
(4,  1, 'Pantalon de Vestir Negro',   '', 'pantalon', 'negro',       'formal',    'todo-el-año',  'Pantalon de vestir negro liso'),
(5,  1, 'Camisa Celeste Formal',      '', 'remera',   'celeste',     'formal',    'verano',       'Camisa manga larga celeste'),
(6,  1, 'Buzo Hoodie Gris',           '', 'remera',   'gris',        'casual',    'invierno',     'Buzo canguro con capucha gris'),
(7,  1, 'Zapatillas Urbanas Blancas', '', 'calzado',  'blanco',      'casual',    'todo-el-año',  'Zapatillas blancas deportivas'),
(8,  1, 'Botas de Cuero Marrones',    '', 'calzado',  'marron',      'urbano',    'invierno',     'Botas de cuero suela gruesa'),
(9,  1, 'Falda Plisada Rosa',         '', 'falda',    'rosa',        'femenino',  'verano',       'Falda plisada midi rosa'),
(10, 1, 'Blazer Beige',               '', 'abrigo',   'beige',       'formal',    'primavera',    'Blazer estructurado beige'),
(11, 1, 'Vestido Floral Verano',      '', 'vestido',  'multicolor',  'femenino',  'verano',       'Vestido floral liviano'),
(12, 1, 'Jogger Gris Deportivo',      '', 'pantalon', 'gris',        'deportivo', 'todo-el-año',  'Jogger comodo para deporte'),
(13, 1, 'Remera Rayada Azul',         '', 'remera',   'azul',        'casual',    'verano',       'Remera de rayas azul y blanca'),
(14, 1, 'Zapatos Oxford Negros',      '', 'calzado',  'negro',       'formal',    'todo-el-año',  'Zapatos de cuero oxford negros'),
(15, 1, 'Cardigan Verde Oliva',       '', 'abrigo',   'verde',       'casual',    'primavera',    'Cardigan tejido verde oliva');

-- ============ CATALOGO usuario 2 ============
INSERT INTO Prendas (id_prenda, id_usuario, nombre_prenda, foto, tipo, color, estilo, temporada, descripcion) VALUES
(101,2,'Campera Denim Azul',        '','abrigo',  'azul',  'casual',    'primavera', 'Campera denim clasica'),
(102,2,'Trench Camel',              '','abrigo',  'camel', 'formal',    'primavera', 'Gabardina larga camel'),
(103,2,'Remera Negra Basica',       '','remera',  'negro', 'casual',    'todo-el-año','Remera negra lisa'),
(104,2,'Blusa Blanca Elegante',     '','remera',  'blanco','formal',    'verano',    'Blusa blanca con volados'),
(105,2,'Jean Skinny Negro',         '','pantalon','negro', 'urbano',    'todo-el-año','Jean ajustado negro'),
(106,2,'Pantalon Cargo Verde',      '','pantalon','verde', 'casual',    'primavera', 'Cargo con bolsillos laterales'),
(107,2,'Zapatillas Running Negras', '','calzado', 'negro', 'deportivo', 'todo-el-año','Zapatillas para correr'),
(108,2,'Botas Cowboy Marron',       '','calzado', 'marron','urbano',    'invierno',  'Botas estilo vaquero'),
(109,2,'Falda Mini Negra',          '','falda',   'negro', 'urbano',    'todo-el-año','Falda corta negra'),
(110,2,'Vestido Negro Formal',      '','vestido', 'negro', 'formal',    'todo-el-año','Vestido negro de noche'),
(111,2,'Remera Oversized Blanca',   '','remera',  'blanco','casual',    'verano',    'Remera oversize blanca'),
(112,2,'Pantalon Blanco Lino',      '','pantalon','blanco','casual',    'verano',    'Pantalon lino blanco'),
(113,2,'Mocasines Marrones',        '','calzado', 'marron','formal',    'todo-el-año','Mocasines cuero marron'),
(114,2,'Chaqueta Deportiva Gris',   '','abrigo',  'gris',  'deportivo', 'todo-el-año','Chaqueta deportiva gris'),
(115,2,'Remera Verde Oliva',        '','remera',  'verde', 'casual',    'primavera', 'Remera manga corta verde oliva');

-- ============ ETIQUETAS ============
INSERT IGNORE INTO etiquetas (id_etiqueta, nombre_etiqueta) VALUES
(1,'casual'),(2,'formal'),(3,'verano'),(4,'invierno'),
(5,'otoño'),(6,'primavera'),(7,'todo-el-año'),(8,'urbano'),
(9,'negro'),(10,'blanco'),(11,'azul'),(12,'denim'),
(13,'algodon'),(14,'gris'),(15,'noche'),(16,'oficina'),
(17,'femenino'),(18,'rosa'),(19,'beige'),(20,'deportivo'),
(21,'marron'),(22,'verde'),(23,'celeste'),(24,'multicolor'),
(25,'camel');

-- ============ ETIQUETAS ARMARIO ============
INSERT INTO prenda_etiqueta (id_prenda, id_etiqueta) VALUES
(1,1),(1,3),(1,10),(1,13),
(2,1),(2,7),(2,11),(2,12),
(3,8),(3,4),(3,9),(3,15),
(4,2),(4,9),(4,15),(4,16),
(5,2),(5,3),(5,6),(5,23),
(6,1),(6,4),(6,5),(6,14),
(7,1),(7,7),(7,10),
(8,8),(8,4),(8,5),(8,21),
(9,17),(9,3),(9,6),(9,18),
(10,2),(10,5),(10,6),(10,19),
(11,17),(11,3),(11,6),(11,24),
(12,20),(12,7),(12,14),
(13,1),(13,3),(13,6),(13,11),
(14,2),(14,7),(14,9),(14,16),
(15,1),(15,5),(15,6),(15,22);

-- ============ ETIQUETAS CATALOGO ============
INSERT INTO prenda_etiqueta (id_prenda, id_etiqueta) VALUES
(101,1),(101,6),(101,5),(101,11),
(102,2),(102,6),(102,5),(102,25),
(103,1),(103,7),(103,9),
(104,2),(104,3),(104,6),(104,10),
(105,8),(105,7),(105,9),
(106,1),(106,5),(106,6),(106,22),
(107,20),(107,7),(107,9),
(108,8),(108,4),(108,5),(108,21),
(109,8),(109,7),(109,9),
(110,2),(110,7),(110,9),(110,15),
(111,1),(111,3),(111,10),
(112,1),(112,3),(112,6),(112,10),
(113,2),(113,7),(113,21),
(114,20),(114,7),(114,14),
(115,1),(115,5),(115,6),(115,22);
