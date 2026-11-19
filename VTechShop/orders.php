<?php
require_once(__DIR__ . '/config/connection.php');
require_once(__DIR__ . '/helpers/auth.php');
require_once(__DIR__ . '/helpers/orders.php');
require_once(__DIR__ . '/views/layout.php');

require_login();
$orders = get_user_orders($conn, (int)$_SESSION['user_id']);
$new_order_id = filter_input(INPUT_GET, 'new_order', FILTER_VALIDATE_INT);

$new_order_belongs_to_user = $new_order_id && in_array($new_order_id, array_map('intval', array_column($orders, 'order_id')), true);
render_head('Mis pedidos · VTechShop', '', 'page-orders');
render_header();
?>
<main id="main-content" class="site-main">
    <div class="commerce-heading"><div><p class="eyebrow">TU CUENTA</p><h1>Mis pedidos</h1></div><p><?= count($orders) ?> <?= count($orders) === 1 ? 'pedido registrado' : 'pedidos registrados' ?></p></div>
    <?php if ($new_order_belongs_to_user): ?><div class="alert alert-success" role="status"><?= get_icon('check') ?> Tu pedido #<?= (int)$new_order_id ?> se ha registrado correctamente.</div><?php endif; ?>
    <?php if (empty($orders)): ?>
        <div class="empty-state"><?= get_icon('receipt') ?><h2>Tu historial empieza con tu primer pedido</h2><p>Aquí encontrarás los productos, el importe y la dirección de cada compra.</p><a class="button button-accent" href="index.php">Explorar catálogo</a></div>
    <?php else: ?><div class="orders-list">
        <?php foreach ($orders as $order): ?>
        <article class="order-card"><header class="order-card-header"><div><h2>Pedido #<?= (int)$order['order_id'] ?></h2><p><time datetime="<?= date('c', strtotime($order['created_at'])) ?>"><?= date('d/m/Y · H:i', strtotime($order['created_at'])) ?></time></p></div><strong><?= format_price((float)$order['total_amount'], $order['currency_code']) ?></strong></header>
            <div class="order-card-body"><ul class="order-products"><?php foreach ($order['items'] as $item): ?><li><a href="products.php?id=<?= (int)$item['products_id'] ?>"><?= htmlspecialchars($item['products_name']) ?></a><strong><?= (int)$item['quantity'] ?> × <?= format_price((float)$item['price_at_purchase'], $order['currency_code']) ?></strong></li><?php endforeach; ?></ul><p class="order-address"><strong>Dirección de envío</strong><br><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p></div>
        </article>
        <?php endforeach; ?></div>
    <?php endif; ?>
</main>
<?php render_footer(); ?>
