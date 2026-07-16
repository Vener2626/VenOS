<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';

$stmt = $pdo->query('SELECT id, name, category, price, icon FROM products ORDER BY category, name');
$products = $stmt->fetchAll();

echo json_encode($products);