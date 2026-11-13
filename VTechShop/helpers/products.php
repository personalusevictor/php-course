<?php 

include_once(dirname(__DIR__, 1) . '/helpers/errors.php');

function get_all_products(PDO $conn) {
    try {
        $stmt = $conn->query("SELECT p.products_id, p.category_id, p.products_name, p.products_description, p.brand, p.model, p.products_image, p.price, p.stock, c.category_name, c.category_description FROM products p JOIN categories c ON p.category_id = c.category_id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("query_failed");
    }
}

function model_key(array $products): string {
    return hash('sha256', json_encode([$products['category_id'], $products['products_id']]));
}

?>