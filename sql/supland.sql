CREATE DATABASE IF NOT EXISTS supland CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE supland;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(190) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('administrador', 'cliente') NOT NULL DEFAULT 'cliente',
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(80) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS productos (
    id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    id_categoria INT UNSIGNED NOT NULL,
    color ENUM('grass','sky','candy','crayon','sun','star') NOT NULL DEFAULT 'sky',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ventas (
    id_venta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL,
    id_usuario INT UNSIGNED NOT NULL,
    clave VARCHAR(64) NOT NULL UNIQUE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detalle_venta (
    id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_venta INT UNSIGNED NOT NULL,
    id_producto INT UNSIGNED NOT NULL,
    nombre_producto VARCHAR(100) NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
) ENGINE=InnoDB;

INSERT IGNORE INTO categorias VALUES
    (1, 'Educativos', 'Aprender jugando.'),
    (2, 'Creativos', 'Crear e imaginar.'),
    (3, 'Aventura', 'Explorar nuevos mundos.'),
    (4, 'Peluches', 'Amigos para cada aventura.');

INSERT IGNORE INTO productos VALUES
    (1,'Smart Puzzle','Rompecabezas para desarrollar la lógica.',15.75,25,1,'grass',1),
    (2,'Number Blocks','Bloques de números para aprender matemáticas.',13.50,20,1,'sun',1),
    (3,'Mini Science Lab','Kit de experimentos y descubrimientos.',22.99,12,1,'sky',1),
    (4,'Abecedario Magnético','Letras para formar las primeras palabras.',12.00,30,1,'candy',1),
    (5,'Mapa del Mundo','Descubre países, continentes y océanos.',19.50,15,1,'sky',1),
    (6,'Arcoíris Creativo','Piezas de colores para construir.',21.00,18,2,'candy',1),
    (7,'Mini Chef','Utensilios de juguete para imaginar recetas.',29.90,12,2,'sun',1),
    (8,'Taller de Pintura','Pinceles y colores para pequeños artistas.',17.50,24,2,'crayon',1),
    (9,'Plastilina Creativa','Modela figuras y personajes.',9.99,35,2,'grass',1),
    (10,'Rocket Builder','Construye un cohete y explora.',24.99,20,3,'sky',1),
    (11,'Rocket Explorer','Nave espacial para nuevas aventuras.',24.50,10,3,'crayon',1),
    (12,'Tren de Madera','Un recorrido lleno de imaginación.',32.00,9,3,'sun',1),
    (13,'Mi Primer Dino','Un compañero jurásico suave.',18.50,15,4,'grass',1),
    (14,'Osito Soft Friends','Osito de peluche para abrazar.',18.50,20,4,'candy',1),
    (15,'Conejo de las Nubes','Un suave compañero de aventuras.',16.90,14,4,'star',1);
