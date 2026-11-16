<?php
require_once(__DIR__ . '/config/connection.php');
require_once(__DIR__ . '/helpers/auth.php');
require_once(__DIR__ . '/helpers/orders.php');
require_once(__DIR__ . '/views/layout.php');

require_login();
$orders = get_user_orders($conn, (int)$_SESSION['user_id']);
$new_order_id = filter_input(INPUT_GET, 'new_order', FILTER_VALIDATE_INT);

render_head('Mis Pedidos - VTechShop');
render_header();
?>

<main>
    <h1>Mis Pedidos Realizados</h1>

    <?php if ($new_order_id): ?>
        <p><strong>¡Pedido #<?= $new_order_id ?> realizado con éxito! Gracias por confiar en VTechShop.</strong></p>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div>
            <p>Aún no has realizado ningún pedido.</p>
            <a href="index.php">Ir a la tienda</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <article>
                <h2>Pedido #<?= $order['order_id'] ?> (<?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>)</h2>
                <p><strong>Envío a:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                <ul>
                    <?php foreach ($order['items'] as $item): ?>
                        <li>
                            <a href="products.php?id=<?= $item['products_id'] ?>"><?= htmlspecialchars($item['products_name']) ?></a>
                            &times; <?= $item['quantity'] ?> -
                            <?= format_price((float)$item['price_at_purchase'] * (int)$item['quantity'], $order['currency_code']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><strong>Total: <?= format_price((float)$order['total_amount'], $order['currency_code']) ?></strong></p>
            </article>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php render_footer(); ?>