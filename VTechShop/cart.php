<?php
require_once(__DIR__ . '/helpers/product.php');
require_once(__DIR__ . '/helpers/cart-cookie.php');
require_once(__DIR__ . '/config/connection.php');

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

    if(!$id || $id < 1) {
        header('Location: cart.php');
        exit;
    }

    if($action === 'remove') {
        remove_from_cart($id);
        header('Location: cart.php');
        exit;
    }

    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $product = product_by_id($conn, $id);

    if (!$product || !$quantity || $quantity <= 1) {
        header('Location: cart.php');
        exit;
    }

    if ($product && $quantity !== false && $quantity !== null && $quantity >= 0) {
        $stock = (int)$product['stock'];
        $action === 'update'
        ? update_cart_item($id, $quantity, $stock) 
        : ($quantity > 0 ? add_to_cart($id, $quantity, $stock) : null);
    }

    header('Location: cart.php');
    exit;
}

$cart = read_cart();
$items = [];
$total = 0;
$adjusted = false;

foreach($cart as $id => $quantity) {
    $product = product_by_id($conn, (int)$id);

    if (!$product || (int)$product['stock'] <= 0) {
        unset($cart[$id]);
        $adjusted = true;
        continue;
    }

    $stock = (int)$product['stock'];
    if($quantity > $stock) {
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito</title>
</head>
<body>
    <h1>Carrito de compra</h1>
    <?php if ($adjusted): ?>
        <p>Algunos productos se ajustaron para cambiar recientes en el stock.</p>
    <?php endif; ?>
    <?php if (empty($items)): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <ul>
        <?php foreach($items as $item): ?>
            <li>
                <strong><?= htmlspecialchars($item['name']) ?></strong> - <?= number_format($item['price'], 2, ',', '.') ?>€
                <form method="POST" action="cart.php">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>" />
                    <input type="hidden" name="quantity" value="<?= $item['qty'] - 1 ?>" />
                    <input type="submit" value="-">
                </form>
                <strong><?= $item['qty'] ?></strong>
                <form method="POST" action="cart.php">
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>" />
                    <input type="hidden" name="quantity" value="<?= $item['qty'] + 1 ?>" />
                    <input type="submit" value="+">
                </form>
                <form method="POST" action="cart.php">
                    <input type="hidden" name="action" value="remove" />
                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>" />
                    <input type="submit" value="X">
                </form>

                = <strong><?= number_format($item['subtotal'], 2, ',', '.') ?>€</strong>
            </li>
        <?php endforeach; ?>
        </ul>
        <p><strong>Total: <?= number_format($total, 2, ',', '.') ?>€</strong></p>
        <a href="checkout.php">Finalizar Compra</a>
    <?php endif; ?>
    <a href="index.php">Volver a la tienda</a>
</body>
</html>