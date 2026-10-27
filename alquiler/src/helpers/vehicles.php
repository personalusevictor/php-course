<?php

function get_all_vehicles(PDO $conn): array {
    try {
        $stmt = $conn->query("
            SELECT v.id, v.plate, v.brand, v.model, v.color, v.doors, v.seats, v.transmission, v.motorization, v.price, vc.name AS class_name 
            FROM vehicles v 
            INNER JOIN vehicle_classes vc ON v.class_id = vc.id 
            ORDER BY v.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function get_vehicle_by_id(PDO $conn, int $id): ?array {
    try {
        $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
        return $vehicle ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

function get_vehicle_classes(PDO $conn): array {
    try {
        $stmt = $conn->query("SELECT id, name, description FROM vehicle_classes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function add_vehicle(PDO $conn, array $vehicle): bool {
    try {
        $stmt = $conn->prepare("
            INSERT INTO vehicles(plate, brand, model, color, doors, seats, transmission, motorization, price, class_id) 
            VALUES(:plate, :brand, :model, :color, :doors, :seats, :transmission, :motorization, :price, :class_id)
        ");
        return $stmt->execute([
            ':plate' => $vehicle['plate'],
            ':brand' => $vehicle['brand'],
            ':model' => $vehicle['model'],
            ':color' => $vehicle['color'],
            ':doors' => $vehicle['doors'],
            ':seats' => $vehicle['seats'],
            ':transmission' => $vehicle['transmission'],
            ':motorization' => $vehicle['motorization'],
            ':price' => $vehicle['price'],
            ':class_id' => $vehicle['class_id'],
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function update_vehicle(PDO $conn, int $id, array $vehicle): bool {
    try {
        $stmt = $conn->prepare("
            UPDATE vehicles 
            SET plate = :plate, brand = :brand, model = :model, color = :color, 
                doors = :doors, seats = :seats, transmission = :transmission, 
                motorization = :motorization, price = :price, class_id = :class_id 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':plate' => $vehicle['plate'],
            ':brand' => $vehicle['brand'],
            ':model' => $vehicle['model'],
            ':color' => $vehicle['color'],
            ':doors' => $vehicle['doors'],
            ':seats' => $vehicle['seats'],
            ':transmission' => $vehicle['transmission'],
            ':motorization' => $vehicle['motorization'],
            ':price' => $vehicle['price'],
            ':class_id' => $vehicle['class_id'],
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function delete_vehicle(PDO $conn, int $id): bool {
    try {
        $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

function get_all_closures(PDO $conn): array {
    try {
        $stmt = $conn->query("
            SELECT vc.*, v.plate, v.brand, v.model 
            FROM vehicles_closures vc 
            INNER JOIN vehicles v ON vc.vehicle_id = v.id 
            ORDER BY vc.start_date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function add_closure(PDO $conn, array $closure): bool {
    try {
        $stmt = $conn->prepare("
            INSERT INTO vehicles_closures(vehicle_id, start_date, end_date, reason) 
            VALUES(:vehicle_id, :start_date, :end_date, :reason)
        ");
        return $stmt->execute([
            ':vehicle_id' => $closure['vehicle_id'],
            ':start_date' => $closure['start_date'],
            ':end_date' => $closure['end_date'],
            ':reason' => $closure['reason'],
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function delete_closure(PDO $conn, int $id): bool {
    try {
        $stmt = $conn->prepare("DELETE FROM vehicles_closures WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) {
        return false;
    }
}

function get_closure_by_id(PDO $conn, int $id): ?array {
    try {
        $stmt = $conn->prepare("SELECT * FROM vehicles_closures WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $closure = $stmt->fetch(PDO::FETCH_ASSOC);
        return $closure ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

function update_closure(PDO $conn, int $id, array $closure): bool {
    try {
        $stmt = $conn->prepare("UPDATE vehicles_closures SET vehicle_id = :vehicle_id, start_date = :start_date, end_date = :end_date, reason = :reason");
        return $stmt->execute([
            ':id' => $id,
            ':vehicle_id' => $closure['vehicle_id'],
            ':start_date' => $closure['start_date'],
            ':end_date' => $closure['end_date'],
            ':reason' => $closure['reason']
            ]);
    } catch (PDOException $e) {
        return false;
    }
}
function add_reservation(PDO $conn, array $reservation): bool {
    try {
        $stmt = $conn->prepare("
            INSERT INTO reservations(customer_id, vehicle_id, start_date, end_date, price_without_discount, price_with_discount, status) 
            VALUES(:customer_id, :vehicle_id, :start_date, :end_date, :pw, :pwd, 'confirmed')
        ");
        return $stmt->execute([
            ':customer_id' => $reservation['customer_id'],
            ':vehicle_id'  => $reservation['vehicle_id'],
            ':start_date'  => $reservation['start_date'],
            ':end_date'    => $reservation['end_date'],
            ':pw'          => $reservation['price_without_discount'],
            ':pwd'         => $reservation['price_with_discount']
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

function get_customer_reservations(PDO $conn, string $dni_nie): array {
    try {
        $stmt = $conn->prepare("
            SELECT r.*, v.plate, v.brand, v.model, v.color 
            FROM reservations r
            INNER JOIN vehicles v ON r.vehicle_id = v.id
            WHERE r.customer_id = :dni_nie
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([':dni_nie' => $dni_nie]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function cancel_reservation(PDO $conn, int $reservation_id, string $dni_nie): bool {
    try {
        $stmt = $conn->prepare("
            UPDATE reservations 
            SET status = 'cancelled' 
            WHERE id = :id AND customer_id = :dni_nie
        ");
        return $stmt->execute([
            ':id' => $reservation_id,
            ':dni_nie' => $dni_nie
        ]);
    } catch (PDOException $e) {
        return false;
    }
}