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
