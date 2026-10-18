<?php 
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: auth-page.php');
    exit();
}

if(isset($_POST['submit_logout_costumers'])) {
    session_unset();
    session_destroy();
    header('Location: auth-page.php');
    exit();
}

try {
    $stmt = $conn->query("
        SELECT v.id, v.brand, v.model, v.color, v.doors, v.seats, v.transmission, v.motorization, vc.name AS class_name, vc.description AS class_description
        FROM vehicles v
        INNER JOIN vehicle_classes vc ON v.class_id = vc.id
        ORDER BY vc.name ASC, v.brand ASC
    ");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
} catch (PDOException $e) {
    getErrors("db_failed");
    $vehicles = [];
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
            <input type="submit" name="submit_logout_costumers" value="Cerrar Sesión"/>
        </form>
    </header>
    <main>
        <h2>Catálogo de Vehículos Disponibles</h2>

        <?php if(empty($vehicles)): ?>
            <p>No hay vehículos disponibles en este momento.</p>
        <?php else: ?>
            <section>
                <?php foreach($vehicles as $vehicle): ?>
                    <article>
                        <h3><?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?></h3>
                        <p><strong>Clase:</strong> <?= htmlspecialchars($vehicle['class_name']) ?> (<?= htmlspecialchars($vehicle['class_description']) ?>)</p>
                        <ul>
                            <li><strong>Transmisión: </strong> <?= ucfirst(htmlspecialchars($vehicle['transmission'])) ?></li>
                            <li><strong>Motorización: </strong> <?= ucfirst(htmlspecialchars($vehicle['motorization'])) ?></li>
                            <li><strong>Puertas: </strong> <?= htmlspecialchars($vehicle['doors']) ?></li>
                            <li><strong>Asientos: </strong> <?= htmlspecialchars($vehicle['seats']) ?></li>
                            <li><strong>Color: </strong><?= htmlspecialchars($vehicle['color']) ?></li>
                        </ul>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>