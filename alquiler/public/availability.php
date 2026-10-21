<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: auth-page.php');
    exit();
}

$selected_vehicle_id = $_POST['vehicle_id'] ?? null;
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

$available_vehicles = [];

try {
    $stmt = $conn->query("
        SELECT v.id, v.plate, v.brand, v.model, v.price, v.transmission, v.motorization, vc.name AS class_name
        FROM vehicles v
        INNER JOIN vehicle_classes vc ON v.class_id = vc.id
        WHERE v.id NOT IN (
            SELECT vehicle_id FROM vehicles_closures
            WHERE CURDATE() BETWEEN start_date AND end_date
        )
        ORDER BY v.brand ASC, v.model ASC
    ");
    $available_vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
} catch (PDOException $e) {
    getErrors('db_failed');
}

$calculation_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selected_vehicle_id) {
    $start_obj = DateTime::createFromFormat('Y-m-d', $start_date);
    $end_obj = DateTime::createFromFormat('Y-m-d', $end_date);

    if ($start_obj && $end_obj && $end_obj >= $start_obj) {
        $rental_days = max(1, $start_obj->diff($end_obj)->days + 1);

        try {
            $stmt_check = $conn->prepare("
                SELECT id FROM vehicles_closures 
                WHERE vehicle_id = :vehicle_id 
                AND NOT (end_date < :start_date OR start_date > :end_date)
            ");
            $stmt_check->execute([
                ':vehicle_id' => $selected_vehicle_id,
                ':start_date' => $start_date,
                ':end_date'   => $end_date
            ]);
            $has_closure = $stmt_check->fetch();

            if (!$has_closure) {
                $stmt_veh = $conn->prepare("SELECT brand, model, price FROM vehicles WHERE id = :id");
                $stmt_veh->execute([':id' => $selected_vehicle_id]);
                $vehicle_info = $stmt_veh->fetch(PDO::FETCH_ASSOC);

                if ($vehicle_info) {
                    $price_per_day = (float)$vehicle_info['price'];
                    $total_price = $price_per_day * $rental_days;

                    $calculation_result = [
                        'brand' => $vehicle_info['brand'],
                        'model' => $vehicle_info['model'],
                        'days' => $rental_days,
                        'price_per_day' => $price_per_day,
                        'total_price' => $total_price
                    ];
                }
            } else {
                getErrors('availability_vehicle_closed');
            }
        } catch (PDOException $e) {
            getErrors('availability_check_failed');
        }
    } else {
        getErrors('validation_invalid_dates');
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Disponibilidad - Alquiler</title>
</head>
<body>
    <header>
        <h1>Panel de Cliente - Comprobador de Disponibilidad</h1>
        <p><a href="index.php">Volver al catálogo general</a></p>
    </header>

    <main>
        <h2>Selecciona un vehículo y calcula tu presupuesto</h2>

        <form method="POST">
            <div>
                <label><strong>Seleccionar Vehículo:</strong></label><br>
                <select name="vehicle_id" required>
                    <option value="">Elige un vehículo...</option>
                    <?php foreach ($available_vehicles as $veh): ?>
                        <option value="<?= $veh['id'] ?>" <?= ($selected_vehicle_id == $veh['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($veh['brand']) ?> <?= htmlspecialchars($veh['model']) ?> (Matrícula: <?= htmlspecialchars($veh['plate']) ?> - Clase <?= htmlspecialchars($veh['class_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <br>

            <div>
                <label><strong>Fecha de Inicio:</strong></label><br>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" min="<?= date('Y-m-d') ?>" required />
            </div>
            <br>

            <div>
                <label><strong>Fecha de Fin:</strong></label><br>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" min="<?= date('Y-m-d') ?>" required />
            </div>
            <br>

            <input type="submit" value="Calcular Disponibilidad y Precio" />
        </form>

        <?php if ($calculation_result): ?>
            <div style="border: 1px solid #4CAF50; padding: 15px; margin-top: 15px; background: #f9f9f9;">
                <h3 style="color: #4CAF50; margin-top: 0;">¡Vehículo Disponible!</h3>
                <p><strong>Vehículo:</strong> <?= htmlspecialchars($calculation_result['brand']) ?> <?= htmlspecialchars($calculation_result['model']) ?></p>
                <p><strong>Periodo:</strong> Del <?= htmlspecialchars($start_date) ?> al <?= htmlspecialchars($end_date) ?> (<strong><?= $calculation_result['days'] ?> días</strong>)</p>
                <p><strong>Precio por día:</strong> <?= number_format($calculation_result['price_per_day'], 2) ?> €</p>
                <p><strong>Precio Total:</strong> <span style="color: #d9534f;"><?= number_format($calculation_result['total_price'], 2) ?> €</span></p>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>