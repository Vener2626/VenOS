<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';
require __DIR__ . '/pin_auth.php';

$input = json_decode(file_get_contents('php://input'), true);
$pin = trim($input['pin'] ?? '');

if (!preg_match('/^\d{4}$/', $pin)) {
    http_response_code(422);
    echo json_encode(['error' => 'Enter a 4-digit PIN.']);
    exit;
}

$hash = pin_getHash($pdo);

if (!password_verify($pin, $hash)) {
    http_response_code(401);
    echo json_encode(['error' => 'Incorrect PIN.']);
    exit;
}

$_SESSION['owner_unlocked_at'] = time();

echo json_encode(['valid' => true]);