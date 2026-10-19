<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header('Location: auth-page.php');
    exit();
}

if (isset($_POST['submit_logout_admin'])) {
    session_unset();
    session_destroy();
    header('Location: auth-page.php');
    exit();
}

function add_vehicle(PDO $conn, array $vehicle)
{
    try {
        $stmt = $conn->prepare("INSERT INTO vehicles(plate, brand, model, color, doors, seats, transmission, motorization, price, class_id) VALUES(:plate, :brand, :model, :color, :doors, :seats, :transmission, :motorization, :price, :class_id)");
        $stmt->execute([
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
        $stmt = null;
    } catch (PDOException $e) {
        getErrors('db_failed');
        return;
    }
}

try {
    $stmt = $conn->query("SELECT v.id, v.plate, v.brand, v.model, v.color, v.doors, v.seats, v.transmission, v.motorization, v.price, vc.name AS class_name FROM vehicles v INNER JOIN vehicle_classes vc ON v.class_id = vc.id ORDER BY v.id DESC");
    $admin_vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = null;
} catch (PDOException $e) {
    getErrors("db_failed");
    $admin_vehicles = [];
}

try {
    $stmt_classes = $conn->query("SELECT id, name, description FROM vehicle_classes");
    $vehicle_classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
    $stmt_classes = null;
} catch (PDOException $e) {
    $vehicle_classes = [];
}

if (isset($_POST['submit-vehicle'])) {
    $vehicle = [
        'plate' => trim($_POST['plate']),
        'brand' => trim($_POST['brand']),
        'model' => trim($_POST['model']),
        'color' => trim($_POST['color']),
        'doors' => (int)($_POST['doors']),
        'seats' => (int)($_POST['seats']),
        'transmission' => trim($_POST['transmission']),
        'motorization' => trim($_POST['motorization']),
        'price' => trim($_POST['price']),
        'class_id' => (int)($_POST['class_id'])
    ];

    add_vehicle($conn, $vehicle);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador - Alquiler</title>
</head>

<body>
    <header>
        Admin
        <form method="post">
            <input type="submit" name="submit_logout_admin" value="Cerrar Sesión" />
        </form>
    </header>
    <main>
        <form method="post">
            <label>Matrícula</label>
            <input type="text" name="plate" required placeholder="Introduce tu matricula..." />
            <label>Marca</label>
            <input type="text" name="brand" required placeholder="Introduce la marca..." />
            <label>Modelo</label>
            <input type="text" name="model" required placeholder="Introduce el modelo..." />
            <label>Color</label>
            <input type="color" name="color" required placeholder="Introduce el color..." />
            <label>Puertas</label>
            <input type="number" name="doors" required min="2" max="7" placeholder="Introduce el Nº de puertas..." />
            <label>Asientos</label>
            <input type="number" name="seats" required min="1" max="9" placeholder="Introduce el Nº de asientos..." />
            <label>Transmisión</label>
            <select name="transmission" required>
                <option value="">Introduce una transmisión...</option>
                <option value="manual">Manual</option>
                <option value="automatic">Automático</option>
            </select>
            <label>Motorización</label>
            <select name="motorization" required>
                <option value="">Introduce una motorización...</option>
                <option value="gasoline">Gasolina</option>
                <option value="diesel">Diésel</option>
                <option value="hybrid">Híbrido</option>
                <option value="electric">Eléctrico</option>
            </select>
            <label>Precio por Día</label>
            <input type="number" name="price" required placeholder="Introduce el precio por día..." />
            <label>Tipo de Clase</label>
            <select name="class_id" required>
                <option value="">Selecciona una clase...</option>
                <?php foreach ($vehicle_classes as $class): ?>
                    <option value="<?= $class['id'] ?>">
                        Clase <?= htmlspecialchars($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="submit-vehicle" value="Agregar Vehículo" />
        </form>
        <?php if (!isset($admin_vehicles)): ?>
            <p>No hay vehiculos registrados</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Matrícula</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Clase</th>
                        <th>Precio/día</th>
                        <th>Transmisión</th>
                        <th>Motor</th>
                    </tr>
                </thead>
                <?php foreach ($admin_vehicles as $vehicle): ?>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($vehicle['id']) ?></td>
                            <td><?= htmlspecialchars($vehicle['plate']) ?></td>
                            <td><?= htmlspecialchars($vehicle['brand']) ?></td>
                            <td><?= htmlspecialchars($vehicle['model']) ?></td>
                            <td><?= htmlspecialchars($vehicle['class_name']) ?></td>
                            <td><?= htmlspecialchars($vehicle['price']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($vehicle['transmission'])) ?></td>
                            <td><?= ucfirst(htmlspecialchars($vehicle['motorization'])) ?></td>
                        </tr>
                    </tbody>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </main>
</body>

</html>