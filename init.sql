-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS reactphp_crud;
USE reactphp_crud;

CREATE TABLE IF NOT EXISTS entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO entries (title, description) VALUES
('Primera entrada', 'Esta es la descripción de la primera entrada'),
('Segunda entrada', 'Descripción de la segunda entrada'),
('Tercera entrada', 'Contenido de la tercera entrada');

