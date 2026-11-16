<?php
require_once(__DIR__ . '/config/connection.php');
require_once(__DIR__ . '/helpers/auth.php');
require_once(__DIR__ . '/helpers/product.php');
require_once(__DIR__ . '/helpers/cart-cookie.php');
require_once(__DIR__ . '/helpers/orders.php');
require_once(__DIR__ . '/views/layout.php');

require_login('auth/auth-page.php?redirect=checkout');
$currency = $_SESSION['currency_code'] ?? 'EUR';

$cart = read_cart();
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

$error_code = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['shipping_address'] ?? '');
    if (mb_strlen($address) < 10) {
        $error_code = 'missing_fields';
    } else {
        $result = process_checkout($conn, (int)$_SESSION['user_id'], $cart, $address, $currency);
        if ($result['success']) {
            clear_cart();
            header('Location: orders.php?new_order=' . $result['order_id']);
            exit;
        } else {
            $error_code = $result['error'];
        }
    }
}

$items = [];
$total = 0;
foreach ($cart as $id => $qty) {
    $product = product_by_id($conn, (int)$id);
    if ($product && (int)$product['stock'] >= $qty) {
        $subtotal = (float)$product['price'] * $qty;
        $total += $subtotal;
        $items[] = [
            'name' => $product['products_name'],
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }
}

render_head('Finalizar Compra - VTechShop');
render_header();
?>

<main>
    <h1>Finalizar Compra</h1>
    <?php render_error($error_code); ?>

    <form method="POST" action="checkout.php">
        <h2>Dirección de Envío</h2>
        <div>
            <label for="shipping_address">Dirección completa (Calle, número, código postal, ciudad)</label><br>
            <textarea id="shipping_address" name="shipping_address" rows="4" required placeholder="Ej: Calle Mayor 15, 2ºB, 28013 Madrid"><?= htmlspecialchars($_POST['shipping_address'] ?? '') ?></textarea>
        </div>
        <button type="submit">Confirmar y Pagar Pedido</button>
    </form>

    <aside>
        <h2>Tu Pedido</h2>
        <ul>
            <?php foreach ($items as $item): ?>
                <li>
                    <span><?= $item['qty'] ?>x <?= htmlspecialchars($item['name']) ?></span> -
                    <strong><?= format_price($item['subtotal'], $currency) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
        <p><strong>Total a pagar: <?= format_price($total, $currency) ?></strong></p>
    </aside>
</main>

<?php render_footer(); ?>