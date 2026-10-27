<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';
require_once '../src/helpers/vehicles.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: auth-page.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("SELECT dni_nie FROM customers WHERE users_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);
$dni_nie = $customer['dni_nie'] ?? '';

if (isset($_POST['cancel_reservation_id'])) {
    cancel_reservation($conn, (int)$_POST['cancel_reservation_id'], $dni_nie);
    header('Location: my-reservations.php');
    exit();
}

$reservations = get_customer_reservations($conn, $dni_nie);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas - Alquiler</title>
</head>

<body>
    <header>
        <h1>Mis Reservas</h1>
        <a href="index.php">Volver al catálogo</a>
        <a href="availability.php">Comprobar disponibilidad</a>
    </header>

    <main>
        <h2>Historial de Alquileres</h2>
        <?php if (empty($reservations)): ?>
            <p>No tienes ninguna reserva registrada.</p>
        <?php else: ?>
            <table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th>Vehículo (Modelo)</th>
                        <th>Matrícula Asignada</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Precio sin Descuento</th>
                        <th>Precio con Descuento</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td><?= htmlspecialchars($res['brand']) ?> <?= htmlspecialchars($res['model']) ?></td>
                            <td><strong><?= htmlspecialchars($res['plate']) ?></strong></td>
                            <td><?= htmlspecialchars($res['start_date']) ?></td>
                            <td><?= htmlspecialchars($res['end_date']) ?></td>
                            <td><?= number_format((float)$res['price_without_discount'], 2) ?> €</td>
                            <td><?= number_format((float)$res['price_with_discount'], 2) ?> €</td>
                            <td>
                                <?= $res['status'] === 'confirmed' ? '<span>Confirmada</span>' : '<span>Cancelada</span>' ?>
                            </td>
                            <td>
                                <?php if ($res['status'] === 'confirmed'): ?>
                                    <form method="POST" onsubmit="return confirm('¿Estás seguro de cancelar esta reserva?');">
                                        <input type="hidden" name="cancel_reservation_id" value="<?= $res['id'] ?>">
                                        <input type="submit" value="Cancelar" />
                                    </form>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>

</html>