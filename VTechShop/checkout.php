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

render_head('Confirmar pedido · VTechShop', '', 'page-checkout');
render_header();
?>
<main id="main-content" class="site-main">
    <nav class="checkout-steps" aria-label="Proceso de compra"><a href="cart.php">Carrito</a><span>/</span><strong aria-current="step">Confirmar pedido</strong></nav>
    <div class="commerce-heading"><div><p class="eyebrow">EL ÚLTIMO PASO</p><h1>Prepara tu pedido</h1></div></div>
    <?php render_error($error_code); ?>
    <div class="commerce-layout">
        <form method="POST" action="checkout.php" class="checkout-form">
            <h2>Dirección de envío</h2><p class="muted">Indica dónde quieres recibir los componentes.</p>
            <div class="form-field"><label for="shipping_address">Dirección completa</label><textarea id="shipping_address" name="shipping_address" rows="4" minlength="10" required autocomplete="street-address" placeholder="Calle, número, piso, código postal y ciudad"><?= htmlspecialchars($_POST['shipping_address'] ?? '') ?></textarea></div>
            <button class="button button-accent" type="submit">Confirmar pedido</button>
            <p class="detail-add-note">Al confirmar, el pedido se guardará en tu historial.</p>
        </form>
        <aside class="order-summary"><h2>Tu pedido</h2><ul class="summary-items"><?php foreach ($items as $item): ?><li><span><?= (int)$item['qty'] ?> × <?= htmlspecialchars($item['name']) ?></span><strong><?= format_price($item['subtotal'], $currency) ?></strong></li><?php endforeach; ?></ul><div class="summary-total"><span>Total</span><strong><?= format_price($total, $currency) ?></strong></div><a class="text-link cart-continue" href="cart.php">Editar mi carrito</a></aside>
    </div>
</main>
<?php render_footer(); ?>
