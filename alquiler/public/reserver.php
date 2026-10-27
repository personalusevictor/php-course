<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';
require_once '../src/helpers/vehicles.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: auth-page.php');
    exit();
}

$success_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reservation'])) {
    $vehicle_id = (int)$_POST['vehicle_id'];
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $price_without_discount = (float)$_POST['price_without_discount'];
    $price_with_discount = (float)$_POST['price_with_discount'];
    $user_id = (int)$_SESSION['user_id'];

    try {
        $stmt_cust = $conn->prepare("SELECT dni_nie FROM customers WHERE users_id = :user_id");
        $stmt_cust->execute([':user_id' => $user_id]);
        $customer = $stmt_cust->fetch(PDO::FETCH_ASSOC);
        $customer_dni = $customer['dni_nie'] ?? null;
    } catch (PDOException $e) {
        getErrors("db_failed");
        exit();
    }

    if (!$customer_dni) {
        getErrors("db_failed");
        exit();
    }

    try {
        $stmt_closure = $conn->prepare("
            SELECT id FROM vehicles_closures 
            WHERE vehicle_id = :vehicle_id 
            AND NOT (end_date < :start_date OR start_date > :end_date)
        ");
        $stmt_closure->execute([
            ':vehicle_id' => $vehicle_id,
            ':start_date' => $start_date,
            ':end_date'   => $end_date
        ]);
        $is_closed = $stmt_closure->fetch();

        $stmt_res = $conn->prepare("
            SELECT id FROM reservations 
            WHERE vehicle_id = :vehicle_id 
            AND status = 'confirmed' 
            AND NOT (end_date < :start_date OR start_date > :end_date)
        ");
        $stmt_res->execute([
            ':vehicle_id' => $vehicle_id,
            ':start_date' => $start_date,
            ':end_date'   => $end_date
        ]);
        $is_reserved = $stmt_res->fetch();

        if ($is_closed || $is_reserved) {
            getErrors("availability_vehicle_closed");
            exit();
        }

        $reservation_data = [
            'customer_id'            => $customer_dni,
            'vehicle_id'             => $vehicle_id,
            'start_date'             => $start_date,
            'end_date'               => $end_date,
            'price_without_discount' => $price_without_discount,
            'price_with_discount'    => $price_with_discount
        ];

        if (add_reservation($conn, $reservation_data)) {
            $success_message = "¡Reserva realizada con éxito!";
        } else {
            getErrors("db_failed");
            exit();
        }
    } catch (PDOException $e) {
        getErrors("db_failed");
        exit();
    }
} else {
    header('Location: availability.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Reserva - Alquiler</title>
</head>

<body>
    <header>
        <h1>Estado de la Reserva</h1>
            <a href="index.php">Ir al catálogo</a>
            <a href="my-reservations.php">Ver Mis Reservas</a>
            <a href="availability.php">Nueva consulta</a>
    </header>

    <main>
        <?php if ($success_message): ?>
            <div>
                <h2><?= htmlspecialchars($success_message) ?></h2>
                <p>Tu vehículo ha sido reservado y asignado correctamente para las fechas indicadas.</p>
                <hr>
                <p><strong>Detalles guardados:</strong></p>
                <ul>
                    <li><strong>Periodo:</strong> Del <?= htmlspecialchars($start_date) ?> al <?= htmlspecialchars($end_date) ?></li>
                    <li><strong>Precio Original:</strong> <?= number_format($price_without_discount, 2) ?> €</li>
                    <li><strong>Precio Abonado (Con Descuento):</strong> <?= number_format($price_with_discount, 2) ?> €</li>
                </ul>
                <p><a href="my-reservations.php">Ir a la gestión de mis reservas</a></p>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>