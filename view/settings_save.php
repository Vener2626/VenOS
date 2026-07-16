<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';
require __DIR__ . '/pin_auth.php';

pin_requireUnlocked();

$input = json_decode(file_get_contents('php://input'), true);
$currentPin = trim($input['currentPin'] ?? '');
$newPin = trim($input['newPin'] ?? '');
$confirmPin = trim($input['confirmPin'] ?? '');

if (!preg_match('/^\d{4}$/', $currentPin) || !preg_match('/^\d{4}$/', $newPin)) {
    http_response_code(422);
    echo json_encode(['error' => 'PIN must be exactly 4 digits.']);
    exit;
}

if ($newPin !== $confirmPin) {
    http_response_code(422);
    echo json_encode(['error' => 'New PIN and confirmation do not match.']);
    exit;
}

$hash = pin_getHash($pdo);

if (!password_verify($currentPin, $hash)) {
    http_response_code(401);
    echo json_encode(['error' => 'Current PIN is incorrect.']);
    exit;
}

$newHash = password_hash($newPin, PASSWORD_DEFAULT);
$pdo->prepare('UPDATE settings SET pin_hash = ? WHERE id = 1')->execute([$newHash]);

echo json_encode(['message' => 'PIN updated successfully.']);