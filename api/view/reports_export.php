<?php

require __DIR__ . '/database.php';
require __DIR__ . '/reports_common.php';
require __DIR__ . '/pin_auth.php';

pin_requireUnlocked();

$type = $_GET['type'] ?? 'logs';
$offset = (int) ($_GET['offset'] ?? 0);

function reports_csvOut(string $filename, array $header, array $rows): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel/WPS don't misread ×, ₱, etc. as ANSI
    fputcsv($out, $header);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
}

try {
    switch ($type) {
        case 'logs':
            $date = $_GET['date'] ?? date('Y-m-d');
            $data = reports_getLogs($pdo, $date);

            $rows = array_map(fn ($r) => [
                $data['date'],
                $r['time'],
                $r['items'],
                ucfirst($r['payment']),
                number_format($r['total'], 2, '.', ''),
            ], $data['rows']);

            reports_csvOut(
                "venos-log-{$data['date']}.csv",
                ['Date', 'Time', 'Items', 'Payment', 'Total'],
                $rows
            );
            break;

        case 'daily':
            $data = reports_getDaily($pdo, $offset);

            $rows = array_map(fn ($r) => [
                $r['date'],
                $r['cnt'],
                number_format($r['revenue'], 2, '.', ''),
            ], $data['rows']);

            reports_csvOut(
                "venos-daily-{$data['start']}_to_{$data['end']}.csv",
                ['Date', 'Sales', 'Revenue'],
                $rows
            );
            break;

        case 'weekly':
            $data = reports_getWeekly($pdo, $offset);

            $rows = [];
            foreach ($data['weeks'] as $week) {
                foreach ($week['days'] as $day) {
                    $rows[] = [
                        $week['label'],
                        $day['day'],
                        $day['full'],
                        $day['cnt'],
                        number_format($day['revenue'], 2, '.', ''),
                    ];
                }
            }

            reports_csvOut(
                "venos-weekly-offset{$offset}.csv",
                ['Week', 'Day', 'Date', 'Sales', 'Revenue'],
                $rows
            );
            break;

        case 'monthly':
            $data = reports_getMonthly($pdo, $offset);

            $rows = array_map(fn ($r) => [
                $r['num'],
                $r['day'],
                $r['date'],
                $r['cnt'],
                number_format($r['revenue'], 2, '.', ''),
            ], $data['rows']);

            reports_csvOut(
                'venos-monthly-' . str_replace(' ', '-', $data['label']) . '.csv',
                ['#', 'Day', 'Date', 'Sales', 'Revenue'],
                $rows
            );
            break;

        case 'yearly':
            $data = reports_getYearly($pdo, $offset);

            $rows = array_map(fn ($r) => [
                $r['month'],
                $r['cnt'],
                number_format($r['revenue'], 2, '.', ''),
            ], $data['rows']);

            reports_csvOut(
                "venos-yearly-{$data['year']}.csv",
                ['Month', 'Sales', 'Revenue'],
                $rows
            );
            break;

        default:
            http_response_code(400);
            echo 'Unknown report type.';
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Could not export report data: ' . $e->getMessage();
}