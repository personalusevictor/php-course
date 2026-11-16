<?php
require_once(__DIR__ . '/config/connection.php');
require_once(__DIR__ . '/helpers/auth.php');
require_once(__DIR__ . '/helpers/product.php');
require_once(__DIR__ . '/helpers/recommendations.php');
require_once(__DIR__ . '/views/layout.php');

start_session_if_needed();
$currency = $_SESSION['currency_code'] ?? 'EUR';

$selected_cat = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT);
$error_code = null;
$products = [];
$categories = [];
$grouped_catalog = [];
$recent = [];
$recommendations = ['mode' => 'none', 'products' => []];
$recommendation_headings = [
    'personalized' => ['Recomendados para ti', 'Según los productos que has visto o añadido al carrito'],
    'bestsellers' => ['Los más vendidos', 'Lo que más están comprando nuestros clientes'],
    'featured' => ['Productos destacados', 'Una selección de distintas categorías'],
];

try {
    $categories = get_all_categories($conn);
    $products = get_all_products($conn, $selected_cat ?: null);
    $grouped_catalog = group_products_by_category($products);
    if (!$selected_cat) {
        $recent = get_recently_viewed($conn, 4);
        $recommendations = get_home_recommendations($conn, 4);
    }
} catch (Exception $e) {
    $error_code = $e->getMessage();
}

render_head('VTechShop - Componentes Informáticos');
render_header()
?>
<main>
    <nav>
        <a href="index.php"><?= !$selected_cat ? '<strong>Todas las categorías</strong>' : 'Todas las categorías' ?></a>
        <?php foreach ($categories as $cat): ?>
            <a href="index.php?cat=<?= $cat['category_id'] ?>"><?= $selected_cat === (int)$cat['category_id'] ? '<strong>' . htmlspecialchars($cat['category_name']) . '</strong>' : htmlspecialchars($cat['category_name']) ?></a>
        <?php endforeach; ?>
    </nav>

    <?php render_error($error_code); ?>

    <?php if (!$selected_cat): ?>
        <?php render_product_section('Vistos recientemente', null, $recent, $currency); ?>
        <?php [$rec_title, $rec_subtitle] = $recommendation_headings[$recommendations['mode']] ?? ['', null]; ?>
        <?php render_product_section($rec_title, $rec_subtitle, $recommendations['products'], $currency); ?>
    <?php endif; ?>

    <?php if (empty($grouped_catalog)): ?>
        <div>
            <p>No hay productos disponibles en esta categoría.</p>
            <a href="index.php">Ver todo el catálogo</a>
        </div>
    <?php else: ?>
        <?php foreach ($grouped_catalog as $category): ?>
            <section>
                <h2><?= htmlspecialchars($category['name']) ?></h2>
                <?php if (!empty($category['description'])): ?>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                <?php endif; ?>
                <div>
                    <?php foreach ($category['products'] as $product): ?>
                        <?php render_product_card($product, '', $currency); ?>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<?php render_footer(); ?>