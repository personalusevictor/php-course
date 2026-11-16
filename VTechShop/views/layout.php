<?php
require_once(dirname(__DIR__) . '/helpers/auth.php');
require_once(dirname(__DIR__) . '/helpers/cart-cookie.php');
require_once(dirname(__DIR__) . '/helpers/currency.php');

function get_icon(String $name) {
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

function render_head(string $title = 'VTechShop', string $base_path = '') {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="<?= $base_path ?>assets/js/app.js" defer></script>
        <title><?= htmlspecialchars($title) ?></title>
    </head>
    <body>
    <?php
}

function render_header(string $base_path = '') {
    start_session_if_needed();
    $logged = is_logged_in();
    $username = $_SESSION['username'] ?? 'Usuario';
    $cart_count = get_cart_count();
    ?>
    <header>
        <a href="<?= $base_path ?>index.php">
            <img src="<?= $base_path ?>assets/memoji.png" width="42" height="42" alt="VTechShop">
            <span>VTechShop</span>
        </a>

        <div class="search-container">
            <input type="text" id="ajax-search-input" class="search-input" placeholder="Buscar componentes, marcas, modelos..." autocomplete="off" data-base="<?= $base_path ?>">
            <div id="ajax-search-results" class="search-dropdown-results" hidden></div>
        </div>

        <nav>
            <a href="<?= $base_path ?>index.php">Catálogo</a>
            <?php if ($logged): ?>
                <a href="<?= $base_path ?>orders.php">Mis Pedidos</a>
            <?php endif; ?>
            <a href="<?= $base_path ?>cart.php">
                Carrito (<span id="header-cart-badge"><?= $cart_count ?></span>)
            </a>
            <?php if ($logged): ?>
                <span>Hola, <strong><?= htmlspecialchars($username) ?></strong></span>
                <form action="<?= $base_path ?>auth/auth-process.php" method="POST">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit">
                        <?= get_icon('log-out') ?> Salir
                    </button>
                </form>
            <?php else: ?>
                <a href="<?= $base_path ?>auth/auth-page.php">
                    <?= get_icon('log-in') ?> Acceder
                </a>
            <?php endif; ?>
        </nav>
    </header>
    <div id="toast-container"></div>
    <hr>
    <?php
}

function render_product_card(array $product, string $base_path = '', string $currency = 'EUR'): void {
    $id = (int)$product['products_id'];
    $stock = (int)$product['stock'];
    $name = htmlspecialchars($product['products_name']);
    $price = format_price((float)$product['price'], $currency);
    $img = !empty($product['products_image']) ? htmlspecialchars($product['products_image']) : '';
    ?>
    <article>
        <a href="<?= $base_path ?>products.php?id=<?= $id ?>">
            <?php if ($img): ?>
                <img src="<?= $img ?>" alt="<?= $name ?>" width="180">
            <?php endif; ?>
        </a>
        <h3>
            <a href="<?= $base_path ?>products.php?id=<?= $id ?>"><?= $name ?></a>
        </h3>
        <p><strong><?= $price ?></strong></p>
        <?php if (!empty($product['reason'])): ?>
            <p><small><?= htmlspecialchars($product['reason']) ?></small></p>
        <?php endif; ?>
        <?php if ($stock <= 0): ?>
            <p><strong>Agotado (Sin stock)</strong></p>
        <?php elseif ($stock <= 5): ?>
            <p>¡Últimas <?= $stock ?> unidades!</p>
        <?php endif; ?>

        <?php if ($stock > 0): ?>
            <button type="button" class="js-quick-add" data-id="<?= $id ?>" data-base="<?= $base_path ?>">
                + Añadir al carrito
            </button>
        <?php else: ?>
            <button type="button" disabled>Sin stock</button>
        <?php endif; ?>
    </article>
    <?php
}

function render_product_section(string $title, ?string $subtitle, array $products, string $currency = 'EUR', string $base_path = ''): void {
    if (empty($products)) {
        return;
    }
    ?>
    <section>
        <h2><?= htmlspecialchars($title) ?></h2>
        <?php if ($subtitle): ?>
            <p><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
        <div>
            <?php foreach ($products as $product): ?>
                <?php render_product_card($product, $base_path, $currency); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

function render_footer(string $base_path = ''): void {
    ?>
    <footer>
        <div><p>&copy; <?= date('Y') ?> VTechShop - Tienda de Informática Online</p></div>
    </footer>
    </body>
    </html>
    <?php
}

?>