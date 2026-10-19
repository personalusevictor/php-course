<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: auth-page.php');
    exit();
}

if (isset($_POST['submit_logout_costumers'])) {
    session_unset();
    session_destroy();
    header('Location: auth-page.php');
    exit();
}

try {
    $stmt = $conn->query("
        SELECT v.id, v.brand, v.model, v.doors, v.seats, v.transmission, v.motorization, vc.name AS class_name, vc.description AS class_description
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

    $model_key = strtolower(trim($vehicle['brand']) . '-' . trim($vehicle['model']));

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
            'units' => 1
        ];
    } else {
        $grouped_catalog[$class_name]['models'][$model_key]['units']++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Clientes - Alquiler</title>
</head>

<body>
    <header>
        <h1>Panel de Cliente</h1>
        <form method="post">
            <input type="submit" name="submit_logout_costumers" value="Cerrar Sesión" />
        </form>
    </header>
    <main>
        <h2>Catálogo de Vehículos Disponibles</h2>

        <?php if (empty($grouped_catalog)): ?>
            <p>No hay vehículos disponibles en este momento.</p>
        <?php else: ?>
            <?php foreach ($grouped_catalog as $class_name => $class_data): ?>
                <section>
                    <h3>Clase <?= htmlspecialchars($class_name) ?></h3>
                    <p><em><?= htmlspecialchars($class_data['description']) ?></em></p>

                    <div>
                        <?php foreach ($class_data['models'] as $car): ?>
                            <article>
                                <h4>
                                    <?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['model']) ?>
                                    <?php if ($car['units'] > 1): ?>
                                        <small>(<?= $car['units'] ?> unidades disponibles)</small>
                                    <?php endif; ?>
                                </h4>
                                <ul>
                                    <li><strong>Transmisión: </strong> <?= ucfirst(htmlspecialchars($car['transmission'])) ?></li>
                                    <li><strong>Motorización: </strong> <?= ucfirst(htmlspecialchars($car['motorization'])) ?></li>
                                    <li><strong>Puertas: </strong> <?= htmlspecialchars($car['doors']) ?></li>
                                    <li><strong>Asientos: </strong> <?= htmlspecialchars($car['seats']) ?></li>
                                </ul>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</body>

</html>