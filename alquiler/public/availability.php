<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: auth-page.php');
    exit();
}

$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$selected_vehicle_id = $_POST['vehicle_id'] ?? null;

$error_code = null;
$available_vehicles = [];
$calculation_result = null;
$discount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($start_date) || empty($end_date)) {
        getErrors("validation_invalid_dates");
    } else {
        $start_obj = DateTime::createFromFormat('Y-m-d', $start_date);
        $end_obj = DateTime::createFromFormat('Y-m-d', $end_date);

        if (!$start_obj || !$end_obj || $end_obj < $start_obj) {
            getErrors("validation_invalid_dates");
        } else {
            try {
                $stmt =$conn->prepare("
                    SELECT v.id, v.plate, v.brand, v.model, v.price, v.transmission, v.motorization, vc.name AS class_name
                    FROM vehicles v
                    INNER JOIN vehicle_classes vc ON v.class_id = vc.id
                    WHERE v.id NOT IN (
                        SELECT vehicle_id FROM vehicles_closures 
                        WHERE NOT (end_date < :start_date OR start_date > :end_date)
                    )
                    AND v.id NOT IN (
                        SELECT vehicle_id FROM reservations 
                        WHERE status = 'confirmed' 
                        AND NOT (end_date < :start_date OR start_date > :end_date)
                    )
                    ORDER BY v.brand ASC, v.model ASC
                ");
                $stmt->execute([
                    ':start_date' => $start_date,
                    ':end_date'   => $end_date
                ]);
                $available_vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt = null;
            } catch (PDOException $e) {
                getErrors("db_failed");
            }

            if (!empty($selected_vehicle_id) && !$error_code) {
                $rental_days = max(1, $start_obj->diff($end_obj)->days + 1);

                $vehicle_info = null;
                foreach ($available_vehicles as $veh) {
                    if ($veh['id'] == $selected_vehicle_id) {
                        $vehicle_info = $veh;
                        break;
                    }
                }

                if ($vehicle_info) {
                    $price_per_day = (float)$vehicle_info['price'];
                    $price_without_discount = $price_per_day * $rental_days;

                    if ($rental_days > 9) {
                        $discount = 0.10; 
                    } elseif ($rental_days > 4) {
                        $discount = 0.05; 
                    } else {
                        $discount = 0;
                    }

                    $price_with_discount = $price_without_discount * (1 - $discount);

                    $calculation_result = [
                        'id' => $vehicle_info['id'],
                        'brand' => $vehicle_info['brand'],
                        'model' => $vehicle_info['model'],
                        'plate' => $vehicle_info['plate'],
                        'days' => $rental_days,
                        'price_per_day' => $price_per_day,
                        'price_without_discount' => $price_without_discount,
                        'price_with_discount' => $price_with_discount,
                        'discount_percentage' => ($discount * 100)
                    ];
                } else {
                    getErrors("availability_vehicle_closed");
                }
            }
        }
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
            <a href="index.php">Volver al catálogo general</a>
            <a href="my-reservations.php">Ver Mis Reservas</a>
    </header>

    <main>
        <h2>Filtrar vehículos por rango de fechas y calcular presupuesto</h2>

        <form method="POST">
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

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error_code && !empty($available_vehicles)): ?>
                <div>
                    <label><strong>Seleccionar Unidad / Vehículo Concreto:</strong></label><br>
                    <select name="vehicle_id" required>
                        <option value="">Selecciona el vehículo para presupuestar</option>
                        <?php foreach ($available_vehicles as $veh): ?>
                            <option value="<?= $veh['id'] ?>" <?= ($selected_vehicle_id == $veh['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($veh['brand']) ?> <?= htmlspecialchars($veh['model']) ?> (Matrícula: <?= htmlspecialchars($veh['plate']) ?> - <?= number_format($veh['price'], 2) ?> €/día)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <br>
            <?php endif; ?>

            <input type="submit" value="Consultar y Calcular" />
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error_code): ?>
            <h3>Vehículos libres del <?= htmlspecialchars($start_date) ?> al <?= htmlspecialchars($end_date) ?>:</h3>
            
            <?php if (empty($available_vehicles)): ?>
                <p>No hay ningún vehículo disponible para las fechas seleccionadas.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($available_vehicles as $veh): ?>
                        <li>
                            <strong><?= htmlspecialchars($veh['brand']) ?> <?= htmlspecialchars($veh['model']) ?></strong> 
                            (Matrícula: <strong><?= htmlspecialchars($veh['plate']) ?></strong>) - 
                            Clase <?= htmlspecialchars($veh['class_name']) ?> - 
                            <em><?= number_format($veh['price'], 2) ?> € / día</em> 
                            [<?= ucfirst($veh['transmission']) ?>, <?= ucfirst($veh['motorization']) ?>]
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($calculation_result): ?>
            <div>
                <h3>Presupuesto Calculado</h3>
                <p><strong>Vehículo:</strong> <?= htmlspecialchars($calculation_result['brand']) ?> <?= htmlspecialchars($calculation_result['model']) ?></p>
                <p><strong>Matrícula Asignada:</strong> <?= htmlspecialchars($calculation_result['plate']) ?></p>
                <p><strong>Periodo:</strong> Del <?= htmlspecialchars($start_date) ?> al <?= htmlspecialchars($end_date) ?> (<strong><?=$calculation_result['days'] ?> días</strong>)</p>
                <p><strong>Precio por día:</strong> <?= number_format($calculation_result['price_per_day'], 2) ?> €</p>
                <p><strong>Precio Base (Sin Descuento):</strong> <?= number_format($calculation_result['price_without_discount'], 2) ?> €</p>
                
                <?php if ($calculation_result['discount_percentage'] > 0): ?>
                    <p><strong>Descuento Aplicado (<?= $calculation_result['discount_percentage'] ?>%):</strong> -<?= number_format($calculation_result['price_without_discount'] -$calculation_result['price_with_discount'], 2) ?> €</p>
                <?php endif; ?>

                <p><strong>Precio Total Final:</strong> <span><?= number_format($calculation_result['price_with_discount'], 2) ?> €</span></p>

                <form action="reserver.php" method="POST">
                    <input type="hidden" name="vehicle_id" value="<?= $calculation_result['id'] ?>">
                    <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                    <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                    <input type="hidden" name="price_without_discount" value="<?= $calculation_result['price_without_discount'] ?>">
                    <input type="hidden" name="price_with_discount" value="<?= $calculation_result['price_with_discount'] ?>">
                    
                    <input type="submit" name="confirm_reservation" value="Confirmar y Realizar Reserva" />
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>