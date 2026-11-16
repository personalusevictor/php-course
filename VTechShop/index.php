<?php
session_start();
ini_set('display_errors', 1);
require_once(__DIR__ . '/helpers/product.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}

$products = [];
$error_show = null;

require_once(__DIR__ . '/config/connection.php');

try {
    $products = get_all_products($conn);
} catch (Exception $e) {
    $error_show = $e->getMessage();
}

$grouped_catalog = [];
foreach ($products as $product) {
    $category_name = $product['category_name'];
    $model_key = model_key($product);

    if (!isset($grouped_catalog[$category_name])) {
        $grouped_catalog[$category_name] = [
            'description' => $product['category_description'],
            'models' => []
        ];
    }

    if (!isset($grouped_catalog[$category_name]['models'][$model_key])) {
        $grouped_catalog[$category_name]['models'][$model_key] = [
            'id' => $product['products_id'],
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
    <?php elseif (empty($grouped_catalog)): ?>
        <p>No hay productos disponibles en este momento.</p>
    <?php else: ?>
        <?php foreach ($grouped_catalog as $category_name => $category_data): ?>
            <section>
                <div>
                    <h2><?= htmlspecialchars($category_name) ?></h2>
                </div>
                <div>
                    <?php foreach ($category_data['models'] as $product): ?>
                        <article>
                            <?php if (!empty($product['image'])): ?>
                                <a href="products.php?id=<?= $product['id'] ?>">
                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['brand'] . ' ' . $product['model']) ?>" width="180">
                                </a>
                            <?php endif; ?>
                            <h3><a href="products.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['brand'] . ' ' . $product['model']) ?></a></h3>
                            <p class="price"><?= number_format((float)$product['price'], 2, ',', '.') ?>€</p>
                            <?php if ($product['stock'] <= 0): ?><p><strong>Sin stock</strong></p><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>