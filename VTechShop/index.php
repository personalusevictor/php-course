<?php
require_once('config/connection.php');
require_once('helpers/products.php');

$products = [];
$error_show = null;

try {
    $products = get_all_products($conn);
} catch(Exception $e) {
    $error_show = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VTechShop - Página Principal</title>
</head>
<header>
    <nav>
        <ul>
            <li><a href="index.php">Página Principal</a></li>
            <li><a href="auth/auth-page.php">Autenticate</a></li>
        </ul>
    </nav>
</header>
<body>
    <h1>Catálogo de Productos</h1>
    <?php if($error_show): ?>
        <?php getErrors($error_show); ?>
    <?php elseif (empty($products)): ?>
        <p>No hay productos disponibles en este momento.</p>
    <?php else: ?>
        <section class="products-grid">
            <?php foreach ($products as $product): ?>
                <article>
                    <span><?= htmlspecialchars($product['category_name']) ?></span>
                    <h2><?= htmlspecialchars($product['products_name']) ?></h2>
                    <p><?= htmlspecialchars($product['products_description'] ?? '') ?></p>
                    <p><?= htmlspecialchars($product['brand'] . " " . $product['model']) ?></p>
                    <p><strong><?= htmlspecialchars(number_format($product['price'], 2, ',', '.')) ?>€</strong></p>
                    <p>
                        <?php if($product['stock'] > 0): ?>
                            <span>Unidades disponibles: <?= $product['stock'] ?></span>
                        <?php else: ?>
                            <span>¡Agotado!</span>
                        <?php endif; ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</body>

</html>