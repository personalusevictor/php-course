<?php
session_start();
require_once '../src/config/connection.php';
require_once '../src/helpers/errors.php';
require_once '../src/helpers/vehicles.php';

$reasons = [
    'itv' => 'Revisión técnica', 
    'maintenance' => 'Mantenimiento', 
    'breakdown' => 'Avería mecánica', 
    'cleaning' => 'Limpieza y desinfección', 
    'accident' => 'Siniestro o accidente', 
    'other' => 'Otros motivos'
];

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

$edit_mode = false;
$vehicle_to_edit = null;

if (isset($_POST['submit-vehicle'])) {
    $vehicle = [
        'plate' => trim($_POST['plate']),
        'brand' => trim($_POST['brand']),
        'model' => trim($_POST['model']),
        'color' => trim($_POST['color']),
        'doors' => (int)$_POST['doors'],
        'seats' => (int)$_POST['seats'],
        'transmission' => trim($_POST['transmission']),
        'motorization' => trim($_POST['motorization']),
        'price' => (float)$_POST['price'],
        'class_id' => (int)$_POST['class_id']
    ];

    if (!empty($_POST['vehicle_id'])) {
        update_vehicle($conn, (int)$_POST['vehicle_id'], $vehicle);
    } else {
        add_vehicle($conn, $vehicle);
    }
    header('Location: admin.php');
    exit();
}

if (isset($_GET['delete_id'])) {
    delete_vehicle($conn, (int)$_GET['delete_id']);
    header('Location: admin.php');
    exit();
}

if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $vehicle_to_edit = get_vehicle_by_id($conn, (int)$_GET['edit_id']);
}

if (isset($_POST['submit-closure'])) {
    $start_date = trim($_POST['start_date']);
    $end_date = trim($_POST['end_date']);
    $vehicle_id = (int)$_POST['vehicle_id'];

    $start_obj = DateTime::createFromFormat('Y-m-d', $start_date);
    $end_obj = DateTime::createFromFormat('Y-m-d', $end_date);

    if (!$start_obj || !$end_obj || $end_obj < $start_obj) {
        getErrors('invalid_date');
        exit();
    }

    $closure = [
        'vehicle_id' => $vehicle_id,
        'start_date' => $start_date,
        'end_date'   => $end_date,
        'reason'     => $_POST['reason'],
    ];
    
    add_closure($conn, $closure);
    header('Location: admin.php');
    exit();
}

if (isset($_GET['delete_closure_id'])) {
    delete_closure($conn, (int)$_GET['delete_closure_id']);
    header('Location: admin.php');
    exit();
}

$admin_vehicles = get_all_vehicles($conn);
$vehicle_classes = get_vehicle_classes($conn);
$closures = get_all_closures($conn);

$today_str = date('Y-m-d');
$vehicles_in_closure_ids = [];
foreach ($closures as $closure) {
    if ($today_str >= $closure['start_date'] && $today_str <= $closure['end_date']) {
        $vehicles_in_closure_ids[] = $closure['vehicle_id'];
    }
}

$vehicles_in_closure_ids = [];
foreach ($closures as $closure) {
    $vehicles_in_closure_ids[] = $closure['vehicle_id'];
}

