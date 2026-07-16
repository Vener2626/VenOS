<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';
require __DIR__ . '/pin_auth.php';

pin_requireUnlocked();

$dbReady = true;
$todayStats = ['cnt' => 0, 'revenue' => 0.0, 'cash_count' => 0, 'gcash_count' => 0];
$days = [];
$orders = [];
$topProducts = [];
$slowProducts = [];

try {
    $row = $pdo->query(
        "SELECT COUNT(*) AS cnt, COALESCE(SUM(total), 0) AS revenue,
                SUM(CASE WHEN payment_method = 'cash' THEN 1 ELSE 0 END) AS cash_count,
                SUM(CASE WHEN payment_method = 'gcash' THEN 1 ELSE 0 END) AS gcash_count
         FROM orders WHERE DATE(created_at) = CURDATE()"
    )->fetch();
    $todayStats = [
        'cnt' => (int) $row['cnt'],
        'revenue' => (float) $row['revenue'],
        'cash_count' => (int) $row['cash_count'],
        'gcash_count' => (int) $row['gcash_count'],
    ];

    $revenueByDate = [];
    $stmt = $pdo->query(
        "SELECT DATE(created_at) AS d, SUM(total) AS revenue
         FROM orders
         WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
         GROUP BY DATE(created_at)"
    );
    foreach ($stmt->fetchAll() as $r) {
        $revenueByDate[$r['d']] = (float) $r['revenue'];
    }

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} day"));
        $days[] = [
            'label' => date('D', strtotime($date)),
            'revenue' => $revenueByDate[$date] ?? 0.0,
        ];
    }

    $ordersRaw = $pdo->query(
        "SELECT id, total, payment_method, created_at FROM orders ORDER BY created_at DESC LIMIT 100"
    )->fetchAll();

    $orderIds = array_column($ordersRaw, 'id');
    $itemsByOrder = [];
    if ($orderIds) {
        $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
        $itemStmt = $pdo->prepare(
            "SELECT order_id, product_name, quantity FROM order_items WHERE order_id IN ($placeholders) ORDER BY id"
        );
        $itemStmt->execute($orderIds);
        foreach ($itemStmt->fetchAll() as $it) {
            $itemsByOrder[$it['order_id']][] = $it;
        }
    }

    foreach ($ordersRaw as $o) {
        $items = $itemsByOrder[$o['id']] ?? [];
        $shown = array_slice($items, 0, 2);
        $parts = array_map(fn ($i) => $i['product_name'] . " \u{00D7}" . (int) $i['quantity'], $shown);
        $summary = implode(', ', $parts);
        $extra = count($items) - count($shown);

        $orders[] = [
            'id' => $o['id'],
            'total' => (float) $o['total'],
            'payment_method' => $o['payment_method'],
            'created_at' => $o['created_at'],
            'itemsSummary' => $extra > 0 ? "{$summary} +{$extra}" : $summary,
        ];
    }
    // Every active product, ranked by units sold in the last 30 days.
    // LEFT JOIN so products with zero sales still show up (as slow movers)
    // instead of silently disappearing from the list.
    $perfRows = $pdo->query(
        "SELECT p.id, p.name, p.icon,
                COALESCE(x.qty_sold, 0) AS qty_sold,
                COALESCE(x.revenue, 0) AS revenue
         FROM products p
         LEFT JOIN (
             SELECT oi.product_id, SUM(oi.quantity) AS qty_sold, SUM(oi.line_total) AS revenue
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY oi.product_id
         ) x ON x.product_id = p.id
         ORDER BY qty_sold DESC, p.name ASC"
    )->fetchAll();

    $performance = array_map(fn ($r) => [
        'id' => (int) $r['id'],
        'name' => $r['name'],
        'icon' => $r['icon'],
        'qtySold' => (int) $r['qty_sold'],
        'revenue' => (float) $r['revenue'],
    ], $perfRows);

    $topProducts = array_slice($performance, 0, 5);
    // Slow movers: worst performers, but only ones that actually exist in
    // meaningful numbers — no point flooding this with a 1-product menu.
    $slowProducts = array_slice(array_reverse($performance), 0, 5);
} catch (PDOException $e) {
    $dbReady = false;
}

$avgOrder = $todayStats['cnt'] > 0 ? $todayStats['revenue'] / $todayStats['cnt'] : 0;

echo json_encode([
    'dbReady' => $dbReady,
    'today' => array_merge($todayStats, ['avgOrder' => $avgOrder]),
    'days' => $days,
    'orders' => $orders,
    'topProducts' => $topProducts,
    'slowProducts' => $slowProducts,
]);