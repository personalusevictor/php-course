<?php
include_once(__DIR__ . '/config/connection.php');
include_once(__DIR__ . '/helpers/product.php');
include_once(__DIR__ . '/helpers/cart-cookie.php');
include_once(__DIR__ . '/helpers/recommendations.php');
include_once(__DIR__ . '/views/layout.php');

start_session_if_needed();
$currency = $_SESSION['currency_code'] ?? 'EUR';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header('Location: index.php');
    exit;
}

$product = product_by_id($conn, $id);

if (!$product) {
    header('Location: index.php');
    exit;
}

record_product_view($id);

$in_cart = read_cart()[$id] ?? 0;
$stock = $product ? (int)$product['stock'] : 0;
$available_to_add = max(0, $stock - $in_cart);
$complements = get_complementary_products($conn, $product, 4);
$similar = get_similar_products($conn, $product, 4);

render_head($product['products_name'] . ' · VTechShop', '', 'page-product');
render_header();
?>
<main id="main-content" class="site-main">
    <nav class="breadcrumb" aria-label="Ruta del producto"><a href="index.php">Catálogo</a><span>/</span><a href="index.php?cat=<?= (int)$product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a><span>/</span><span><?= htmlspecialchars($product['model']) ?></span></nav>
    <article class="product-detail">
        <div class="product-visual">
            <?php render_product_media($product, '', true); ?>
            <div class="product-reference"><span><?= htmlspecialchars($product['category_name']) ?></span><span>Ref. <?= str_pad((string)$id, 4, '0', STR_PAD_LEFT) ?></span></div>
        </div>
        <div class="product-info">
            <p class="eyebrow"><?= htmlspecialchars($product['brand']) ?></p>
            <h1><?= htmlspecialchars($product['products_name']) ?></h1>
            <p class="detail-price"><?= format_price((float)$product['price'], $currency) ?></p>
            <p class="availability <?= $stock <= 0 ? 'out' : '' ?>"><?= $stock > 0 ? 'En stock · ' . $stock . ' unidades disponibles' : 'Producto agotado' ?></p>
            <dl class="spec-list"><dt>Marca</dt><dd><?= htmlspecialchars($product['brand']) ?></dd><dt>Modelo</dt><dd><?= htmlspecialchars($product['model']) ?></dd><dt>Categoría</dt><dd><?= htmlspecialchars($product['category_name']) ?></dd></dl>
            <?php if ($available_to_add > 0): ?>
                <div class="purchase-controls"><div class="quantity-field"><label for="detail-qty">Cantidad</label><input id="detail-qty" type="number" value="1" min="1" max="<?= $available_to_add ?>" required></div><button type="button" class="js-detail-add button button-accent" data-id="<?= $id ?>"><?= get_icon('bag') ?> Añadir al carrito</button></div>
                <a class="text-link detail-cart-link" href="cart.php">Ver mi carrito</a>
                <?php if ($in_cart > 0): ?><p class="detail-add-note">Ya tienes <?= $in_cart ?> unidades en tu carrito.</p><?php endif; ?>
            <?php elseif ($stock > 0): ?>
                <p class="detail-add-note">Ya tienes las <?= $in_cart ?> unidades disponibles en tu carrito.</p><a class="button" href="cart.php">Ver mi carrito</a>
            <?php else: ?><button type="button" class="button" disabled>Sin stock disponible</button><?php endif; ?>
            <div class="detail-description"><h2>Descripción y especificaciones</h2><p><?= nl2br(htmlspecialchars($product['products_description'] ?? 'Sin descripción adicional.')) ?></p></div>
        </div>
    </article>
    <?php
    $similar_subtitle = $stock > 0 ? 'Misma categoría y un precio parecido' : 'Estas alternativas sí tienen stock disponible';
    if ($stock <= 0) render_product_section('Alternativas disponibles', $similar_subtitle, $similar, $currency);
    render_product_section('Completa tu equipo', 'Sugerencias según la categoría y los datos técnicos disponibles', $complements, $currency);
    if ($stock > 0) render_product_section('Alternativas similares', $similar_subtitle, $similar, $currency);
    ?>
</main>
<?php render_footer(); ?>
