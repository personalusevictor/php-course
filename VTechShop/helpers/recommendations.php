<?php
require_once(__DIR__ . '/cart-cookie.php');

const HISTORY_COOKIE_NAME = 'vtech_history';
const HISTORY_MAX_ITEMS = 12;
const HISTORY_COOKIE_DAYS = 30;
const SALES_WINDOW_DAYS = 90;

const COMPATIBILITY_ATTRIBUTES = ['socket', 'memory_type'];

function get_view_history(): array {
    $data = json_decode($_COOKIE[HISTORY_COOKIE_NAME] ?? '', true);
    $ids = is_array($data) ? ($data['products'] ?? []) : [];
    if (!is_array($ids)) {
        return [];
    }

    $clean = [];
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id > 0 && !in_array($id, $clean, true)) {
            $clean[] = $id;
        }
    }
    return array_slice($clean, 0, HISTORY_MAX_ITEMS);
}

function record_product_view(int $product_id): void {
    if ($product_id < 1) {
        return;
    }

    $products = array_values(array_diff(get_view_history(), [$product_id]));
    array_unshift($products, $product_id);
    $json = json_encode(['products' => array_slice($products, 0, HISTORY_MAX_ITEMS)]);

    if (!headers_sent()) {
        setcookie(HISTORY_COOKIE_NAME, $json, [
            'expires' => time() + 86400 * HISTORY_COOKIE_DAYS,
            'path' => '/',
            'samesite' => 'Lax',
            'httponly' => true,
        ]);
    }
    $_COOKIE[HISTORY_COOKIE_NAME] = $json;
}

function fetch_products_by_ids(PDO $conn, array $ids): array {
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn($id) => $id > 0)));
    if (!$ids) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $conn->prepare(
        "SELECT p.*, c.category_name
         FROM products p
         JOIN categories c ON c.category_id = p.category_id
         WHERE p.products_id IN ($placeholders)"
    );
    $stmt->execute($ids);

    $by_id = [];
    foreach ($stmt->fetchAll() as $row) {
        $by_id[(int)$row['products_id']] = $row;
    }

    $ordered = [];
    foreach ($ids as $id) {
        if (isset($by_id[$id])) {
            $ordered[$id] = $by_id[$id];
        }
    }
    return $ordered;
}

function fetch_candidates(PDO $conn, array $category_ids, array $exclude_ids = []): array {
    $category_ids = array_values(array_map('intval', $category_ids));
    if (!$category_ids) {
        return [];
    }

    $sql = "SELECT p.*, c.category_name
            FROM products p
            JOIN categories c ON c.category_id = p.category_id
            WHERE p.stock > 0 AND p.category_id IN (" . implode(',', array_fill(0, count($category_ids), '?')) . ")";
    $params = $category_ids;

    $exclude_ids = array_values(array_unique(array_map('intval', $exclude_ids)));
    if ($exclude_ids) {
        $sql .= " AND p.products_id NOT IN (" . implode(',', array_fill(0, count($exclude_ids), '?')) . ")";
        $params = array_merge($params, $exclude_ids);
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_category_affinities(PDO $conn): array {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $rows = $conn->query("SELECT category_id, related_category_id, weight FROM category_affinity")->fetchAll();
        foreach ($rows as $row) {
            $cache[(int)$row['category_id']][(int)$row['related_category_id']] = (float)$row['weight'];
        }
    }
    return $cache;
}

function get_sales_map(PDO $conn): array {
    static $cache = null;
    if ($cache === null) {
        $stmt = $conn->prepare(
            "SELECT oi.products_id, SUM(oi.quantity) AS units
             FROM orders_items oi
             JOIN orders o ON o.order_id = oi.order_id
             WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
             GROUP BY oi.products_id"
        );
        $stmt->bindValue(':days', SALES_WINDOW_DAYS, PDO::PARAM_INT);
        $stmt->execute();

        $cache = [];
        foreach ($stmt->fetchAll() as $row) {
            $cache[(int)$row['products_id']] = (int)$row['units'];
        }
    }
    return $cache;
}

function popularity_score(array $sales, int $product_id): float {
    $max = $sales ? max($sales) : 0;
    $units = $sales[$product_id] ?? 0;
    return $max > 0 ? log(1 + $units) / log(1 + $max) : 0.0;
}

function get_price_index(PDO $conn): array {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $rows = $conn->query("SELECT category_id, price FROM products WHERE stock > 0 ORDER BY price ASC")->fetchAll();
        foreach ($rows as $row) {
            $cache[(int)$row['category_id']][] = (float)$row['price'];
        }
    }
    return $cache;
}

function price_percentile(array $price_index, int $category_id, float $price): float {
    $prices = $price_index[$category_id] ?? [];
    if (!$prices) {
        return 0.5;
    }
    $not_above = count(array_filter($prices, fn($p) => $p <= $price));
    return max(0.0, min(1.0, ($not_above - 0.5) / count($prices)));
}

