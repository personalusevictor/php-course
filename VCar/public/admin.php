<?php
session_start();

require_once __DIR__ . '/../src/helpers/security.php';

if (($_SESSION['role_id'] ?? null) != 1) {
    header('Location: ' . (isset($_SESSION['user_id']) ? 'index.php' : 'auth-page.php'));
    exit;
}
require_once __DIR__ . '/../src/config/connection.php';
require_once __DIR__ . '/../src/helpers/vehicles.php';

$reasons = ['itv' => 'Revisión técnica', 'maintenance' => 'Mantenimiento', 'breakdown' => 'Avería mecánica', 'cleaning' => 'Limpieza y desinfección', 'accident' => 'Siniestro o accidente', 'other' => 'Otros motivos'];
$error = '';
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);

$vehicle_classes = get_vehicle_classes($conn);
$vehicle_colors = ['#ffffff' => 'Blanco', '#000000' => 'Negro', '#808080' => 'Gris', '#c0c0c0' => 'Plata', '#0000ff' => 'Azul', '#ff0000' => 'Rojo', '#008000' => 'Verde', '#ffff00' => 'Amarillo', '#a52a2a' => 'Marrón', '#f5f5dc' => 'Beige', '#ffa500' => 'Naranja'];
$vehicle_to_edit = isset($_GET['edit_id']) ? get_vehicle_by_id($conn, (int)$_GET['edit_id']) : null;
$edit_mode = $vehicle_to_edit !== null;
$closure_to_edit = isset($_GET['edit_closure_id']) ? get_closure_by_id($conn, (int)$_GET['edit_closure_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (isset($_POST['submit_logout_admin'])) {
            session_unset();
            session_destroy();
            header('Location: index.php');
            exit;
        }
        if (isset($_POST['submit-vehicle'])) {
            $vehicle = ['plate' => strtoupper(input('plate')), 'brand' => input('brand'), 'model' => input('model'), 'color' => input('color'), 'doors' => input('doors'), 'seats' => input('seats'), 'transmission' => input('transmission'), 'motorization' => input('motorization'), 'price' => input('price'), 'class_id' => input('class_id')];
            $vehicle_to_edit = $vehicle + ['id' => (int)input('vehicle_id')];
            $edit_mode = true;
            if (!preg_match('/^[0-9]{4}[BCDFGHJKLMNPRSTVWXYZ]{3}$/', $vehicle['plate']) || $vehicle['brand'] === '' || mb_strlen($vehicle['brand'], 'UTF-8') > 50 || $vehicle['model'] === '' || mb_strlen($vehicle['model'], 'UTF-8') > 50 || !preg_match('/^#[0-9a-fA-F]{6}$/', $vehicle['color']) || !filter_var($vehicle['doors'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 2, 'max_range' => 7]]) || !filter_var($vehicle['seats'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]) || !preg_match('/^[0-9]{1,8}(\.[0-9]{1,2})?$/', $vehicle['price']) || (float)$vehicle['price'] <= 0 || !in_array($vehicle['transmission'], ['manual', 'automatic'], true) || !in_array($vehicle['motorization'], ['gasoline', 'diesel', 'hybrid', 'electric'], true) || !in_array($vehicle['class_id'], array_column($vehicle_classes, 'id'))) {
                throw new RuntimeException('Revisa los datos. Usa matrícula española de cuatro números y tres consonantes, precio positivo con hasta dos decimales y una clase válida.');
            }
            $id = (int)input('vehicle_id');
            if ($id && !get_vehicle_by_id($conn, $id)) {
                throw new RuntimeException('El vehículo ya no existe.');
            }
            $saved = $id ? update_vehicle($conn, $id, $vehicle) : add_vehicle($conn, $vehicle);
            if (!$saved) {
                throw new RuntimeException('No se ha podido guardar el vehículo. Comprueba que la matrícula no esté repetida.');
            }
        } elseif (isset($_POST['delete_id'])) {
            if (!delete_vehicle($conn, (int)input('delete_id'))) {
                throw new RuntimeException('No se puede eliminar el vehículo: puede tener cierres o reservas asociados.');
            }
        } elseif (isset($_POST['submit-closure'])) {
            $closure = ['vehicle_id' => (int)input('vehicle_id'), 'start_date' => input('start_date'), 'end_date' => input('end_date'), 'reason' => input('reason')];
            $id = (int)input('closure_id');
            $closure_to_edit = $closure + ['id' => $id];
            if (!valid_date($closure['start_date']) || !valid_date($closure['end_date']) || $closure['end_date'] < $closure['start_date'] || (!$id && $closure['start_date'] < date('Y-m-d')) || !array_key_exists($closure['reason'], $reasons)) {
                throw new RuntimeException('Revisa las fechas y el motivo. Un cierre nuevo no puede comenzar en el pasado.');
            }
            if ($id && !get_closure_by_id($conn, $id)) {
                throw new RuntimeException('El cierre ya no existe.');
            }
            if (!($id ? update_closure($conn, $id, $closure) : add_closure($conn, $closure))) {
                throw new RuntimeException('No se ha guardado el cierre. Puede coincidir con otro cierre o una reserva confirmada. No se cancelan reservas automáticamente.');
            }
        } elseif (isset($_POST['delete_closure_id'])) {
            if (!delete_closure($conn, (int)input('delete_closure_id'))) {
                throw new RuntimeException('No se ha podido eliminar el cierre.');
            }
        } else {
            throw new RuntimeException('Operación no válida.');
        }
        $_SESSION['notice'] = 'Operación realizada correctamente.';
        header('Location: admin.php');
        exit;
    } catch (RuntimeException $e) {
        $error = $e instanceof PDOException ? 'No se ha podido completar la operación.' : $e->getMessage();
    }
}

