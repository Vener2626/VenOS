<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['items']) || !is_array($input['items'])) {
    http_response_code(422);
    echo json_encode(['error' => 'Cart is empty.']);
    exit;
}

$ids = array_map(fn ($item) => (int) $item['id'], $input['items']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);

$products = [];
foreach ($stmt->fetchAll() as $row) {
    $products[$row['id']] = ['name' => $row['name'], 'price' => (float) $row['price']];
}

$prices = array_map(fn ($p) => $p['price'], $products);

$subtotal = 0;
foreach ($input['items'] as $item) {
    $id = (int) $item['id'];
    $qty = max(1, (int) $item['qty']);
    if (isset($prices[$id])) {
        $subtotal += $prices[$id] * $qty;
    }
}

$allowedDiscounts = [0, 5, 10, 15, 20];
$discount = (int) ($input['discount'] ?? 0);
$discount = in_array($discount, $allowedDiscounts, true) ? $discount : 0;

$discountAmount = $subtotal * ($discount / 100);
$total = $subtotal - $discountAmount;

$paymentMethod = in_array($input['paymentMethod'] ?? '', ['cash', 'gcash'], true)
    ? $input['paymentMethod']
    : 'cash';

$change = 0;
if ($paymentMethod === 'cash') {
    $amountTendered = (float) ($input['amountTendered'] ?? 0);

    if ($amountTendered < $total) {
        http_response_code(422);
        echo json_encode(['error' => 'Amount tendered is less than the total due.']);
        exit;
    }

    $change = $amountTendered - $total;
}

try {
    $pdo->beginTransaction();

    $insertOrder = $pdo->prepare(
        'INSERT INTO orders (subtotal, discount_percent, discount_amount, total, payment_method, amount_tendered, change_due)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $insertOrder->execute([
        round($subtotal, 2),
        $discount,
        round($discountAmount, 2),
        round($total, 2),
        $paymentMethod,
        $paymentMethod === 'cash' ? round($amountTendered, 2) : null,
        $paymentMethod === 'cash' ? round($change, 2) : null,
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $insertItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    foreach ($input['items'] as $item) {
        $id = (int) $item['id'];
        $qty = max(1, (int) $item['qty']);
        if (!isset($products[$id])) {
            continue;
        }
        $insertItem->execute([
            $orderId,
            $id,
            $products[$id]['name'],
            $products[$id]['price'],
            $qty,
            round($products[$id]['price'] * $qty, 2),
        ]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Could not save the sale: ' . $e->getMessage()]);
    exit;
}

$message = 'Sale completed: ₱' . number_format($total, 2);
if ($paymentMethod === 'cash' && $change > 0) {
    $message .= ' (Change: ₱' . number_format($change, 2) . ')';
}

echo json_encode([
    'message' => $message,
    'total' => round($total, 2),
    'change' => round($change, 2),
    'paymentMethod' => $paymentMethod,
]);