function check_compatibility(array $a, array $b): ?array {
    $matches = [];
    foreach (COMPATIBILITY_ATTRIBUTES as $attribute) {
        $value_a = $a[$attribute] ?? null;
        $value_b = $b[$attribute] ?? null;
        if ($value_a === null || $value_b === null) {
            continue;
        }
        if ($value_a !== $value_b) {
            return null;
        }
        $matches[] = $value_a;
    }
    return $matches;
}

function short_model(array $product, int $max = 36): string {
    return mb_strimwidth((string)$product['model'], 0, $max, '…');
}

function sort_by_score(array &$items): void {
    usort($items, fn($x, $y) => [$y['relevance_score'], $x['products_id']] <=> [$x['relevance_score'], $y['products_id']]);
}

function pick_diverse(array $sorted, int $limit, callable $group_of): array {
    $picked = [];
    $seen = [];

    foreach ($sorted as $i => $item) {
        if (count($picked) >= $limit) {
            break;
        }
        $group = $group_of($item);
        if (!isset($seen[$group])) {
            $seen[$group] = true;
            $picked[$i] = $item;
        }
    }
    foreach ($sorted as $i => $item) {
        if (count($picked) >= $limit) {
            break;
        }
        $picked[$i] ??= $item;
    }

    ksort($picked);
    return array_values($picked);
}

function recommend_complements(PDO $conn, array $anchors, int $limit, array $exclude_ids = [], array $exclude_categories = []): array {
    if (!$anchors || $limit < 1) {
        return [];
    }

    $affinities = get_category_affinities($conn);
    $related = [];
    foreach ($anchors as $anchor) {
        foreach ($affinities[(int)$anchor['product']['category_id']] ?? [] as $category_id => $weight) {
            if (!in_array($category_id, $exclude_categories, true)) {
                $related[$category_id] = true;
            }
        }
    }

    $candidates = fetch_candidates($conn, array_keys($related), $exclude_ids);
    if (!$candidates) {
        return [];
    }

    $price_index = get_price_index($conn);
    $sales = get_sales_map($conn);

    foreach ($anchors as &$anchor) {
        $anchor['percentile'] = price_percentile($price_index, (int)$anchor['product']['category_id'], (float)$anchor['product']['price']);
    }
    unset($anchor);

    $scored = [];
    foreach ($candidates as $candidate) {
        $candidate_category = (int)$candidate['category_id'];
        $candidate_percentile = price_percentile($price_index, $candidate_category, (float)$candidate['price']);
        $popularity = popularity_score($sales, (int)$candidate['products_id']);

        $total = 0.0;
        $best = 0.0;
        $reason = null;
        $rejected = false;

        foreach ($anchors as $anchor) {
            $affinity = $affinities[(int)$anchor['product']['category_id']][$candidate_category] ?? 0.0;
            if ($affinity <= 0) {
                continue;
            }

            $matches = check_compatibility($anchor['product'], $candidate);
            if ($matches === null) {
                if ($anchor['hard']) {
                    $rejected = true;
                    break;
                }
                continue;
            }

            $tier = 1 - abs($anchor['percentile'] - $candidate_percentile);
            $score = 0.45 * $affinity + 0.30 * $tier + 0.08 * count($matches) + 0.10 * $popularity;
            $contribution = $anchor['weight'] * $score;
            $total += $contribution;

            if ($contribution > $best) {
                $best = $contribution;
                $name = short_model($anchor['product']);
                $reason = $matches
                    ? "Compatible con {$name} (" . implode(' · ', $matches) . ')'
                    : "Combina bien con {$name}";
            }
        }

        if ($rejected || $total <= 0) {
            continue;
        }

        $candidate['relevance_score'] = $total;
        $candidate['reason'] = $reason;
        $scored[] = $candidate;
    }

    sort_by_score($scored);
    return pick_diverse($scored, $limit, fn($p) => $p['category_id']);
}

function recommend_similar(PDO $conn, array $product, int $limit = 4, array $exclude_ids = []): array {
    $base_price = (float)$product['price'];
    if ($base_price <= 0) {
        return [];
    }

    $exclude_ids[] = (int)$product['products_id'];
    $sales = get_sales_map($conn);

    $scored = [];
    foreach (fetch_candidates($conn, [(int)$product['category_id']], $exclude_ids) as $candidate) {
        if (check_compatibility($product, $candidate) === null) {
            continue;
        }

        $ratio = (float)$candidate['price'] / $base_price;
        $closeness = 1 - abs(log($ratio)) / log(2.5);
        if ($closeness <= 0) {
            continue;
        }

        $candidate['relevance_score'] = 0.8 * $closeness + 0.2 * popularity_score($sales, (int)$candidate['products_id']);
        $candidate['reason'] = $ratio < 0.85
            ? 'Alternativa más económica'
            : ($ratio > 1.18 ? 'Alternativa de gama superior' : 'Alternativa de precio similar');
        $scored[] = $candidate;
    }

    sort_by_score($scored);
    return pick_diverse($scored, $limit, fn($p) => $p['brand']);
}

