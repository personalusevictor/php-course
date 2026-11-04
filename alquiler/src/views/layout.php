<?php
require_once __DIR__ . '/../helpers/security.php';

function icon(string $name): string {
    static $icons = [];
    if (!preg_match('/^[a-z][a-z0-9-]*$/', $name)) {
        return '';
    }
    if (!isset($icons[$name])) {
        $path = __DIR__ . '/../../public/assets/icons/' . $name . '.svg';
        $icons[$name] = is_file($path) ? str_replace('<svg', '<svg class="icon" aria-hidden="true" focusable="false"', file_get_contents($path)) : '';
    }
    return $icons[$name];
}

function site_header(string $active): void {
    $logged = isset($_SESSION['user_id']);
    $admin = ($_SESSION['role_id'] ?? null) == 1;
    ?>
    <a class="skip-link" href="#main">Saltar al contenido</a>
    <header class="site-header <?= $active === 'auth' ? 'auth-header' : '' ?>">
        <a class="brand" href="index.php" aria-label="VCar, inicio"><img class="brand-logo" src="assets/vcar-memoji.png" width="56" height="56" alt=""><span>VCar</span></a>
        <?php if ($active === 'auth'): ?>
        <a class="auth-back" href="index.php">Volver al catálogo</a>
        <?php else: ?>
        <nav aria-label="Navegación principal">
            <a href="index.php" <?= $active === 'catalog' ? 'aria-current="page"' : '' ?>><?= icon('layout-grid') ?>Catálogo</a>
            <?php if ($admin): ?>
            <a href="admin.php" <?= $active === 'admin' ? 'aria-current="page"' : '' ?>><?= icon('sliders-horizontal') ?>Administración</a>
            <?php else: ?>
            <a href="availability.php" <?= $active === 'availability' ? 'aria-current="page"' : '' ?>><?= icon('calendar-days') ?>Disponibilidad</a>
            <?php if ($logged): ?><a href="reservations.php" <?= $active === 'reservations' ? 'aria-current="page"' : '' ?>><?= icon('clipboard-list') ?>Mis reservas</a><?php endif; ?>
            <?php endif; ?>
        </nav>
        <div class="header-actions">
        <?php if ($logged): ?>
        <form method="post" action="index.php"><?= csrf_field() ?><button class="button-quiet logout" aria-label="Cerrar sesión" name="submit_logout_costumers"><?= icon('log-out') ?><span>Cerrar sesión</span></button></form>
        <?php else: ?><a class="button button-dark button-small" href="auth-page.php"><?= icon('log-in') ?>Acceder</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </header>
    <?php
}

function site_footer(): void {
    ?>
    <footer class="site-footer"><a class="footer-identity" href="index.php" aria-label="VCar, inicio"><img src="assets/vcar-memoji.png" width="44" height="44" alt=""><span>VCar<small>Alquiler de vehículos en La Palma</small></span></a><div class="footer-credit">Víctor Martín Pérez · 2ªDAW</div></footer>
    <?php
}

function class_label(string $class): string {
    return ['A' => 'Urbanos', 'B' => 'Compactos', 'C' => 'SUV', 'D' => 'Familiares'][$class] ?? 'Vehículos';
}

function reservation_description(array $reservation): string {
    if (trim($reservation['vehicle_description'] ?? '') !== '') {
        return $reservation['vehicle_description'];
    }
    return $reservation['brand'] . ' ' . $reservation['model'] . ' · ' . vehicle_label($reservation['motorization']) . ' · ' . vehicle_label($reservation['transmission']);
}
