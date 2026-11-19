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

$selected_name = 'Todos los componentes';
foreach ($categories as $category) {
    if ((int)$category['category_id'] === $selected_cat) $selected_name = $category['category_name'];
}
$category_counts = [];
if (!$selected_cat) foreach ($grouped_catalog as $group) $category_counts[$group['id']] = count($group['products']);

render_head('VTechShop · Tu equipo, pieza a pieza', '', 'page-catalog');
render_header();
?>
<main id="main-content" class="site-main">
    <div class="catalog-layout" id="catalogo">
        <aside class="catalog-sidebar">
            <div class="sidebar-heading"><h2>Categorías</h2><span><?= count($categories) ?></span></div>
            <nav class="category-nav" aria-label="Categorías de componentes">
                <a href="index.php#catalogo" class="<?= !$selected_cat ? 'is-active' : '' ?>" <?= !$selected_cat ? 'aria-current="page"' : '' ?>><?= get_icon('grid') ?><span>Todos los componentes</span></a>
                <?php foreach ($categories as $cat): ?>
                <a href="index.php?cat=<?= (int)$cat['category_id'] ?>#catalogo" class="<?= $selected_cat === (int)$cat['category_id'] ? 'is-active' : '' ?>" <?= $selected_cat === (int)$cat['category_id'] ? 'aria-current="page"' : '' ?>>
                    <img src="assets/illustrations/<?= category_art($cat['category_name']) ?>.svg" alt="" width="36" height="27"><span><?= htmlspecialchars($cat['category_name']) ?></span>
                    <?php if (isset($category_counts[$cat['category_id']])): ?><small><?= $category_counts[$cat['category_id']] ?></small><?php endif; ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <div class="catalog-content">
            <?php render_error($error_code); ?>
            <?php if (!$selected_cat): ?>
                <h1 class="sr-only">Catálogo de componentes y periféricos</h1>
                <?php [$rec_title, $rec_subtitle] = $recommendation_headings[$recommendations['mode']] ?? ['', null]; ?>
                <?php render_product_section($rec_title, $rec_subtitle, $recommendations['products'], $currency); ?>
                <?php render_product_section('Vuelve a echarles un vistazo', 'Tus productos vistos recientemente', $recent, $currency); ?>
            <?php else: ?>
                <div class="page-heading"><p class="eyebrow">CATÁLOGO</p><h1><?= htmlspecialchars($selected_name) ?></h1><p><?= count($products) ?> productos</p></div>
            <?php endif; ?>
            <?php if (empty($grouped_catalog)): ?>
                <div class="empty-state"><?= get_icon('grid') ?><h2>No hay productos en esta categoría</h2><a class="button" href="index.php">Ver todo el catálogo</a></div>
            <?php else: ?>
                <?php foreach ($grouped_catalog as $category): ?>
                <section class="product-section category-section">
                    <div class="section-heading"><div><h2><?= htmlspecialchars($category['name']) ?></h2><p><?= htmlspecialchars($category['description'] ?? '') ?></p></div><span class="section-count"><?= count($category['products']) ?> productos</span></div>
                    <div class="product-grid">
                        <?php foreach ($category['products'] as $product) render_product_card($product, '', $currency); ?>
                    </div>
                </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php render_footer(); ?>
