<?php
session_start();
require_once(__DIR__ . '/helpers/products.php');

$products = [];
$error_show = null;

if (isset($_POST['submit_logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

require_once(__DIR__ . '/config/connection.php');

try {
    $products = get_all_products($conn);
} catch (Exception $e) {
    $error_show = $e->getMessage();
}

$grouped_catalog = [];
foreach ($products as $product) {
    $category_name = $product['category_name'];
    $category_description = $product['category_description'];

    $model_key = model_key($product);

    if (!isset($grouped_catalog[$category_name])) {
        $grouped_catalog[$category_name] = [
            'description' => $category_description,
            'models' => []
        ];
    }

    if (!isset($grouped_catalog[$category_name]['models'][$model_key])) {
        $grouped_catalog[$category_name]['models'][$model_key] = [
            'id' => $product['products_id'],
            'description' => $product['products_description'],
            'brand' => $product['brand'],
            'model' => $product['model'],
            'price' => $product['price'],
            'stock' => $product['stock'],
            'image' => $product['products_image'],
        ];
    }
}

require_once(__DIR__ . '/views/layout.php');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VTechShop - Página Principal</title>
</head>

<body>
    <?php site_header() ?>
    <h1>Catálogo de Productos</h1>
    <?php if ($error_show): ?>
        <?php getErrors($error_show); ?>
    <?php elseif (empty($products)): ?>
        <p>No hay productos disponibles en este momento.</p>
    <?php else: ?>
        <?php if (empty($grouped_catalog)): ?>
            <p>No hay productos en el catalogo.</p>
        <?php else: ?>
            <?php foreach ($grouped_catalog as $category_name => $category_data): ?>
                <section>
                    <div>
                        <h2><?= htmlspecialchars($category_name) ?></h2>
                    </div>
                    <div>
                        <?php foreach ($category_data['models'] as $product): ?>
                            <article>
                                <h3><a href="products.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['brand'] . ' ' . $product['model']) ?></a></h3> <!-- Se que puedo poner $product['product_name'] para poner Procesador AMD Ryzen 5700 lo que sea pero prefiero ponerlo más limpio -->
                                <p><?= htmlspecialchars($product['description'] ?? '') ?></p>
                                <p class="price"><?= number_format((float)$product['price'], 2, ',', '.') ?>€</p>
                                <p><?= htmlspecialchars($product['stock'] > 0 ? 'Cantidad: ' . $product['stock'] : 'Agotado') ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>