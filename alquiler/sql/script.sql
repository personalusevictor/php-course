-- Borramos las tablas en orden inverso de dependencia para evitar errores de Foreign Keys
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS vehicle_classes;
DROP TABLE IF EXISTS customers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (id, name) VALUES 
(1, 'admin'),
(2, 'customer');

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE customers (
    dni_nie VARCHAR(20) PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    users_id INT NOT NULL UNIQUE,
    birth_date DATE NOT NULL,
    driver_license VARCHAR(255) NOT NULL,
    FOREIGN KEY (users_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehicle_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10) NOT NULL UNIQUE,
    description VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO vehicle_classes (name, description) VALUES 
('A', 'Economic / Utilitarian'),
('B', 'Compact / Medium'),
('C', 'SUV / All-Terrain'),
('D', 'Family / Minivan');

CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plate VARCHAR(20) NOT NULL UNIQUE,
    brand VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    color VARCHAR(30) NOT NULL,
    doors INT NOT NULL,
    seats INT NOT NULL,
    transmission ENUM('manual', 'automatic') NOT NULL,
    motorization ENUM('gasoline', 'diesel', 'hybrid', 'electric') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    class_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES vehicle_classes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehicles_closures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason VARCHAR(100),
    out_of_service BOOLEAN NOT NULL,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (id, email, password, role_id) 
VALUES (1, 'personalusevictor@gmail.com', '$2y$10$HMxIJO.1DRdTZn6Rh5nlBOiH.XtVz6IyN1Y9kQbetnDaPOBHLgtDC', 1);