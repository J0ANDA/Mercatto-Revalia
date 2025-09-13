-- Configuración de la base de datos
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

CREATE DATABASE mercatto_revalia;
USE mercatto_revalia;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de productos
CREATE TABLE productos (
    id_producto INT PRIMARY KEY AUTO_INCREMENT,
    id_vendedor INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    disponible BOOLEAN DEFAULT TRUE,
    ciudad VARCHAR(100),
    provincia VARCHAR(100),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de fotos del producto
CREATE TABLE fotos_producto (
    id_foto INT PRIMARY KEY AUTO_INCREMENT,
    id_producto INT NOT NULL,
    url_foto VARCHAR(255) NOT NULL,
    descripcion VARCHAR(255),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de carrito
CREATE TABLE carritos (
    id_carrito INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Productos dentro del carrito
CREATE TABLE carrito_productos (
    id_carrito INT,
    id_producto INT,
    cantidad INT DEFAULT 1,
    PRIMARY KEY (id_carrito, id_producto),
    FOREIGN KEY (id_carrito) REFERENCES carritos(id_carrito) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de productos favoritos
CREATE TABLE productos_favoritos (
    id_usuario INT,
    id_producto INT,
    fecha_agregado DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_producto),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de transacciones
CREATE TABLE transacciones (
    id_transaccion INT PRIMARY KEY AUTO_INCREMENT,
    id_comprador INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT DEFAULT 1,
    precio_total DECIMAL(10,2) NOT NULL,
    fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'completada', 'cancelada') DEFAULT 'pendiente',
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de valoraciones
CREATE TABLE valoraciones (
    id_valoracion INT PRIMARY KEY AUTO_INCREMENT,
    id_valorado INT NOT NULL,
    id_valorador INT NOT NULL,
    puntuacion INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT,
    fecha_valoracion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_valorado) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_valorador) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    UNIQUE (id_valorado, id_valorador)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de prueba para usuarios (las contraseñas deberían estar hasheadas en producción)
INSERT INTO usuarios (nombre, email, password) VALUES
('Pepe', 'pepe@example.com', '123'),
('Juan David', 'juanda@example.com', '123'),
('Néstor', 'nestor@example.com', '123');


-- Insertar productos de prueba
INSERT INTO productos (id_vendedor, nombre, descripcion, precio, stock, disponible, ciudad, provincia, fecha_creacion) VALUES
(1, 'iPhone 13 Pro', 
   'iPhone 13 Pro en perfecto estado. Color grafito, 256GB de almacenamiento. Incluye cargador y caja original.', 
   799.99, 1, true, 'Madrid', 'Madrid', NOW()),

(1, 'PlayStation 5', 
   'PS5 nueva, versión con lector de discos. Incluye un mando DualSense y el juego Spider-Man Miles Morales.', 
   549.99, 2, true, 'Barcelona', 'Barcelona', NOW()),

(2, 'MacBook Pro M1', 
   'MacBook Pro 13" con chip M1, 8GB RAM, 256GB SSD. Apenas 6 meses de uso, en perfecto estado.', 
   999.99, 1, true, 'Valencia', 'Valencia', NOW()),

(2, 'Samsung Smart TV 55"', 
   'Smart TV Samsung 55" 4K UHD, modelo 2023. HDR10+, compatible con Alexa y Google Assistant.', 
   649.99, 3, true, 'Sevilla', 'Sevilla', NOW()),

(1, 'Nintendo Switch OLED', 
   'Nintendo Switch modelo OLED en color blanco. Incluye todos los accesorios originales y garantía.', 
   299.99, 4, true, 'Málaga', 'Málaga', NOW());

-- Asociar las imágenes con los productos
INSERT INTO fotos_producto (id_producto, url_foto, descripcion) VALUES
(1, 'img/iphone13Pro.jpeg', 'Imagen principal del iPhone 13 Pro'),
(2, 'img/Play5.jpeg', 'Imagen principal de la PlayStation 5'),
(3, 'img/MacBookProM1.jpeg', 'Imagen principal del MacBook Pro M1'),
(4, 'img/samsungtv.jpeg', 'Imagen principal del Samsung Smart TV'),
(5, 'img/NintendoSwitch.jpeg', 'Imagen principal de la Nintendo Switch OLED');
