<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';
require __DIR__ . '/pin_auth.php';

pin_requireUnlocked();

$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name'] ?? '');
$category = trim($input['category'] ?? '');
$price = (float) ($input['price'] ?? 0);
$icon = trim($input['icon'] ?? '');
$id = isset($input['id']) && $input['id'] !== '' ? (int) $input['id'] : null;

if ($name === '' || $category === '' || $price <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Please provide a name, category, and a price greater than 0.']);
    exit;
}

try {
    if ($id) {
        $stmt = $pdo->prepare('UPDATE products SET name = ?, category = ?, price = ?, icon = ? WHERE id = ?');
        $stmt->execute([$name, $category, $price, $icon, $id]);
        $message = 'Product updated.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO products (name, category, price, icon) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $category, $price, $icon]);
        $id = (int) $pdo->lastInsertId();
        $message = 'Product added.';
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not save the product: ' . $e->getMessage()]);
    exit;
}

$row = $pdo->prepare('SELECT id, name, category, price, icon FROM products WHERE id = ?');
$row->execute([$id]);

echo json_encode([
    'message' => $message,
    'product' => $row->fetch(),
]);