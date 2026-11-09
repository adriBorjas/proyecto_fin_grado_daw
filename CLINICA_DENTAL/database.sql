-- Crear base de datos
CREATE DATABASE IF NOT EXISTS clinica_dental;
USE clinica_dental;

-- Tabla usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(16) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    direccion TEXT,
    telefono VARCHAR(9),
    rol TINYINT NOT NULL DEFAULT 1
);

ALTER TABLE usuarios MODIFY direccion VARCHAR(255) NULL;
ALTER TABLE usuarios MODIFY telefono VARCHAR(20) NULL;

-- Tabla productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    stock INT NOT NULL DEFAULT 0
);

-- Tabla carrito (para almacenar productos temporalmente)
CREATE TABLE carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_producto) REFERENCES productos(id)
);

-- Tabla citas
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'activa',
    razon_cancelacion VARCHAR(255) NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    UNIQUE(fecha, hora_inicio, estado) -- Evita solapamientos entre citas con el mismo estado
);

CREATE TABLE dias_no_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (fecha)
);

-- Tabla para horarios específicos por fechas
CREATE TABLE horarios_especificos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    UNIQUE KEY fecha_hora (fecha, hora_inicio)
);

-- Tabla para notificaciones de cancelaciones de citas
CREATE TABLE IF NOT EXISTS notificaciones_cancelaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_cita INT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    razon VARCHAR(255) NOT NULL,
    leido BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de horarios disponibles
CREATE TABLE horarios_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana TINYINT NOT NULL, -- 1: Lunes, 2: Martes, etc.
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    UNIQUE(dia_semana, hora_inicio)
);

-- Insertar los horarios disponibles para cada día de la semana
INSERT INTO horarios_disponibles (dia_semana, hora_inicio, hora_fin) VALUES
-- Lunes (1)
(1, '09:00:00', '10:00:00'), (1, '10:00:00', '11:00:00'), (1, '11:00:00', '12:00:00'), (1, '12:00:00', '13:00:00'),
(1, '16:00:00', '17:00:00'), (1, '17:00:00', '18:00:00'), (1, '18:00:00', '19:00:00'), (1, '19:00:00', '20:00:00'), (1, '20:00:00', '21:00:00'),
-- Martes (2)
(2, '09:00:00', '10:00:00'), (2, '10:00:00', '11:00:00'), (2, '11:00:00', '12:00:00'), (2, '12:00:00', '13:00:00'),
(2, '16:00:00', '17:00:00'), (2, '17:00:00', '18:00:00'), (2, '18:00:00', '19:00:00'), (2, '19:00:00', '20:00:00'), (2, '20:00:00', '21:00:00'),
-- Miércoles (3)
(3, '09:00:00', '10:00:00'), (3, '10:00:00', '11:00:00'), (3, '11:00:00', '12:00:00'), (3, '12:00:00', '13:00:00'),
(3, '16:00:00', '17:00:00'), (3, '17:00:00', '18:00:00'), (3, '18:00:00', '19:00:00'), (3, '19:00:00', '20:00:00'), (3, '20:00:00', '21:00:00'),
-- Jueves (4)
(4, '09:00:00', '10:00:00'), (4, '10:00:00', '11:00:00'), (4, '11:00:00', '12:00:00'), (4, '12:00:00', '13:00:00'),
(4, '16:00:00', '17:00:00'), (4, '17:00:00', '18:00:00'), (4, '18:00:00', '19:00:00'), (4, '19:00:00', '20:00:00'), (4, '20:00:00', '21:00:00'),
-- Viernes (5)
(5, '09:00:00', '10:00:00'), (5, '10:00:00', '11:00:00'), (5, '11:00:00', '12:00:00'), (5, '12:00:00', '13:00:00'),
(5, '16:00:00', '17:00:00'), (5, '17:00:00', '18:00:00'), (5, '18:00:00', '19:00:00'), (5, '19:00:00', '20:00:00'), (5, '20:00:00', '21:00:00');