$admin_vehicles = get_all_vehicles($conn);
$closures = get_all_closures($conn);

try {
    $admin_reservations = $conn->query('SELECT r.*, v.plate, v.brand, v.model, v.motorization, v.transmission, u.email, c.name, c.surname FROM reservations r JOIN vehicles v ON v.id = r.vehicle_id JOIN users u ON u.id = r.user_id JOIN customers c ON c.users_id = u.id ORDER BY r.start_date DESC, r.id DESC')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $admin_reservations = [];
    $error = 'No se han podido cargar las reservas.';
}

require_once __DIR__ . '/../src/views/layout.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración · VCar</title>
    <link rel="stylesheet" href="assets/app.css?v=vcar-footer-color-4">
<link rel="icon" type="image/png" href="assets/vcar-memoji.png"><meta name="theme-color" content="#f5ece3"></head>
<body>
    <?php site_header('admin'); ?>
    <main id="main" class="page admin-page">
        <div class="page-intro"><h1>Administración</h1><p>Gestiona tus vehículos, organiza los cierres y consulta las reservas de tus clientes.</p></div>
        <?php if ($error): ?><p role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($notice): ?><p role="status"><?= htmlspecialchars($notice) ?></p><?php endif; ?>
        <h2><?= !empty($vehicle_to_edit['id']) ? 'Modificar Vehículo' : 'Agregar Nuevo Vehículo' ?></h2>
        <form method="post" class="editor-form">
            <?= csrf_field() ?>
            <?php if ($edit_mode && $vehicle_to_edit): ?>
                <input type="hidden" name="vehicle_id" value="<?= $vehicle_to_edit['id'] ?>">
            <?php endif; ?>
            <div class="field"><label for="admin-plate">Matrícula</label><input id="admin-plate" type="text" name="plate" required value="<?= $edit_mode ? htmlspecialchars($vehicle_to_edit['plate']) : '' ?>" /></div>
            <div class="field"><label for="admin-brand">Marca</label><input id="admin-brand" type="text" name="brand" required value="<?= $edit_mode ? htmlspecialchars($vehicle_to_edit['brand']) : '' ?>" /></div>
            <div class="field"><label for="admin-model">Modelo</label><input id="admin-model" type="text" name="model" required value="<?= $edit_mode ? htmlspecialchars($vehicle_to_edit['model']) : '' ?>" /></div>
            <div class="field"><label for="admin-color">Color</label>
                <?php $selected_color = $edit_mode ? strtolower($vehicle_to_edit['color']) : ''; ?>
                <select id="admin-color" name="color" required>
                    <option value="">Selecciona un color...</option>
                    <?php if (preg_match('/^#[0-9a-f]{6}$/', $selected_color) && !isset($vehicle_colors[$selected_color])): ?>
                    <option value="<?= htmlspecialchars($selected_color) ?>" selected>Color actual (<?= htmlspecialchars($selected_color) ?>)</option>
                    <?php endif; ?>
                    <?php foreach ($vehicle_colors as $hex => $name): ?>
                    <option value="<?= $hex ?>" <?= $selected_color === $hex ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field"><label for="admin-doors">Puertas</label><input id="admin-doors" type="number" name="doors" required min="2" max="7" value="<?= $edit_mode ? htmlspecialchars((string)$vehicle_to_edit['doors']) : '' ?>" /></div>
            <div class="field"><label for="admin-seats">Asientos</label><input id="admin-seats" type="number" name="seats" required min="1" max="9" value="<?= $edit_mode ? htmlspecialchars((string)$vehicle_to_edit['seats']) : '' ?>" /></div>
            <div class="field"><label for="admin-transmission">Transmisión</label><select id="admin-transmission" name="transmission" required>
                <option value="">Selecciona transmisión...</option>
                <option value="manual" <?= ($edit_mode && $vehicle_to_edit['transmission'] == 'manual') ? 'selected' : '' ?>>Manual</option>
                <option value="automatic" <?= ($edit_mode && $vehicle_to_edit['transmission'] == 'automatic') ? 'selected' : '' ?>>Automático</option>
            </select></div>
            <div class="field"><label for="admin-motorization">Motorización</label><select id="admin-motorization" name="motorization" required>
                <option value="">Selecciona motorización...</option>
                <option value="gasoline" <?= ($edit_mode && $vehicle_to_edit['motorization'] == 'gasoline') ? 'selected' : '' ?>>Gasolina</option>
                <option value="diesel" <?= ($edit_mode && $vehicle_to_edit['motorization'] == 'diesel') ? 'selected' : '' ?>>Diésel</option>
                <option value="hybrid" <?= ($edit_mode && $vehicle_to_edit['motorization'] == 'hybrid') ? 'selected' : '' ?>>Híbrido</option>
                <option value="electric" <?= ($edit_mode && $vehicle_to_edit['motorization'] == 'electric') ? 'selected' : '' ?>>Eléctrico</option>
            </select></div>
            <div class="field"><label for="admin-price">Precio por Día (€)</label><input id="admin-price" type="number" step="0.01" min="0.01" name="price" required value="<?= $edit_mode ? htmlspecialchars((string)$vehicle_to_edit['price']) : '' ?>" /></div>
            <div class="field"><label for="admin-class_id">Tipo de Clase</label><select id="admin-class_id" name="class_id" required>
                <option value="">Selecciona una clase...</option>
                <?php foreach ($vehicle_classes as $class): ?>
                    <option value="<?= $class['id'] ?>" <?= ($edit_mode && $vehicle_to_edit['class_id'] == $class['id']) ? 'selected' : '' ?>>
                        Clase <?= htmlspecialchars($class['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select></div>
            <button type="submit" name="submit-vehicle"><?= icon('save') ?><?= !empty($vehicle_to_edit['id']) ? 'Actualizar vehículo' : 'Agregar vehículo' ?></button>
            <?php if ($edit_mode): ?>
                <a class="text-link" href="admin.php">Cancelar edición</a>
            <?php endif; ?>
        </form>

        <h2><?= icon('car-front') ?>Vehículos registrados</h2>
        <?php if (empty($admin_vehicles)): ?>
            <p>No hay vehículos registrados.</p>
        <?php else: ?>
            <div class="table-wrap" role="region" aria-label="Tabla de gestión" tabindex="0"><table>
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
                            <td><?= htmlspecialchars(vehicle_label($vehicle['transmission'])) ?></td>
                            <td><?= htmlspecialchars(vehicle_label($vehicle['motorization'])) ?></td>
                            <td><div class="row-actions">
                                <a class="edit-button" href="admin.php?edit_id=<?= $vehicle['id'] ?>"><?= icon('pencil') ?>Editar</a>
                                <form method="post"><?= csrf_field() ?><button name="delete_id" value="<?= (int)$vehicle['id'] ?>"><?= icon('trash') ?>Eliminar</button></form>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>

        <h2><?= icon('calendar-days') ?>Cierres y mantenimiento</h2>
        <?php if (!empty($closure_to_edit['id'])): ?><p>Editando cierre <?= (int)$closure_to_edit['id'] ?>. <a class="text-link" href="admin.php">Cancelar edición</a></p><?php endif; ?>
        <form method="post" class="editor-form">
            <?= csrf_field() ?>
            <?php if ($closure_to_edit): ?><input type="hidden" name="closure_id" value="<?= (int)$closure_to_edit['id'] ?>"><?php endif; ?>
            <div class="field"><label for="admin-vehicle_id">Vehículo</label><select id="admin-vehicle_id" name="vehicle_id" required>
                <option value="">Selecciona un vehículo...</option>
                <?php foreach ($admin_vehicles as $vehicle): ?>
                    <option value="<?= $vehicle['id'] ?>" <?= $closure_to_edit && $closure_to_edit['vehicle_id'] == $vehicle['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($vehicle['plate']) ?> - <?= htmlspecialchars($vehicle['brand']) ?> <?= htmlspecialchars($vehicle['model']) ?>
                    </option>
                <?php endforeach; ?>
            </select></div>
            <div class="field"><label for="admin-start_date">Fecha de Inicio</label><input id="admin-start_date" type="date" name="start_date" value="<?= htmlspecialchars($closure_to_edit['start_date'] ?? '') ?>" required /></div>
            <div class="field"><label for="admin-end_date">Fecha de Fin</label><input id="admin-end_date" type="date" name="end_date" value="<?= htmlspecialchars($closure_to_edit['end_date'] ?? '') ?>" required /></div>
            <div class="field"><label for="admin-reason">Motivo</label><select id="admin-reason" required name="reason">
                <option value="">Selecciona un motivo...</option>
                <?php foreach($reasons as $key => $value): ?>
                <option value="<?= $key ?>" <?= ($closure_to_edit['reason'] ?? '') === $key ? 'selected' : '' ?>><?= $value ?></option>
                <?php endforeach; ?>
            </select></div>
            <button type="submit" name="submit-closure"><?= icon('save') ?>Guardar cierre</button>
        </form>

        <h3>Cierres registrados</h3>
        <?php if (empty($closures)): ?>
            <p>No hay cierres o bloqueos registrados.</p>
        <?php else: ?>
            <div class="table-wrap" role="region" aria-label="Tabla de gestión" tabindex="0"><table>
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
                            <td><div class="row-actions">
                                <a class="edit-button" href="admin.php?edit_closure_id=<?= (int)$closure['id'] ?>"><?= icon('pencil') ?>Editar</a>
                                <form method="post"><?= csrf_field() ?><button name="delete_closure_id" value="<?= (int)$closure['id'] ?>"><?= icon('trash') ?>Eliminar</button></form>
                            </div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
        <h2><?= icon('clipboard-list') ?>Reservas de clientes</h2>
        <?php if (!$admin_reservations): ?><p>No hay reservas registradas.</p><?php else: ?>
        <div class="table-wrap" role="region" aria-label="Tabla de gestión" tabindex="0"><table>
            <thead><tr><th>Reserva</th><th>Cliente</th><th>Modelo contratado</th><th>Matrícula asignada</th><th>Periodo</th><th>Estado</th><th>Importes</th><th>Carnet</th></tr></thead>
            <tbody>
            <?php foreach ($admin_reservations as $r): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['name'] . ' ' . $r['surname'] . ' / ' . $r['email']) ?></td>
                <td><?= htmlspecialchars(reservation_description($r)) ?></td>
                <td><?= htmlspecialchars($r['plate']) ?></td>
                <td><?= display_date($r['start_date']) ?> — <?= display_date($r['end_date']) ?></td>
                <td><?= $r['status'] === 'cancelled' ? 'Cancelada' : ($r['end_date'] < date('Y-m-d') ? 'Finalizada' : 'Confirmada') ?></td>
                <td>Sin descuento: <?= number_format((float)$r['total_price'], 2, ',', '.') ?> €. Descuento: <?= (int)$r['discount_percent'] ?> %. Final: <?= number_format((float)$r['final_price'], 2, ',', '.') ?> €.</td>
                <td><a href="license.php?user_id=<?= (int)$r['user_id'] ?>"><?= icon('file-badge') ?>Ver carnet</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </main>
<?php site_footer(); ?>
</body>
</html>
