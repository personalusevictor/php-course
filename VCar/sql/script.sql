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
    out_of_service BOOLEAN NOT NULL DEFAULT 1,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (id, email, password, role_id) 
VALUES (1, 'personalusevictor@gmail.com', '$2y$10$HMxIJO.1DRdTZn6Rh5nlBOiH.XtVz6IyN1Y9kQbetnDaPOBHLgtDC', 1);

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    vehicle_description VARCHAR(500) NOT NULL DEFAULT '',
    cancelled_at DATETIME NULL,
    daily_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    discount_percent INT NOT NULL,
    final_price DECIMAL(10,2) NOT NULL,
    status ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
    INDEX reservation_availability (vehicle_id, status, start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (id, email, password, role_id) VALUES
(2, 'victrmartinperez@gmail.com', '$2y$10$pdwUMfUB57ToBJkXZl8ZyuoBoqolBHPkd5m8Fj2AJgbxXZR5J1Pve', 2);

INSERT INTO customers (dni_nie, name, surname, users_id, birth_date, driver_license) VALUES
('12345678Z', 'Víctor', 'Martín Pérez', 2, '1995-06-15', 'demo-license.png');

INSERT INTO vehicles (id, plate, brand, model, color, doors, seats, transmission, motorization, price, class_id) VALUES
(1, '5674NPC', 'Fiat', '500', '#ffffff', 3, 4, 'manual', 'gasoline', 30.00, 1),
(2, '5675NPC', 'Fiat', '500', '#ff0000', 3, 4, 'manual', 'gasoline', 30.00, 1),
(3, '5676NPC', 'Fiat', '500', '#000000', 3, 4, 'manual', 'diesel', 30.00, 1),
(4, '5677NPC', 'Fiat', '500', '#0000ff', 3, 4, 'automatic', 'gasoline', 30.00, 1),
(5, '6781NPD', 'Seat', 'Ibiza', '#ffffff', 5, 5, 'manual', 'gasoline', 40.00, 2),
(6, '6782NPD', 'Seat', 'Ibiza', '#808080', 5, 5, 'manual', 'gasoline', 40.00, 2),
(7, '6783NPD', 'Seat', 'Ibiza', '#000000', 3, 5, 'manual', 'gasoline', 40.00, 2),
(8, '7891NPF', 'Toyota', 'Yaris', '#008000', 5, 5, 'automatic', 'hybrid', 45.00, 2),
(9, '7892NPF', 'Hyundai', 'Kona', '#0000ff', 5, 5, 'automatic', 'electric', 60.00, 3),
(10, '7893NPF', 'Dacia', 'Duster', '#808080', 5, 5, 'manual', 'diesel', 55.00, 3),
(11, '8901NPG', 'Renault', 'Espace', '#000000', 5, 7, 'automatic', 'diesel', 80.00, 4),
(12, '8902NPG', 'Renault', 'Espace', '#ffffff', 5, 5, 'automatic', 'diesel', 80.00, 4);

INSERT INTO vehicles_closures (vehicle_id, start_date, end_date, reason) VALUES
(1, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 'maintenance'),
(3, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 'breakdown'),
(9, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'maintenance'),
(10, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 'itv'),
(10, DATE_ADD(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 21 DAY), 'cleaning');

INSERT INTO reservations (user_id, vehicle_id, start_date, end_date, daily_price, total_price, discount_percent, final_price, status) VALUES
(2, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 30.00, 120.00, 0, 120.00, 'confirmed'),
(2, 5, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), 40.00, 200.00, 5, 190.00, 'confirmed'),
(2, 11, DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 19 DAY), 80.00, 800.00, 10, 720.00, 'confirmed'),
(2, 8, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 45.00, 225.00, 5, 213.75, 'cancelled');

UPDATE reservations r JOIN vehicles v ON v.id = r.vehicle_id JOIN vehicle_classes c ON c.id = v.class_id
SET r.vehicle_description = CONCAT('Clase ', c.name, ': ', v.brand, ' ', v.model, ' / ', CASE v.motorization WHEN 'gasoline' THEN 'Gasolina' WHEN 'diesel' THEN 'Diésel' WHEN 'hybrid' THEN 'Híbrido' ELSE 'Eléctrico' END, ' / ', CASE v.transmission WHEN 'manual' THEN 'Manual' ELSE 'Automático' END, ' / ', v.doors, ' puertas / ', v.seats, ' asientos'),
    r.cancelled_at = CASE WHEN r.status = 'cancelled' THEN NOW() ELSE NULL END;
