<?php

const CART_COOKIE_NAME = 'cart';
define('CART_COOKIE_EXPIRY', time() + (86400 * 30));
const CART_MAX_ITEMS = 30;

function read_cart(): array {
    if (!isset($_COOKIE[CART_COOKIE_NAME])) {
        return [];
    }

    $data = json_decode($_COOKIE[CART_COOKIE_NAME], true);

    if (!is_array($data)) {
        return [];
    }

    $clean = [];
    foreach($data as $id => $qty) {
        if((int)$id> 0 && (int)$qty > 0) {
            $clean[(int)$id] = (int)$qty;
        }
    }

    return $clean;
}

function save_cart(array $cart): void {
    $clean = [];
    foreach ($cart as $id => $qty) {
        if ((int)$id > 0 && (int)$qty > 0) {
            $clean[(int)$id] = (int)$qty;
        }
    }
    setcookie(CART_COOKIE_NAME, json_encode($clean), [
        'expires' => CART_COOKIE_EXPIRY,
        'path' => '/',
        'samesite' => 'Lax'
    ]);
    $_COOKIE[CART_COOKIE_NAME] = json_encode($clean);
}

function add_to_cart(int $id, int $qty, int $max_stock): bool {
    if ($max_stock <= 0 || $qty <= 0) return false;
    $cart = read_cart();
    $current_qty = $cart[$id] ?? 0;
    $new_qty = min($max_stock, $current_qty + $qty);
    $cart[$id] = $new_qty;
    save_cart($cart);
    return true;
}

function update_cart_item(int $id, int $qty, int $max_stock): void {
    $cart = read_cart();
    if($qty <= 0 || $max_stock <= 0) {
        unset($cart[$id]);
    } else {
        $cart[$id] = min($qty, $max_stock);
    }
    save_cart($cart);
}

function remove_from_cart(int $id): void {
    $cart = read_cart();
    unset($cart[$id]);
    save_cart($cart);
}

function clear_cart(): void {
    setcookie(CART_COOKIE_NAME, '', ['expires' => time() - 3600, 'path' => '/', 'samesite' => 'Lax']);
    unset($_COOKIE[CART_COOKIE_NAME]);
}

function get_cart_count(): int {
    return array_sum(read_cart());
}

?>