$vehicles_in_closure_ids = array_unique($vehicles_in_closure_ids);


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
        <h1>Panel de Administración</h1>
        <form method="post">
            <input type="submit" name="submit_logout_admin" value="Cerrar Sesión" />
        </form>
    </header>

    <main>
        <h2><?= $edit_mode ? 'Modificar Vehículo' : 'Agregar Nuevo Vehículo' ?></h2>
        <form method="post">
            <?php if ($edit_mode && $vehicle_to_edit): ?>
                <input type="hidden" name="vehicle_id" value="<?= $vehicle_to_edit['id'] ?>">
            <?php endif; ?>
            <label>Matrícula</label>
            <input type="text" name="plate" required value="<?= $edit_mode ? htmlspecialchars($vehicle_to_edit['plate']) : '' ?>" />
            <label>Marca</label>
            <input type="text" name="brand" required value="<?= $edit_mode ? htmlspecialchars($vehicle_to_edit['brand']) : '' ?>" />
            <label>Modelo</label>
            <input type="text" name="model" required value="<?= $edit_mode ? htmlspecialchars($vehicle_to_edit['model']) : '' ?>" />
            <label>Color</label>
            <input type="color" name="color" required value="<?= $edit_mode ? htmlspecialchars($vehicle_to_edit['color']) : '#000000' ?>" />
            <label>Puertas</label>
            <input type="number" name="doors" required min="2" max="7" value="<?= $edit_mode ? $vehicle_to_edit['doors'] : '' ?>" />
            <label>Asientos</label>
            <input type="number" name="seats" required min="1" max="9" value="<?= $edit_mode ? $vehicle_to_edit['seats'] : '' ?>" />
            <label>Transmisión</label>
            <select name="transmission" required>
                <option value="">Selecciona transmisión...</option>
                <option value="manual" <?= ($edit_mode && $vehicle_to_edit['transmission'] == 'manual') ? 'selected' : '' ?>>Manual</option>
                <option value="automatic" <?= ($edit_mode && $vehicle_to_edit['transmission'] == 'automatic') ? 'selected' : '' ?>>Automático</option>
            </select>
            <label>Motorización</label>
            <select name="motorization" required>
                <option value="">Selecciona motorización...</option>
                <option value="gasoline" <?= ($edit_mode && $vehicle_to_edit['motorization'] == 'gasoline') ? 'selected' : '' ?>>Gasolina</option>
                <option value="diesel" <?= ($edit_mode && $vehicle_to_edit['motorization'] == 'diesel') ? 'selected' : '' ?>>Diésel</option>
                <option value="hybrid" <?= ($edit_mode && $vehicle_to_edit['motorization'] == 'hybrid') ? 'selected' : '' ?>>Híbrido</option>
                <option value="electric" <?= ($edit_mode && $vehicle_to_edit['motorization'] == 'electric') ? 'selected' : '' ?>>Eléctrico</option>
            </select>
            <label>Precio por Día (€)</label>
            <input type="number" step="0.01" name="price" required value="<?= $edit_mode ? $vehicle_to_edit['price'] : '' ?>" />
            <label>Tipo de Clase</label>
            <select name="class_id" required>
                <option value="">Selecciona una clase...</option>
                <?php foreach ($vehicle_classes as $class): ?>
                    <option value="<?= $class['id'] ?>" <?= ($edit_mode && $vehicle_to_edit['class_id'] == $class['id']) ? 'selected' : '' ?>>
                        Clase <?= htmlspecialchars($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="submit-vehicle" value="<?= $edit_mode ? 'Actualizar Vehículo' : 'Agregar Vehículo' ?>" />
            <?php if ($edit_mode): ?>
                <a href="admin.php">Cancelar edición</a>
            <?php endif; ?>
        </form>

        <h2>Vehículos Registrados</h2>
        <?php if (empty($admin_vehicles)): ?>
            <p>No hay vehículos registrados.</p>
        <?php else: ?>
            <table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Matrícula</th>
                        <th>Marca y Modelo</th>
                        <th>Clase</th>
                        <th>Precio/día</th>
                        <th>Transmisión</th>
                        <th>Motor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admin_vehicles as $vehicle): ?>
                        <tr>
                            <td><?= htmlspecialchars($vehicle['id']) ?></td>
                            <td><?= htmlspecialchars($vehicle['plate']) ?></td>
                            <td><?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?></td>
                            <td><?= htmlspecialchars($vehicle['class_name']) ?></td>
                            <td><?= number_format((float)$vehicle['price'], 2) ?> €</td>
                            <td><?= ucfirst(htmlspecialchars($vehicle['transmission'])) ?></td>
                            <td><?= ucfirst(htmlspecialchars($vehicle['motorization'])) ?></td>
                            <td>
                                <a href="admin.php?edit_id=<?= $vehicle['id'] ?>">Editar</a> | 
                                <a href="admin.php?delete_id=<?= $vehicle['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este vehículo?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Gestión de Cierres y Mantenimiento de Vehículos</h2>
        <form method="post">
            <label>Vehículo</label>
            <select name="vehicle_id" required>
                <option value="">Selecciona un vehículo disponible...</option>
                <?php foreach ($admin_vehicles as $vehicle): ?>
                    <?php 
                        if (in_array($vehicle['id'], $vehicles_in_closure_ids)) {
                            continue; 
                        }
                    ?>
                    <option value="<?= $vehicle['id'] ?>">
                        <?= htmlspecialchars($vehicle['plate']) ?> - <?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label>Fecha de Inicio</label>
            <input type="date" name="start_date" min="<?= date('Y-m-d') ?>" required />
            <label>Fecha de Fin</label>
            <input type="date" name="end_date" min="<?= date('Y-m-d') ?>" required />
            <label>Motivo</label>
            <select required name="reason">
                <option value="">Selecciona un motivo...</option>
                <?php foreach($reasons as $key => $value): ?>
                <option value="<?= $key ?>"><?= $value ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="submit-closure" value="Añadir Cierre" />
        </form>

        <h3>Cierres Activos</h3>
        <?php if (empty($closures)): ?>
            <p>No hay cierres o bloqueos registrados.</p>
        <?php else: ?>
            <table border="1" cellpadding="5">
                <thead>
                    <tr>
                        <th>Vehículo</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Motivo</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($closures as $closure): ?>
                        <tr>
                            <td><?= htmlspecialchars($closure['plate']) ?> (<?= htmlspecialchars($closure['brand']) ?> <?= htmlspecialchars($closure['model']) ?>)</td>
                            <td><?= htmlspecialchars($closure['start_date']) ?></td>
                            <td><?= htmlspecialchars($closure['end_date']) ?></td>
                            <td><?= htmlspecialchars($reasons[$closure['reason']] ?? $closure['reason']) ?></td>
                            <td>
                                <a href="admin.php?delete_closure_id=<?= $closure['id'] ?>" onclick="return confirm('¿Eliminar este cierre?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
