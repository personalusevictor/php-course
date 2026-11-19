<?php
require_once(dirname(__DIR__) . '/helpers/auth.php');
require_once(dirname(__DIR__) . '/helpers/cart-cookie.php');
require_once(dirname(__DIR__) . '/helpers/currency.php');

function get_icon(string $name): string {
    $paths = [
        'search' => '<circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/>',
        'bag' => '<path d="M5 7h14l1 14H4L5 7Z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
        'user' => '<circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'minus' => '<path d="M5 12h14"/>',
        'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'trash' => '<path d="M3 6h18M9 6V3h6v3M6 6l1 15h10l1-15M10 10v7M14 10v7"/>',
        'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 7h6M9 11h6M9 15h4"/>',
    ];
    if (isset($paths[$name])) {
        return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths[$name] . '</svg>';
    }
    if (!preg_match('/^[a-z][a-z0-9-]*$/', $name)) return '';
    $path = dirname(__DIR__) . '/assets/icons/' . $name . '.svg';
    return is_file($path) ? str_replace('<svg', '<svg class="icon" aria-hidden="true" focusable="false"', file_get_contents($path)) : '';
}

function category_art(string $category): string {
    $map = [
        'Procesadores' => 'cpu', 'Tarjetas gráficas' => 'gpu', 'Placas base' => 'motherboard',
        'Memoria RAM' => 'ram', 'Almacenamiento SSD' => 'ssd', 'Fuentes de alimentación' => 'psu',
        'Refrigeración' => 'cooling', 'Cajas de PC' => 'case', 'Teclados' => 'keyboard',
        'Ratones' => 'mouse', 'Monitores' => 'monitor', 'Auriculares' => 'headset',
    ];
    return $map[$category] ?? 'cpu';
}

function render_product_media(array $product, string $base_path = '', bool $large = false): void {
    $category = $product['category_name'] ?? '';
    $image = $product['products_image'] ?? '';
    ?>
    <div class="product-media <?= $large ? 'product-media-large' : '' ?> <?= $image ? 'has-product-photo' : 'art-' . category_art($category) ?>">
        <?php if ($image): ?>
            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product['products_name'] ?? '') ?>" loading="<?= $large ? 'eager' : 'lazy' ?>" width="400" height="300">
        <?php else: ?>
            <img src="<?= $base_path ?>assets/illustrations/<?= category_art($category) ?>.svg" alt="" loading="<?= $large ? 'eager' : 'lazy' ?>" width="400" height="300">
            <span class="illustration-label">Ilustración de categoría</span>
        <?php endif; ?>
    </div>
    <?php
}

function render_head(string $title = 'VTechShop', string $base_path = '', string $page_class = ''): void {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="theme-color" content="#17191b">
        <meta name="description" content="Componentes y periféricos para tu próximo equipo. Explora el catálogo de VTechShop, encuentra tu pieza y prepara tu pedido.">
        <link rel="icon" type="image/svg+xml" href="<?= $base_path ?>assets/favicon.svg">
        <link rel="preload" href="<?= $base_path ?>assets/fonts/manrope-variable.ttf" as="font" type="font/ttf" crossorigin>
        <link rel="stylesheet" href="<?= $base_path ?>assets/css/style.css">
        <script src="<?= $base_path ?>assets/js/app.js" defer></script>
        <title><?= htmlspecialchars($title) ?></title>
    </head>
    <body class="<?= htmlspecialchars($page_class) ?>">
    <a href="#main-content" class="skip-link">Saltar al contenido</a>
    <?php
}

