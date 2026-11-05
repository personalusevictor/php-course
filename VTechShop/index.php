<?php
require_once('config/connection.php');
require_once('helpers/products.php');

$products = get_all_products($conn);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VTechShop - Página Principal</title>
</head>
<body>
    <?php foreach($products as $product): ?>
        <article>
            <h2><?= $product['products_name'] ?></h2>
            <p><?= $product['products_description'] ?></p>
            <p><?= $product['brand'] . " " . $product['model'] ?></p>
            <p><strong><?= number_format($product['price'], 2) ?>€</strong></p>

        </article>
    <?php endforeach; ?>
</body>
</html>