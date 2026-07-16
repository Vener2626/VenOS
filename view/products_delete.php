<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';
require __DIR__ . '/pin_auth.php';

pin_requireUnlocked();

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int) $input['id'] : 0;

if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid product id.']);
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Could not delete this product. It may already be part of a past sale, so it can\'t be removed: ' . $e->getMessage(),
    ]);
    exit;
}

echo json_encode(['message' => 'Product deleted.']);