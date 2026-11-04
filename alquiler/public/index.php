<?php
session_start();
require_once __DIR__ . '/../src/config/connection.php';
require_once __DIR__ . '/../src/helpers/errors.php';
require_once __DIR__ . '/../src/helpers/security.php';
require_once __DIR__ . '/../src/helpers/reservations.php';

if (isset($_POST['submit_logout_costumers'])) {
    verify_csrf();
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

try {
    $stmt = $conn->query("
        SELECT v.id, v.class_id, v.brand, v.model, v.doors, v.seats, v.transmission, v.motorization, v.price, vc.name AS class_name, vc.description AS class_description
        FROM vehicles v
        INNER JOIN vehicle_classes vc ON v.class_id = vc.id
        ORDER BY vc.name ASC, v.brand ASC, v.model ASC
    ");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
} catch (PDOException $e) {
    getErrors("db_failed");
    $vehicles = [];
}

$grouped_catalog = [];
foreach ($vehicles as $vehicle) {
    $class_name = $vehicle['class_name'];
    $class_description = $vehicle['class_description'];

    $model_key = model_key($vehicle);

    if (!isset($grouped_catalog[$class_name])) {
        $grouped_catalog[$class_name] = [
            'description' => $class_description,
            'models' => []
        ];
    }

    if (!isset($grouped_catalog[$class_name]['models'][$model_key])) {
        $grouped_catalog[$class_name]['models'][$model_key] = [
            'brand' => $vehicle['brand'],
            'model' => $vehicle['model'],
            'transmission' => $vehicle['transmission'],
            'motorization' => $vehicle['motorization'],
            'doors' => $vehicle['doors'],
            'seats' => $vehicle['seats'],
            'price' => $vehicle['price'],
        ];
    }
}
require_once __DIR__ . '/../src/views/layout.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catálogo · VCar</title>
<link rel="stylesheet" href="assets/app.css?v=vcar-footer-color-4">
<link rel="icon" type="image/png" href="assets/vcar-memoji.png"><meta name="theme-color" content="#f5ece3"></head>
<body>
<?php site_header('catalog'); ?>
<main id="main" class="page">
<div class="page-intro">
<h1>Alquiler de coches en La Palma</h1>
<p>Consulta nuestros modelos y sus características. Elige tus fechas para comprobar la disponibilidad y reservar.</p>
<a class="button" href="availability.php"><?= icon('calendar-days') ?>Consultar disponibilidad</a>
</div>
<?php if (empty($grouped_catalog)): ?>
<p class="empty-state">No hay modelos registrados en el catálogo.</p>
<?php else: ?>

<?php foreach ($grouped_catalog as $class_name => $class_data): ?>
<section class="fleet-section" id="clase-<?= htmlspecialchars($class_name) ?>">
<div class="fleet-heading"><h2>Clase <?= htmlspecialchars($class_name) ?> <span><?= htmlspecialchars(class_label($class_name)) ?></span></h2></div>
<div class="vehicle-grid">
<?php foreach ($class_data['models'] as $car): ?>
<article class="vehicle-card">
<h3><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
<p class="vehicle-subtitle"><?= htmlspecialchars(vehicle_label($car['motorization'])) ?> · <?= htmlspecialchars(vehicle_label($car['transmission'])) ?></p>
<ul class="specs"><li><?= icon('users') ?><?= (int)$car['seats'] ?> asientos</li><li><?= icon('door-open') ?><?= (int)$car['doors'] ?> puertas</li></ul>
<div class="card-bottom"><span class="price"><?= number_format((float)$car['price'], 2, ',', '.') ?> € <small>/ día</small></span><a class="text-link" href="availability.php" aria-label="Consultar disponibilidad de <?= htmlspecialchars($car['brand'] . ' ' . $car['model'] . ' ' . vehicle_label($car['motorization']) . ' ' . vehicle_label($car['transmission']) . ' ' . $car['doors'] . ' puertas') ?>"><?= icon('search') ?>Ver disponibilidad</a></div>
</article>
<?php endforeach; ?>
</div>
</section>
<?php endforeach; ?>
<p class="footer-note">Precios diarios sin descuento. La disponibilidad y el precio final se calculan al consultar tus fechas.</p>
<?php endif; ?>
</main>
<?php site_footer(); ?>
</body>
</html>
