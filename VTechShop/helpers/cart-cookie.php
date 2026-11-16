<?php

const CART_COOKIE_NAME = 'cart';
const CART_COOKIE_DAYS = 7;
const CART_MAX_ITEMS = 30;

function read_cart(): array {
    if (!isset($_COOKIE[CART_COOKIE_NAME])) {
        return [];
    }

    $cart = json_decode($_COOKIE[CART_COOKIE_NAME], true);

    if (!is_array($cart)) {
        return [];
    }

    $clean_cart = [];
    foreach($cart as $id => $qty) {
        $clean_id = filter_var($id, FILTER_VALIDATE_INT);
        $clean_qty = filter_var($qty, FILTER_VALIDATE_INT);

        if($clean_id && $clean_id > 0 && $clean_qty && $clean_qty > 0) {
            $clean_cart[$clean_id] = $clean_qty;
        }
    }

    return $clean_cart;
}

function save_cart(array $cart): void {
    setcookie(CART_COOKIE_NAME, json_encode($cart), [
        'expires' => time() + (86400 * CART_COOKIE_DAYS),
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

function add_to_cart(int $id, int $qty, int $max_stock): array {
    $cart = read_cart();
    if (!isset($cart[$id]) && count($cart) >= CART_MAX_ITEMS) {
        return $cart;
    }

    $current_qty = $cart[$id] ?? 0;
    $new_qty = $current_qty + $qty;

    if($new_qty > $max_stock) {
        $new_qty = $max_stock;
    }

    $cart[$id] = $new_qty;
    save_cart($cart);

    return $cart;
}

function update_cart_item(int $id, int $qty, int $max_stock): array {
    $cart = read_cart();

    if($qty <= 0 || $max_stock <= 0) {
        unset($cart[$id]);
    } else {
        $cart[$id] = min($qty, $max_stock);
    }

    save_cart($cart);
    return $cart;
}

function remove_from_cart(int $id): array {
    $cart = read_cart();
    unset($cart[$id]);
    save_cart($cart);
    return $cart;
}

function clear_cart(): void {
    setcookie(CART_COOKIE_NAME, '', time() - 3600, '/');
}

?>