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

function add_vehicle(PDO $conn, array $vehicle) {
    try {
        $stmt = $conn->prepare("INSERT INTO vehicles(plate, brand, model, color, doors, seats, transmission, motorization, class_id) VALUES(:plate, :brand, :model, :color, :doors, :seats, :transmission, :motorization, :class_id)");
        $stmt->execute([
                        ':plate' => $vehicle['plate'],
                        ':brand' => $vehicle['brand'],
                        ':model' => $vehicle['model'],
                        ':color' => $vehicle['color'],
                        ':doors' => $vehicle['doors'],
                        ':seats' => $vehicle['seats'],
                        ':transmission' => $vehicle['transmission'],
                        ':motorization' => $vehicle['motorization'],
                        ':class_id' => $vehicle['class_id'],
            ]);
    } catch (PDOException $e) {
        getErrors('db_failed');
        return;
    }
}

if(isset($_POST['submit-vehicle'])) {
    $vehicle = [
            'plate' => $_POST['plate'],
            'brand' => $_POST['brand'],
            'model' => $_POST['model'],
            'color' => $_POST['color'],
            'doors' => $_POST['doors'],
            'seats' => $_POST['seats'],
            'transmission' => $_POST['transmission'],
            'motorization' => $_POST['motorization'],
            'class_id' => $_POST['class_id']
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
            <label>Matricula</label>
            <input type="text" name="plate" placeholder="Introduce tu matricula..." />
            <label>Marca</label>
            <input type="text" name="brand" placeholder="Introduce la marca..." />
            <label>Matricula</label>
            <input type="text" name="model" placeholder="Introduce el modelo..." />
            <label>Color</label>
            <input type="color" name="color" placeholder="Introduce el color..." />
            <label>Puertas</label>
            <input type="number" name="doors" placeholder="Introduce el número de puertas..." />
            <label>Asientos</label>
            <input type="number" name="seats" placeholder="Introduce el número de asientos..." />
            <label>Transmición</label>
            <input type="text" name="transmission" placeholder="Introduce la transmición..." />
            <label>Motorización</label>
            <input type="text" name="motorization" placeholder="Introduce la motorización..." />
            <label>Tipo de Clase</label>
            <input type="text" name="class" placeholder="Introduce el tipo de clase..." />
            <input type="submit" value="submit-vehicle" value="Añadir Vehículo" />
        </form>
    </main>
</body>

</html>