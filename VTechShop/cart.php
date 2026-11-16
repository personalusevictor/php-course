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

render_head('Carrito de Compra - VTechShop');
render_header();
?>
<main>
    <h1>Tu Carrito de Compra</h1>
    <?php if ($adjusted): ?>
        <p><strong>Aviso: </strong>Algunos productos de tu carrito han sido ajustado debido a cambios recientes en el stock disponible.</p>
    <?php endif; ?>
    <?php if (empty($items)): ?>
        <div>
            <p>Tu carrito está vacío en este momento.</p>
            <a href="index.php">Explorar componentes</a>
        </div>
    <?php else: ?>
        <ul>
            <?php foreach ($items as $item): ?>
                <?php if (!empty($item['image'])): ?>
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                <?php endif; ?>
                <h3><a href="products.php?id=<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></a></h3>
                <p>Precio unitario: <?= format_price($item['price'], $currency) ?>(Stock disponible <?= $item['stock'] ?> uds.)</p>

                <form action="cart.php" method="post">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>" />
                    <input type="hidden" name="quantity" value="<?= $item['qty'] - 1 ?>" />
                    <input type="submit" value="-" />
                </form>
                <strong><?= $item['qty'] ?></strong>

                <form action="cart.php" method="post">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>" />
                    <input type="hidden" name="quantity" value="<?= $item['qty'] + 1 ?>" />
                    <input type="submit" value="+" <?= $item['qty'] >= $item['stock'] ? 'disabled' : '' ?> />
                </form>

                <p>Subtotal: <strong><?= format_price($item['subtotal'], $currency) ?></strong></p>

                <form action="cart.php" method="post">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                    <input type="submit" value="Eliminar">
                </form>
            <?php endforeach; ?>
        </ul>

        <aside>
            <h2>Resumen del Pedido</h2>
            <p>Productos (<?= array_sum(array_column($items, 'qty')) ?>): <?= format_price($total, $currency) ?></p>
            <p>Envío: Gratis</p>
            <p><strong>Total IVA incluido: <?= format_price($total, $currency) ?></strong></p>

            <?php if (is_logged_in()): ?>
                <a href="checkout.php">Tramitar Pedido</a>
            <?php else: ?>
                <p>Inicia sesión para finalizar tu compra.</p>
                <a href="auth/auth-page.php?redirect=checkout">Inicia Sesión y Comprar</a>
            <?php endif; ?>
            <a href="index.php">Seguir comprando</a>
        </aside>

        <?php render_product_section('Completa tu pedido', 'Compatibles con lo que ya tienes en el carrito', $cart_recommendations, $currency); ?>
    <?php endif; ?>
</main>

<?php render_footer(); ?>