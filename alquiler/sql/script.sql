DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS vehicles_closures;
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
    id INT AUTO_INCREMENT UNIQUE,
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

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(20) NOT NULL, 
    vehicle_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    price_without_discount DECIMAL(10,2) NOT NULL,
    price_with_discount DECIMAL(10,2) NOT NULL,
    status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(dni_nie) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (id, email, password, role_id) 
VALUES (1, 'personalusevictor@gmail.com', '$2y$10$HMxIJO.1DRdTZn6Rh5nlBOiH.XtVz6IyN1Y9kQbetnDaPOBHLgtDC', 1);

INSERT INTO vehicles (plate, brand, model, color, doors, seats, transmission, motorization, price, class_id) VALUES 
('1234ABC', 'Fiat', 'Panda', 'Blanco', 5, 4, 'manual', 'gasoline', 35.00, 1),
('5678DEF', 'Fiat', 'Panda', 'Rojo', 5, 4, 'manual', 'gasoline', 35.00, 1),
('9012GHJ', 'Toyota', 'Aygo', 'Negro', 5, 4, 'automatic', 'hybrid', 40.00, 1);

INSERT INTO vehicles (plate, brand, model, color, doors, seats, transmission, motorization, price, class_id) VALUES 
('3456KLM', 'Volkswagen', 'Golf', 'Gris', 5, 5, 'manual', 'diesel', 55.00, 2),
('7890NPQ', 'Volkswagen', 'Golf', 'Azul', 5, 5, 'automatic', 'diesel', 60.00, 2),
('1122RST', 'Seat', 'Leon', 'Blanco', 5, 5, 'manual', 'gasoline', 50.00, 2),
('3344UVW', 'Seat', 'Leon', 'Negro', 5, 5, 'automatic', 'hybrid', 58.00, 2);

INSERT INTO vehicles (plate, brand, model, color, doors, seats, transmission, motorization, price, class_id) VALUES 
('5566XYZ', 'Nissan', 'Qashqai', 'Plata', 5, 5, 'manual', 'diesel', 75.00, 3),
('7788ABC', 'Nissan', 'Qashqai', 'Blanco', 5, 5, 'automatic', 'hybrid', 80.00, 3),
('9900DEF', 'Hyundai', 'Tucson', 'Rojo', 5, 5, 'automatic', 'diesel', 85.00, 3);

INSERT INTO vehicles (plate, brand, model, color, doors, seats, transmission, motorization, price, class_id) VALUES 
('2468GHI', 'Renault', 'Scenic', 'Azul', 5, 7, 'manual', 'diesel', 90.00, 4),
('1357JKL', 'Citroen', 'SpaceTourer', 'Gris', 5, 9, 'automatic', 'diesel', 120.00, 4);

INSERT INTO vehicles_closures (vehicle_id, start_date, end_date, reason, out_of_service) 
VALUES (1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'maintenance', 1);
