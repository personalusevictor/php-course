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

render_head($product['products_name'] . ' - VTechShop');
render_header();
?>
<main>
    <nav>
        <a href="index.php">Catálogo</a> &rsaquo;
        <a href="index.php?cat=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a> &rsaquo;
        <span><?= htmlspecialchars($product['model']) ?></span>
    </nav>

    <section>
        <?php if (!empty($product['products_image'])): ?>
            <img src="<?= htmlspecialchars($product['products_image']) ?>" alt="<?= htmlspecialchars($product['products_name']) ?>" width="300">
        <?php endif; ?>

        <p><strong>Categoría:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
        <h1><?= htmlspecialchars($product['products_name']) ?></h1>
        <p>
            <strong>Marca:</strong> <?= htmlspecialchars($product['brand']) ?> |
            <strong>Modelo:</strong> <?= htmlspecialchars($product['model']) ?>
        </p>
        <p><strong>Precio:</strong> <?= format_price((float)$product['price'], $currency) ?></p>

        <div>
            <?php if ($stock > 5): ?>
                <p>En stock (<?= $stock ?> unidades disponibles)</p>
            <?php elseif ($stock > 0): ?>
                <p>¡Date prisa! Solo quedan <?= $stock ?> unidades</p>
            <?php else: ?>
                <p><strong>Producto agotado temporalmente (Sin stock)</strong></p>
            <?php endif; ?>
        </div>

        <div>
            <h3>Descripción y especificaciones</h3>
            <p><?= nl2br(htmlspecialchars($product['products_description'] ?? 'Sin descripción adicional.')) ?></p>
        </div>

        <div>
            <?php if ($available_to_add > 0): ?>
                <label for="detail-qty">Cantidad:</label>
                <input id="detail-qty" type="number" value="1" min="1" max="<?= $available_to_add ?>">
                <button type="button" class="js-detail-add" data-id="<?= $id ?>">
                    Añadir al carrito
                </button>
                <a href="cart.php">Ir al carrito</a>
            <?php elseif ($stock > 0): ?>
                <p>Ya tienes en tu carrito el máximo de unidades disponibles (<?= $in_cart ?>).</p>
                <a href="cart.php">Ver mi carrito</a>
            <?php else: ?>
                <button type="button" disabled>Sin stock disponible</button>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($complements) || !empty($similar)): ?>
        <hr>
        <?php
        $similar_subtitle = $stock > 0
            ? 'Otras opciones de la misma categoría y de precio parecido'
            : 'Este producto está agotado; estas opciones sí están disponibles';
        ?>
        <?php if ($stock <= 0): ?>
            <?php render_product_section('Alternativas disponibles', $similar_subtitle, $similar, $currency); ?>
        <?php endif; ?>
        <?php render_product_section('Completa tu equipo', 'Productos compatibles seleccionados para este artículo', $complements, $currency); ?>
        <?php if ($stock > 0): ?>
            <?php render_product_section('Alternativas similares', $similar_subtitle, $similar, $currency); ?>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php render_footer(); ?>