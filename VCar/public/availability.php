<?php
session_start();

if (($_SESSION['role_id'] ?? null) != 2) {
    header('Location: ' . (isset($_SESSION['user_id']) ? 'index.php' : 'auth-page.php'));
    exit;
}

require_once __DIR__ . '/../src/config/connection.php';
require_once __DIR__ . '/../src/helpers/security.php';
require_once __DIR__ . '/../src/helpers/reservations.php';

$start = is_string($_POST['start_date'] ?? null) ? $_POST['start_date'] : '';
$end = is_string($_POST['end_date'] ?? null) ? $_POST['end_date'] : '';
$error = '';
$models = [];
$prices = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!valid_period($start, $end)) {
        $error = 'Introduce fechas reales desde hoy y una fecha final igual o posterior a la inicial.';
    } else {
        try {
            if (isset($_POST['reserve'])) {
                reserve_vehicle($conn, (int)$_SESSION['user_id'], input('model_key'), $start, $end);
                header('Location: reservations.php');
                exit;
            }
            foreach (available_vehicles($conn, $start, $end) as $vehicle) {
                $key = model_key($vehicle);
                $prices[$key] = rental_price($start, $end, $vehicle['price']);
                $models[$key] = $vehicle;
            }
        } catch (RuntimeException $e) {
            $models = [];
            $prices = [];
            $error = $e instanceof PDOException ? 'No se ha podido completar la operación.' : $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../src/views/layout.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disponibilidad · VCar</title>
    <link rel="stylesheet" href="assets/app.css?v=vcar-footer-color-4">
    <link rel="icon" type="image/png" href="assets/vcar-memoji.png">
    <meta name="theme-color" content="#f5ece3">
</head>

<body>
    <?php site_header('availability'); ?>
    <main id="main" class="page">
        <div class="page-intro">
            <h1>Consultar disponibilidad</h1>
            <p>Elige tus fechas. Te mostraremos los modelos disponibles y su precio final, con el descuento ya aplicado.</p>
        </div>
        <?php if ($error): ?><p role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="post" class="search-form surface">
            <?= csrf_field() ?>
            <div class="field"><label for="start"><?= icon('calendar-days') ?>Fecha de recogida</label><input id="start" type="date" name="start_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($start) ?>" required></div>
            <div class="field"><label for="end"><?= icon('calendar-days') ?>Fecha de devolución</label><input id="end" type="date" name="end_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($end) ?>" required></div>
            <button><?= icon('search') ?>Buscar disponibilidad</button>
        </form>
        <p class="discount-note"><?= icon('percent') ?><span>Ambos días incluidos. De 5 a 9 días, 5 % de descuento. Desde 10 días, 10 %.</span></p>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error): ?>
            <div class="results-title">
                <h2><?= $models ? 'Modelos disponibles' : 'No hay modelos disponibles.' ?></h2><span><?= count($models) ?> opciones · <?= display_date($start) ?> — <?= display_date($end) ?></span>
            </div>
            <?php if (!$models): ?><p class="empty-state">Prueba con otras fechas para encontrar un coche disponible.</p><?php endif; ?>
        <?php endif; ?>
        <div class="vehicle-grid">
            <?php foreach ($models as $key => $car): $price = $prices[$key]; ?>
                <article class="vehicle-card">
                    <div class="vehicle-body">
                        <p class="vehicle-subtitle">Clase <?= htmlspecialchars($car['class_name']) ?></p>
                        <h2><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
                        <p class="vehicle-subtitle"><?= htmlspecialchars(vehicle_label($car['motorization']) . ' · ' . vehicle_label($car['transmission'])) ?></p>
                        <ul class="specs">
                            <li><?= icon('users') ?><?= (int)$car['seats'] ?> asientos</li>
                            <li><?= icon('door-open') ?><?= (int)$car['doors'] ?> puertas</li>
                        </ul>
                        <div class="quote">
                            <div class="quote-line"><span><?= $price['days'] ?> días × <?= number_format((float)$car['price'], 2, ',', '.') ?> €</span><span><?= number_format($price['base'], 2, ',', '.') ?> €</span></div>
                            <div class="quote-line"><span>Descuento <?= $price['percent'] ?> %</span><span>−<?= number_format($price['base'] - $price['final'], 2, ',', '.') ?> €</span></div>
                            <div class="quote-line quote-total"><span>Total</span><span><?= number_format($price['final'], 2, ',', '.') ?> €</span></div>
                        </div>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="start_date" value="<?= htmlspecialchars($start) ?>"><input type="hidden" name="end_date" value="<?= htmlspecialchars($end) ?>"><input type="hidden" name="model_key" value="<?= htmlspecialchars($key) ?>"><button name="reserve"><?= icon('calendar-check') ?>Reservar este modelo</button></form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if ($models): ?><p class="catalog-note">Te asignaremos una unidad disponible del modelo elegido.</p><?php endif; ?>
    </main><?php site_footer(); ?>
</body>
</html>