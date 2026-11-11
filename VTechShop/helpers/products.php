<?php 

include_once('helpers/errors.php');

function get_all_products(PDO $conn) {
    try {
        $stmt = $conn->query("SELECT * FROM products");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        getErrors("db_failed");
        return [];
    }
}

?>