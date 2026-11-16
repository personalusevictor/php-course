<?php 
include_once(__DIR__ . '/errors.php');

function get_all_categories(PDO $conn): array {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
    return $stmt->fetchAll();
}

function get_all_products(PDO $conn, ?string $category_id = null): array {
    try {
        $sql = "SELECT p.products_id, p.category_id, p.products_name, p.products_description, p.brand, p.model, p.products_image, p.price, p.stock, c.category_name, c.category_description FROM products p JOIN categories c ON p.category_id = c.category_id";
        $params = [];
        if ($category_id !== null || $category_id > 0) {
            $sql .= " WHERE p.category_id = :cat_id";
            $params[':cat_id'] = $category_id;
        }
        $sql .= " ORDER BY c.category_name ASC, p.products_name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        throw new Exception("query_failed");
    }
}

function product_by_id(PDO $conn, int $id) {
    try {
        $stmt = $conn->prepare("SELECT p.products_id, p.category_id, p.products_name, p.products_description, p.brand, p.model, p.products_image, p.price, p.stock, c.category_name, c.category_description FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.products_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        throw new Exception('query_failed', 0, $e);
    }
}

function search_products(PDO $conn, string $term, int $limit = 8): array {
    $like = '%' . $term . '%';
    $stmt = $conn->prepare("SELECT p.products_id, p.products_name, p.brand, p.model, p.price, p.stock, p.products_image, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.products_name LIKE :term1 OR p.brand LIKE :term2 OR p.model LIKE :term3 OR c.category_name LIKE :term4 ORDER BY p.stock DESC, p.products_name ASC LIMIT :lim");
    $stmt->bindValue(':term1', $like, PDO::PARAM_STR);
    $stmt->bindValue(':term2', $like, PDO::PARAM_STR);
    $stmt->bindValue(':term3', $like, PDO::PARAM_STR);
    $stmt->bindValue(':term4', $like, PDO::PARAM_STR);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function group_products_by_category(array $products): array {
    $grouped = [];
    foreach ($products as $product) {
        $cat_id = $product['category_id'];
        if (!isset($grouped[$cat_id])) {
            $grouped[$cat_id] = [
                'id' => $cat_id,
                'name' => $product['category_name'],
                'description' => $product['category_description'],
                'products' => []
            ];
        }
        $grouped[$cat_id]['products'][] = $product;
    }
    return $grouped;
}

?>