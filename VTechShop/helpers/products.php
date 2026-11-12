<?php 

include_once('helpers/errors.php');

function get_all_products(PDO $conn) {
    try {
        $stmt = $conn->query("SELECT * FROM products p JOIN categories c ON p.category_id = c.category_id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        throw new Exception("query_failed");
    }
}

?>