function render_header(string $base_path = ''): void {
    start_session_if_needed();
    $logged = is_logged_in();
    $username = $_SESSION['username'] ?? 'Usuario';
    $cart_count = get_cart_count();
    $page = basename($_SERVER['SCRIPT_NAME'] ?? '');
    ?>
    <header class="site-header">
        <div class="header-inner">
            <a href="<?= $base_path ?>index.php" class="brand" aria-label="VTechShop, inicio">
                <img src="<?= $base_path ?>assets/brand/logo.svg" width="224" height="56" alt="VTechShop">
            </a>
            <div class="search-container" role="search">
                <?= get_icon('search') ?>
                <label for="ajax-search-input" class="sr-only">Buscar productos, marcas o modelos</label>
                <input type="search" id="ajax-search-input" class="search-input" placeholder="¿Qué pieza estás buscando?" autocomplete="off" maxlength="100" data-base="<?= $base_path ?>" aria-controls="ajax-search-results" aria-expanded="false">
                <span class="search-hint" aria-hidden="true">/</span>
                <div id="ajax-search-results" class="search-dropdown-results" hidden></div>
                <span id="search-status" class="sr-only" role="status"></span>
            </div>
            <nav class="header-nav" aria-label="Navegación principal">
                <a href="<?= $base_path ?>index.php" class="catalog-link <?= $page === 'index.php' ? 'is-current' : '' ?>">Catálogo</a>
                <?php if ($logged): ?>
                    <details class="account-menu">
                        <summary aria-label="Menú de mi cuenta"><?= get_icon('user') ?><span><?= htmlspecialchars($username) ?></span></summary>
                        <div class="account-popover">
                            <a href="<?= $base_path ?>orders.php"><?= get_icon('receipt') ?> Mis pedidos</a>
                            <form action="<?= $base_path ?>auth/auth-process.php" method="POST">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit"><?= get_icon('log-out') ?> Cerrar sesión</button>
                            </form>
                        </div>
                    </details>
                <?php else: ?>
                    <a href="<?= $base_path ?>auth/auth-page.php" aria-label="Acceder a mi cuenta" class="account-link <?= $page === 'auth-page.php' ? 'is-current' : '' ?>"><?= get_icon('user') ?><span>Acceder</span></a>
                <?php endif; ?>
                <a href="<?= $base_path ?>cart.php" class="cart-link <?= $page === 'cart.php' ? 'is-current' : '' ?>"><?= get_icon('bag') ?><span>Carrito</span><span id="header-cart-badge" class="cart-badge"><?= $cart_count ?></span></a>
            </nav>
        </div>
    </header>
    <div id="toast-container" class="toast-container" role="status" aria-live="polite" aria-atomic="true"></div>
    <?php
}

function render_product_card(array $product, string $base_path = '', string $currency = 'EUR'): void {
    $id = (int)$product['products_id'];
    $stock = (int)$product['stock'];
    $name = htmlspecialchars($product['products_name']);
    ?>
    <article class="product-card <?= $stock <= 0 ? 'is-sold-out' : '' ?>">
        <a href="<?= $base_path ?>products.php?id=<?= $id ?>" class="product-image-link" tabindex="-1" aria-hidden="true">
            <?php render_product_media($product, $base_path); ?>
        </a>
        <div class="product-card-body">
            <div class="product-meta"><span><?= htmlspecialchars($product['brand'] ?? $product['category_name'] ?? '') ?></span><span class="stock-label <?= $stock <= 0 ? 'stock-out' : ($stock <= 5 ? 'stock-low' : '') ?>"><?= $stock <= 0 ? 'Agotado' : ($stock <= 5 ? 'Quedan ' . $stock : 'En stock') ?></span></div>
            <h3><a href="<?= $base_path ?>products.php?id=<?= $id ?>"><?= $name ?></a></h3>
            <?php if (!empty($product['reason'])): ?><p class="recommendation-reason"><?= htmlspecialchars($product['reason']) ?></p><?php endif; ?>
            <div class="product-card-bottom">
                <strong class="product-price"><?= format_price((float)$product['price'], $currency) ?></strong>
                <?php if ($stock > 0): ?>
                    <button type="button" class="js-quick-add quick-add" data-id="<?= $id ?>" data-base="<?= $base_path ?>" aria-label="Añadir <?= $name ?> al carrito"><?= get_icon('plus') ?><span>Añadir</span></button>
                <?php else: ?><button type="button" class="quick-add" disabled>Sin stock</button><?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}

function render_product_section(string $title, ?string $subtitle, array $products, string $currency = 'EUR', string $base_path = ''): void {
    if (empty($products)) return;
    ?>
    <section class="product-section recommendation-section">
        <div class="section-heading"><div><h2><?= htmlspecialchars($title) ?></h2><?php if ($subtitle): ?><p><?= htmlspecialchars($subtitle) ?></p><?php endif; ?></div></div>
        <div class="product-grid"><?php foreach ($products as $product) render_product_card($product, $base_path, $currency); ?></div>
    </section>
    <?php
}

function render_footer(string $base_path = ''): void {
    ?>
    <footer class="site-footer">
        <div class="footer-inner">
            <a class="brand footer-brand" href="<?= $base_path ?>index.php"><img src="<?= $base_path ?>assets/brand/logo.svg" width="224" height="56" alt="VTechShop, inicio"></a>
            <nav aria-label="Navegación del pie"><a href="<?= $base_path ?>index.php">Catálogo</a><a href="<?= $base_path ?>cart.php">Carrito</a><a href="<?= $base_path ?><?= is_logged_in() ? 'orders.php' : 'auth/auth-page.php' ?>"><?= is_logged_in() ? 'Mis pedidos' : 'Mi cuenta' ?></a></nav>
            <small>© <?= date('Y') ?> VTechShop</small>
        </div>
    </footer>
    </body>
    </html>
    <?php
}
