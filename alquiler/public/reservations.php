<?php
session_start();
require_once __DIR__ . '/../src/helpers/security.php';
if (($_SESSION['role_id'] ?? null) != 2) {
    header('Location: ' . (isset($_SESSION['user_id']) ? 'index.php' : 'auth-page.php'));
    exit;
}
require_once __DIR__ . '/../src/config/connection.php';
$error = '';
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);
$reservations = [];
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();
        $stmt = $conn->prepare("UPDATE reservations SET status = 'cancelled', cancelled_at = NOW() WHERE id = ? AND user_id = ? AND status = 'confirmed'");
        $stmt->execute([(int)input('reservation_id'), $_SESSION['user_id']]);
        $_SESSION['notice'] = $stmt->rowCount() ? 'Reserva cancelada. El registro se conserva en el historial.' : 'La reserva no existe, no es tuya o ya estaba cancelada.';
        header('Location: reservations.php');
        exit;
    }
    $stmt = $conn->prepare('SELECT r.*, v.brand, v.model, v.motorization, v.transmission FROM reservations r JOIN vehicles v ON v.id = r.vehicle_id WHERE r.user_id = ? ORDER BY r.start_date DESC, r.id DESC');
    $stmt->execute([$_SESSION['user_id']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'No se han podido gestionar las reservas.';
}
$groups = ['Próximas y en curso' => [], 'Historial' => []];
foreach ($reservations as $r) {
    $groups[$r['status'] === 'confirmed' && $r['end_date'] >= date('Y-m-d') ? 'Próximas y en curso' : 'Historial'][] = $r;
}
require_once __DIR__ . '/../src/views/layout.php';
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mis reservas · VCar</title><link rel="stylesheet" href="assets/app.css?v=vcar-footer-color-4"><link rel="icon" type="image/png" href="assets/vcar-memoji.png"><meta name="theme-color" content="#f5ece3"></head><body>
<?php site_header('reservations'); ?>
<main id="main" class="page">
<div class="page-intro"><h1>Mis reservas</h1><p>Consulta tus reservas activas, sus precios y tu historial.</p><div class="account-tools"><a class="text-link" href="availability.php"><?= icon('plus') ?>Nueva reserva</a><a class="text-link" href="license.php"><?= icon('file-badge') ?>Ver mi carnet</a></div></div>
<?php if ($error): ?><p role="alert"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($notice): ?><p role="status"><?= htmlspecialchars($notice) ?></p><?php endif; ?>
<?php foreach ($groups as $heading => $items): ?>
<section class="reservation-section"><h2><?= htmlspecialchars($heading) ?><span class="count-badge"><?= count($items) ?></span></h2>
<?php if (!$items): ?><p class="empty-state"><?= $heading === 'Historial' ? 'Aquí aparecerán tus reservas finalizadas y canceladas.' : 'No tienes reservas próximas o en curso.' ?></p><?php endif; ?>
<?php foreach ($items as $r): $cancelled = $r['status'] === 'cancelled'; ?>
<article class="reservation-card"><div>
<div class="reservation-top"><span class="reservation-id">Reserva #<?= (int)$r['id'] ?></span><span class="status <?= $cancelled ? 'status-cancelled' : '' ?>"><?= $cancelled ? 'Cancelada' : ($r['end_date'] < date('Y-m-d') ? 'Finalizada' : ($r['start_date'] <= date('Y-m-d') ? 'En curso' : 'Confirmada')) ?></span></div>
<h3><?= htmlspecialchars(reservation_description($r)) ?></h3><p class="reservation-dates"><?= icon('calendar-days') ?><?= display_date($r['start_date']) ?> — <?= display_date($r['end_date']) ?></p>
<?php if ($r['cancelled_at']): ?><p class="help">Cancelada el <?= htmlspecialchars((new DateTimeImmutable($r['cancelled_at']))->format('d/m/Y H:i')) ?>. Conservamos el registro en tu historial.</p><?php endif; ?>
<?php if (!$cancelled): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="reservation_id" value="<?= (int)$r['id'] ?>"><button class="cancel-button"><?= icon('circle-x') ?>Cancelar reserva #<?= (int)$r['id'] ?></button></form><?php endif; ?>
</div><div class="quote"><div class="quote-line"><span>Precio diario</span><span><?= number_format((float)$r['daily_price'], 2, ',', '.') ?> €</span></div><div class="quote-line"><span>Total sin descuento</span><span><?= number_format((float)$r['total_price'], 2, ',', '.') ?> €</span></div><div class="quote-line"><span>Descuento aplicado</span><span><?= (int)$r['discount_percent'] ?> %</span></div><div class="quote-line quote-total"><span>Precio final</span><span><?= number_format((float)$r['final_price'], 2, ',', '.') ?> €</span></div></div>
</article>
<?php endforeach; ?></section>
<?php endforeach; ?>
<p class="footer-note">Las fechas inicial y final se cobran y se bloquean completas. Cancelar una reserva conserva su registro.</p>
</main><?php site_footer(); ?></body></html>
