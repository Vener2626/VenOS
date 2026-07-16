<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';

// Best seller = more than 5 units sold in the last 30 days.
// Matches the same metric the "Top Sellers" dashboard panel uses
// (qty_sold), so a product badged here is always consistent with what
// the owner sees on the dashboard.
// Kept as its own tiny endpoint (separate from dashboard_data.php) so the
// POS screen stays fast and doesn't need to wait on the full dashboard query.
$bestSellerIds = [];

try {
    $stmt = $pdo->query(
        "SELECT oi.product_id, SUM(oi.quantity) AS qty_sold
         FROM order_items oi
         JOIN orders o ON o.id = oi.order_id
         WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY oi.product_id
         HAVING qty_sold > 5
         ORDER BY qty_sold DESC"
    );
    $bestSellerIds = array_map(fn ($r) => (int) $r['product_id'], $stmt->fetchAll());
} catch (PDOException $e) {
    // orders table may not exist yet (fresh install) — fail quietly,
    // the POS just won't show badges until there's sales history.
    $bestSellerIds = [];
}

echo json_encode(['bestSellerIds' => $bestSellerIds]);