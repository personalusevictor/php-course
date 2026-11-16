<?php
function icon(String $name) {
    static $icons = [];
    if (!preg_match('/^[a-z][a-z0-9-]*$/', $name)) {
        return '';
    }

    if (!isset($icons[$name])) {
        $path = dirname(__DIR__, 1) . '/assets/icons/' . $name . '.svg';
        $icons[$name] = is_file($path) ? str_replace('<svg', '<svg class="icon" aria-hidden="true" focusable="false"', file_get_contents($path)) : '';
    }

    return $icons[$name];
}

function site_header() {
    $logged = isset($_SESSION['user_id']);
    $username = $_SESSION['username'] ?? 'Usuario';

    include_once(dirname(__DIR__, 1) . '/helpers/cart-cookie.php');
    $cart_count = array_sum(read_cart());
    ?>
    <header>
        <a href="index.php">
            <img src="assets/memoji.png" width="56" height="56" alt="">
            <span>VTechShop</span>
        </a>
        <nav>
            <a href="index.php">Página Principal</a>
            <a href="cart.php">Carrito 🛒 <?= $cart_count > 0 ? "($cart_count)" : '' ?></a>
        </nav>
        <?php if ($logged): ?>
            <div>
                <span>Hola, <strong><?= htmlspecialchars($username) ?></strong></span>
                <form action="index.php" method="POST" style="display:inline;">
                    <button aria-label="Cerrar Sesión" name="submit_logout">
                        <?= icon('log-out') ?><span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        <?php else: ?>
            <a href="auth/auth-page.php"><?= icon('log-in') ?><span>Acceder</span></a>
        <?php endif; ?>
    </header>
<?php
}

?>