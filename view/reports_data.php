<?php

header('Content-Type: application/json');
require __DIR__ . '/database.php';
require __DIR__ . '/reports_common.php';
require __DIR__ . '/pin_auth.php';

pin_requireUnlocked();

$type = $_GET['type'] ?? 'logs';
$offset = (int) ($_GET['offset'] ?? 0);

try {
    switch ($type) {
        case 'logs':
            $date = $_GET['date'] ?? date('Y-m-d');
            $page = (int) ($_GET['page'] ?? 1);
            echo json_encode(reports_getLogs($pdo, $date, $page));
            break;

        case 'daily':
            echo json_encode(reports_getDaily($pdo, $offset));
            break;

        case 'weekly':
            echo json_encode(reports_getWeekly($pdo, $offset));
            break;

        case 'monthly':
            echo json_encode(reports_getMonthly($pdo, $offset));
            break;

        case 'yearly':
            echo json_encode(reports_getYearly($pdo, $offset));
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown report type.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not load report data: ' . $e->getMessage()]);
}