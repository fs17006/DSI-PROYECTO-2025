
CREATE DATABASE IF NOT EXISTS sistema_web;
USE sistema_web;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    perfil ENUM('ADMINISTRADOR', 'MONITOREO', 'ESTANDAR') NOT NULL DEFAULT 'ESTANDAR',
    activo BOOLEAN DEFAULT TRUE
);

INSERT INTO usuarios (usuario, contrasena, perfil) VALUES
('administrador', '123', 'ADMINISTRADOR'),
('monitoreo', '123', 'MONITOREO'),
('estandar', '123', 'ESTANDAR');

CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    codigo VARCHAR(20),
    actividad_economica VARCHAR(100),
    telefono VARCHAR(20),
    correo VARCHAR(100)
);

CREATE TABLE facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_factura VARCHAR(50),
    fecha DATE,
    monto DECIMAL(10,2),
    proveedor_id INT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
);

CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    factura_id INT NOT NULL,
    fecha_pago DATE,
    monto_pagado DECIMAL(10,2),
    estado ENUM('PAGADO', 'PENDIENTE') DEFAULT 'PENDIENTE',
    FOREIGN KEY (factura_id) REFERENCES facturas(id)
);

