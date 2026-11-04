<?php

function available_vehicles(PDO $conn, string $start, string $end): array {
    $stmt = $conn->prepare('SELECT v.*, c.name AS class_name FROM vehicles v JOIN vehicle_classes c ON c.id = v.class_id
        WHERE NOT EXISTS (SELECT 1 FROM vehicles_closures x WHERE x.vehicle_id = v.id AND x.start_date <= ? AND x.end_date >= ?)
        AND NOT EXISTS (SELECT 1 FROM reservations r WHERE r.vehicle_id = v.id AND r.status = \'confirmed\' AND r.start_date <= ? AND r.end_date >= ?)
        ORDER BY c.name, v.brand, v.model, v.id');
    $stmt->execute([$end, $start, $end, $start]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function model_key(array $vehicle): string {
    return hash('sha256', json_encode([$vehicle['class_id'], $vehicle['brand'], $vehicle['model'], $vehicle['transmission'], $vehicle['motorization'], $vehicle['doors'], $vehicle['seats'], $vehicle['price']]));
}

function rental_price(string $start, string $end, string $daily): array {
    $days = (new DateTimeImmutable($start))->diff(new DateTimeImmutable($end))->days + 1;
    $percent = $days > 9 ? 10 : ($days > 4 ? 5 : 0);
    $parts = explode('.', $daily);
    $cents = (int)$parts[0] * 100 + (int)str_pad(substr($parts[1] ?? '', 0, 2), 2, '0');
    $base = $cents * $days;

    if ($base > 9999999999) {
        throw new RuntimeException('El importe del periodo supera el máximo admitido. Selecciona un periodo más corto.');
    }

    $final = (int)round($base * (100 - $percent) / 100);
    return ['days' => $days, 'percent' => $percent, 'base' => $base / 100, 'final' => $final / 100];
}

function reserve_vehicle(PDO $conn, int $user, string $key, string $start, string $end): void {
    if (!valid_period($start, $end)) {
        throw new RuntimeException('El periodo no es válido.');
    }

    $conn->beginTransaction();
    try {
        $stmt = $conn->query('SELECT * FROM vehicles ORDER BY id');
        $candidates = array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), fn($v) => model_key($v) === $key);
        foreach ($candidates as $candidate) {
            $lock = $conn->prepare('SELECT * FROM vehicles WHERE id = ? FOR UPDATE');
            $lock->execute([$candidate['id']]);
            $vehicle = $lock->fetch(PDO::FETCH_ASSOC);

            if (!$vehicle || model_key($vehicle) !== $key) {
                continue;
            }

            $check = $conn->prepare('SELECT id FROM vehicles_closures WHERE vehicle_id = ? AND start_date <= ? AND end_date >= ? FOR UPDATE');
            $check->execute([$vehicle['id'], $end, $start]);

            if ($check->fetch()) {
                continue;
            }

            $check = $conn->prepare("SELECT id FROM reservations WHERE vehicle_id = ? AND status = 'confirmed' AND start_date <= ? AND end_date >= ? FOR UPDATE");
            $check->execute([$vehicle['id'], $end, $start]);
            
            if ($check->fetch()) {
                continue;
            }

            $price = rental_price($start, $end, $vehicle['price']);
            $class = $conn->prepare('SELECT name FROM vehicle_classes WHERE id = ?');
            $class->execute([$vehicle['class_id']]);
            $description = 'Clase ' . $class->fetchColumn() . ': ' . $vehicle['brand'] . ' ' . $vehicle['model'] . ' / ' . vehicle_label($vehicle['motorization']) . ' / ' . vehicle_label($vehicle['transmission']) . ' / ' . $vehicle['doors'] . ' puertas / ' . $vehicle['seats'] . ' asientos';
            $insert = $conn->prepare('INSERT INTO reservations (user_id, vehicle_id, start_date, end_date, daily_price, total_price, discount_percent, final_price, vehicle_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insert->execute([$user, $vehicle['id'], $start, $end, $vehicle['price'], $price['base'], $price['percent'], $price['final'], $description]);
            $conn->commit();
            $_SESSION['notice'] = 'Reserva confirmada correctamente.';
            return;
        }
        throw new RuntimeException('Ese modelo ya no está disponible. Consulta de nuevo las fechas.');
    } catch (Throwable $e) {
        $conn->rollBack();
        throw $e;
    }
}
