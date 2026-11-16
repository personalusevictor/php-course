<?php
ini_set('display_errors', 1);

include_once(__DIR__ . '/helpers/product.php');
include_once(__DIR__ . '/helpers/cart-cookie.php');
include_once(__DIR__ . '/config/connection.php');


$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header('Location: index.php');
    exit;
}

$error_show = null;
$product = null;

try {
    $product = product_by_id($conn, $id);

    if (!$product) {
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    $error_show = $e->getMessage();
}

$in_cart = read_cart()[$id] ?? 0;
$stock = $product ? (int)$product['stock'] : 0;
$available_to_add = max(0, $stock - $in_cart);

include_once(__DIR__ . '/views/layout.php')
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
</head>

<body>
    <?php site_header(); ?>

    <main>
        <p><a href="index.php">&larr; Volver al catálogo</a></p>

        <?php if ($error_show !== null): ?>
            <?php getErrors($error_show); ?>
        <?php else: ?>
            <section>
                <?php if (!empty($product['products_image'])): ?>
                    <img src="<?= htmlspecialchars($product['products_image']) ?>" alt="<?= htmlspecialchars($product['brand'] . ' ' . $product['model']) ?>" width="300">
                <?php endif; ?>

                <p><strong>Categoría:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
                <h1><?= htmlspecialchars($product['products_name']) ?></h1>
                <p><strong>Marca:</strong> <?= htmlspecialchars($product['brand']) ?> | <strong>Modelo:</strong> <?= htmlspecialchars($product['model']) ?></p>
                <p><?= htmlspecialchars($product['products_description'] ?? '') ?></p>
                <p class="price"><strong>Precio:</strong> <?= number_format((float)$product['price'], 2, ',', '.') ?>€</p>
                <p><strong>Stock:</strong> <?= $stock > 0 ? $stock . ' uds.' : 'Sin stock' ?></p>
            </section>

            <?php if ($available_to_add > 0): ?>
                <form method="post" action="cart.php">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <label for="quantity">Cantidad: </label>
                    <input id="quantity" type="number" name="quantity" value="1" min="1" max="<?= $available_to_add ?>" required>
                    <input type="submit" name="submit_order" value="Añadir al carrito">
                </form>
                <?php if ($in_cart > 0): ?>
                    <p><small>Ya tienes <?= $in_cart ?> ud(s). de este producto en tu carrito.</small></p>
                <?php endif; ?>
            <?php elseif ($stock > 0): ?>
                <p><strong>Ya tienes el máximo de stock disponible en tu carrito (<?= $in_cart ?> uds.).</strong></p>
                <a href="cart.php">Ver carrito</a>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>

</html>