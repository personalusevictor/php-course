<?php

function process_checkout(PDO $conn, int $user_id, array $cart, string $shipping_address, string $currency_code = 'EUR'): array {
    if (empty($cart)) {
        return ['success' => false, 'error' => 'empty_cart'];
    }

    try {
        $conn->beginTransaction();

        $total_amount = 0;
        $order_items = [];

        $stmt_prod = $conn->prepare("SELECT products_id, products_name, price, stock FROM products WHERE products_id = :id FOR UPDATE");

        foreach ($cart as $pid => $qty) {
            $stmt_prod->execute([':id' => (int)$pid]);
            $product = $stmt_prod->fetch();

            if (!$product || (int)$product['stock'] < (int)$qty) {
                $conn->rollBack();
                return ['success' => false, 'error' => 'out_of_stock'];
            }

            $price = (float)$product['price'];
            $subtotal = $price * (int)$qty;
            $total_amount += $subtotal;

            $order_items[] = [
                'products_id' => (int)$pid,
                'quantity' => (int)$qty,
                'price_at_purchase' => $price,
            ];
        }

        $stmt_order = $conn->prepare("INSERT INTO orders(user_id, total_amount, currency_code, shipping_address) VALUES (:user_id, :total_amount, :currency_code, :shipping_address)");
        $stmt_order->execute([
            ':user_id' => $user_id,
            ':total_amount' => $total_amount,
            ':currency_code' => $currency_code,
            ':shipping_address' => $shipping_address,
        ]);

        $order_id = (int)$conn->lastInsertId();

        $stmt_item = $conn->prepare("INSERT INTO orders_items(order_id, products_id, quantity, price_at_purchase) VALUES (:order_id, :products_id, :quantity, :price_at_purchase)");
        $stmt_stock = $conn->prepare("UPDATE products SET stock = stock - :qty WHERE products_id = :pid");

        foreach ($order_items as $item) {
            $stmt_item->execute([
                ':order_id' => $order_id,
                ':products_id' => $item['products_id'],
                ':quantity' => $item['quantity'],
                ':price_at_purchase' => $item['price_at_purchase'],
            ]);
            $stmt_stock->execute([
                ':qty' => $item['quantity'],
                ':pid' => $item['products_id'],
            ]);
        }

        $conn->commit();
        return ['success' => true, 'order_id' => $order_id];
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        return ['success' => false, 'error' => 'order_failed'];
    }
}

function get_user_orders(PDO $conn, int $user_id): array {
    $stmt = $conn->prepare("SELECT order_id, total_amount, currency_code, shipping_address, created_at FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $user_id]);
    $orders = $stmt->fetchAll();

    $stmt_items = $conn->prepare("SELECT oi.quantity, oi.price_at_purchase, p.products_id, p.products_name, p.brand, p.model, p.products_image FROM orders_items oi JOIN products p ON oi.products_id = p.products_id WHERE oi.order_id = :order_id");

    foreach ($orders as &$order) {
        $stmt_items->execute([':order_id' => $order['order_id']]);
        $order['items'] = $stmt_items->fetchAll();
    }
    unset($order);

    return $orders;
}

?>