<?php
require_once(__DIR__ . '/config/connection.php');
require_once(__DIR__ . '/helpers/product.php');
require_once(__DIR__ . '/helpers/cart-cookie.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? 'add';
$id = isset($input['product_id']) ? (int)$input['product_id'] : 0;
$qty = isset($input['quantity']) ? (int)$input['quantity'] : 1;

if ($id < 1) {
    echo json_encode(['success' => false, 'message' => 'Producto no válido']);
    exit;
}

if ($action === 'remove') {
    remove_from_cart($id);
    echo json_encode([
        'success' => true,
        'cart_count' => get_cart_count(),
        'message' => 'Producto eliminado del carrito'
    ]);
    exit;
}

$product = product_by_id($conn, $id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit;
}

$stock = (int)$product['stock'];
if ($stock <= 0) {
    echo json_encode(['success' => false, 'message' => 'Producto sin stock']);
    exit;
}

if ($action === 'update') {
    update_cart_item($id, $qty, $stock);
    echo json_encode([
        'success' => true,
        'cart_count' => get_cart_count(),
        'message' => 'Carrito actualizado'
    ]);
    exit;
}

$current_in_cart = read_cart()[$id] ?? 0;
if ($current_in_cart >= $stock) {
    echo json_encode([
        'success' => false,
        'cart_count' => get_cart_count(),
        'message' => 'Ya tienes el máximo de stock disponible en el carrito'
    ]);
    exit;
}

add_to_cart($id, max(1, $qty), $stock);

echo json_encode([
    'success' => true,
    'cart_count' => get_cart_count(),
    'message' => $product['products_name'] . ' añadido al carrito'
]);