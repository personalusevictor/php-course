<?php
require_once(__DIR__ . '/helpers/product.php');
require_once(__DIR__ . '/helpers/cart-cookie.php');
require_once(__DIR__ . '/helpers/recommendations.php');
require_once(__DIR__ . '/config/connection.php');
require_once(__DIR__ . '/views/layout.php');

start_session_if_needed();
$currency = $_SESSION['currency_code'] ?? 'EUR';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        if ($action === 'remove') {
            remove_from_cart($id);
        } elseif ($action === 'update') {
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            $product = product_by_id($conn, $id);
            if ($product && $quantity !== false && $quantity !== null) {
                update_cart_item($id, $quantity, (int)$product['stock']);
            }
        } elseif ($action === 'clear') {
            clear_cart();
        }
    }

    header('Location: cart.php');
    exit;
}

$cart = read_cart();
$items = [];
$total = 0;
$adjusted = false;

foreach ($cart as $id => $quantity) {
    $product = product_by_id($conn, (int)$id);

    if (!$product || (int)$product['stock'] <= 0) {
        unset($cart[$id]);
        $adjusted = true;
        continue;
    }

    $stock = (int)$product['stock'];
    if ($quantity > $stock) {
        $quantity = $stock;
        $cart[$id] = $stock;
        $adjusted = true;
    }

    $subtotal = (float)$product['price'] * $quantity;
    $total += $subtotal;

    $items[] = [
        'id' => (int)$id,
        'name' => $product['products_name'],
        'product' => $product,
        'price' => (float)$product['price'],
        'qty' => $quantity,
        'stock' => $stock,
        'subtotal' => $subtotal,
    ];
}

if ($adjusted) {
    save_cart($cart);
}
$cart_recommendations = !empty($items) ? get_cart_recommendations($conn, 4) : [];

render_head('Tu carrito · VTechShop', '', 'page-cart');
render_header();
?>
<main id="main-content" class="site-main">
    <div class="commerce-heading"><div><p class="eyebrow">TU PRÓXIMO EQUIPO</p><h1>Tu carrito</h1></div><?php if ($items): ?><p><?= array_sum(array_column($items, 'qty')) ?> <?= array_sum(array_column($items, 'qty')) === 1 ? 'unidad' : 'unidades' ?> · <?= count($items) ?> <?= count($items) === 1 ? 'producto' : 'productos' ?></p><?php endif; ?></div>
    <?php if ($adjusted): ?><div class="alert" role="status">Hemos ajustado algunos productos del carrito al stock disponible.</div><?php endif; ?>
    <?php if (empty($items)): ?>
        <div class="empty-state"><?= get_icon('bag') ?><h2>Aún no hay piezas en tu carrito</h2><p>Explora el catálogo y añade los componentes de tu próximo equipo.</p><a class="button button-accent" href="index.php">Explorar componentes</a></div>
    <?php else: ?>
        <div class="commerce-layout">
            <div>
                <ul class="cart-items">
                <?php foreach ($items as $item): ?>
                    <li class="cart-item">
                        <a href="products.php?id=<?= $item['id'] ?>" tabindex="-1" aria-hidden="true"><?php render_product_media($item['product']); ?></a>
                        <div class="cart-item-info"><h2><a href="products.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></h2><p><?= format_price($item['price'], $currency) ?> / unidad · Stock: <?= $item['stock'] ?></p>
                            <div class="quantity-controls" role="group" aria-label="Cantidad de <?= htmlspecialchars($item['name']) ?>">
                                <form action="cart.php" method="post"><input type="hidden" name="action" value="update"><input type="hidden" name="product_id" value="<?= $item['id'] ?>"><input type="hidden" name="quantity" value="<?= $item['qty'] - 1 ?>"><button type="submit" aria-label="Restar una unidad"><?= get_icon('minus') ?></button></form>
                                <strong><?= $item['qty'] ?></strong>
                                <form action="cart.php" method="post"><input type="hidden" name="action" value="update"><input type="hidden" name="product_id" value="<?= $item['id'] ?>"><input type="hidden" name="quantity" value="<?= $item['qty'] + 1 ?>"><button type="submit" aria-label="Sumar una unidad" <?= $item['qty'] >= $item['stock'] ? 'disabled' : '' ?>><?= get_icon('plus') ?></button></form>
                            </div>
                        </div>
                        <div class="cart-item-end"><strong class="cart-line-total"><?= format_price($item['subtotal'], $currency) ?></strong><form action="cart.php" method="post"><input type="hidden" name="action" value="remove"><input type="hidden" name="product_id" value="<?= $item['id'] ?>"><button class="remove-button" type="submit" aria-label="Eliminar <?= htmlspecialchars($item['name']) ?>"><?= get_icon('trash') ?> Eliminar</button></form></div>
                    </li>
                <?php endforeach; ?>
                </ul>
                <a class="text-link cart-continue" href="index.php">Seguir explorando el catálogo</a>
            </div>
            <aside class="order-summary"><h2>Resumen del pedido</h2><div class="summary-row"><span>Productos (<?= array_sum(array_column($items, 'qty')) ?>)</span><span><?= format_price($total, $currency) ?></span></div><div class="summary-total"><span>Total</span><strong><?= format_price($total, $currency) ?></strong></div>
                <?php if (is_logged_in()): ?><a class="button button-accent" href="checkout.php">Tramitar pedido</a><?php else: ?><a class="button button-accent" href="auth/auth-page.php?redirect=checkout">Acceder y tramitar pedido</a><p class="muted">Necesitas una cuenta para guardar tu pedido.</p><?php endif; ?>
                <p class="muted">Tu carrito se conserva en este navegador durante 30 días.</p>
            </aside>
        </div>
        <div class="cart-recommendations"><?php render_product_section('Completa tu pedido', 'Sugerencias a partir de los componentes del carrito', $cart_recommendations, $currency); ?></div>
    <?php endif; ?>
</main>
<?php render_footer(); ?>