function collect_anchors(PDO $conn, bool $include_views = true): array {
    $cart = read_cart();
    $history = $include_views ? get_view_history() : [];
    $products = fetch_products_by_ids($conn, array_merge(array_keys($cart), $history));

    $anchors = [];
    $cart_categories = [];
    foreach (array_keys($cart) as $id) {
        if (isset($products[$id])) {
            $anchors[] = ['product' => $products[$id], 'weight' => 3.0, 'hard' => true];
            $cart_categories[] = (int)$products[$id]['category_id'];
        }
    }

    $weight = 1.0;
    foreach ($history as $id) {
        if (isset($cart[$id]) || !isset($products[$id])) {
            continue;
        }
        $anchors[] = ['product' => $products[$id], 'weight' => $weight, 'hard' => false];
        $weight *= 0.8;
    }

    return [
        'anchors' => $anchors,
        'exclude_ids' => array_keys($products),
        'cart_categories' => array_values(array_unique($cart_categories)),
    ];
}

function recommend_popular(PDO $conn, int $limit, array $exclude_ids = []): array {
    $sales = get_sales_map($conn);
    arsort($sales);
    $top_ids = array_slice(array_diff(array_keys($sales), $exclude_ids), 0, $limit * 3);

    if ($top_ids) {
        $products = array_filter(fetch_products_by_ids($conn, $top_ids), fn($p) => (int)$p['stock'] > 0);
        if (count($products) >= $limit) {
            return ['mode' => 'bestsellers', 'products' => array_slice(array_values($products), 0, $limit)];
        }
    }

    $stmt = $conn->query(
        "SELECT p.*, c.category_name
         FROM products p JOIN categories c ON c.category_id = p.category_id
         WHERE p.stock >= 5"
    );
    $by_category = [];
    foreach ($stmt->fetchAll() as $row) {
        if (!in_array((int)$row['products_id'], $exclude_ids, true)) {
            $by_category[(int)$row['category_id']][] = $row;
        }
    }

    $picks = [];
    foreach ($by_category as $category_id => $rows) {
        usort($rows, fn($x, $y) => (float)$x['price'] <=> (float)$y['price']);
        $median = (float)$rows[intdiv(count($rows), 2)]['price'];
        usort($rows, fn($x, $y) => [abs((float)$x['price'] - $median), $x['products_id']] <=> [abs((float)$y['price'] - $median), $y['products_id']]);
        $picks[$category_id] = $rows[0];
    }

    $seed = date('Y-m-d');
    uksort($picks, fn($a, $b) => crc32("$seed-$a") <=> crc32("$seed-$b"));

    return ['mode' => 'featured', 'products' => array_values(array_slice($picks, 0, $limit))];
}

function get_home_recommendations(PDO $conn, int $limit = 4): array {
    try {
        $context = collect_anchors($conn);
        if ($context['anchors']) {
            $products = recommend_complements($conn, $context['anchors'], $limit, $context['exclude_ids'], $context['cart_categories']);
            if (count($products) >= 2) {
                return ['mode' => 'personalized', 'products' => $products];
            }
        }
        return recommend_popular($conn, $limit, $context['exclude_ids']);
    } catch (PDOException $e) {
        error_log('[recommendations] ' . $e->getMessage());
        return ['mode' => 'none', 'products' => []];
    }
}

function get_recently_viewed(PDO $conn, int $limit = 4, ?int $exclude_id = null): array {
    try {
        $ids = array_values(array_diff(get_view_history(), $exclude_id ? [$exclude_id] : []));
        return array_values(fetch_products_by_ids($conn, array_slice($ids, 0, $limit)));
    } catch (PDOException $e) {
        error_log('[recommendations] ' . $e->getMessage());
        return [];
    }
}

function get_complementary_products(PDO $conn, array $product, int $limit = 4): array {
    try {
        $product = fetch_products_by_ids($conn, [(int)$product['products_id']])[(int)$product['products_id']] ?? $product;
        $in_cart = array_keys(read_cart());
        $anchors = [['product' => $product, 'weight' => 1.0, 'hard' => true]];
        return recommend_complements($conn, $anchors, $limit, array_merge([(int)$product['products_id']], $in_cart));
    } catch (PDOException $e) {
        error_log('[recommendations] ' . $e->getMessage());
        return [];
    }
}

function get_similar_products(PDO $conn, array $product, int $limit = 4): array {
    try {
        $product = fetch_products_by_ids($conn, [(int)$product['products_id']])[(int)$product['products_id']] ?? $product;
        return recommend_similar($conn, $product, $limit);
    } catch (PDOException $e) {
        error_log('[recommendations] ' . $e->getMessage());
        return [];
    }
}

function get_cart_recommendations(PDO $conn, int $limit = 4): array {
    try {
        $context = collect_anchors($conn, false);
        return recommend_complements($conn, $context['anchors'], $limit, $context['exclude_ids'], $context['cart_categories']);
    } catch (PDOException $e) {
        error_log('[recommendations] ' . $e->getMessage());
        return [];
    }
}
