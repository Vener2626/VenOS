<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';
require __DIR__ . '/pin_auth.php';
require __DIR__ . '/auth.php';

pin_requireUnlocked();
require_role(['owner']);

$input = json_decode(file_get_contents('php://input'), true);
$role = $input['role'] ?? '';
$newPin = trim($input['newPin'] ?? '');
$confirmPin = trim($input['confirmPin'] ?? '');

if (!in_array($role, ['cashier', 'owner'], true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Unknown PIN type.']);
    exit;
}

if (!preg_match('/^\d{6}$/', $newPin)) {
    http_response_code(422);
    echo json_encode(['error' => 'Login PIN must be exactly 6 digits.']);
    exit;
}

if ($newPin !== $confirmPin) {
    http_response_code(422);
    echo json_encode(['error' => 'New PIN and confirmation do not match.']);
    exit;
}

$column = $role === 'owner' ? 'owner_pin_hash' : 'cashier_pin_hash';
$otherColumn = $role === 'owner' ? 'cashier_pin_hash' : 'owner_pin_hash';

$row = $pdo->query("SELECT id, $otherColumn AS other_hash FROM settings ORDER BY id ASC LIMIT 1")->fetch();

if ($row && !empty($row['other_hash']) && password_verify($newPin, $row['other_hash'])) {
    http_response_code(422);
    echo json_encode(['error' => 'Cashier and owner login PINs must be different.']);
    exit;
}

$newHash = password_hash($newPin, PASSWORD_DEFAULT);

if ($row) {
    $pdo->prepare("UPDATE settings SET $column = ?, updated_at = NOW() WHERE id = ?")
        ->execute([$newHash, $row['id']]);
} else {
    $pdo->prepare("INSERT INTO settings ($column, updated_at) VALUES (?, NOW())")
        ->execute([$newHash]);
}

echo json_encode(['message' => ucfirst($role) . ' login PIN updated successfully.']);