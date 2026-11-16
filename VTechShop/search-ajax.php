<?php
require_once(__DIR__ . '/config/connection.php');
require_once(__DIR__ . '/helpers/product.php');
require_once(__DIR__ . '/helpers/currency.php');
require_once(__DIR__ . '/helpers/auth.php');

header('Content-Type: application/json; charset=utf-8');
start_session_if_needed();
$currency = $_SESSION['currency_code'] ?? 'EUR';

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $results = search_products($conn, $q, 8);
    $formatted = array_map(function ($item) use ($currency) {
        return [
            'id' => (int)$item['products_id'],
            'name' => $item['products_name'],
            'category' => $item['category_name'],
            'price' => format_price((float)$item['price'], $currency),
            'stock' => (int)$item['stock'],
            'image' => $item['products_image'] ?? ''
        ];
    }, $results);
    echo json_encode($formatted);
} catch (Exception $e) {
    echo json_encode([]);
}