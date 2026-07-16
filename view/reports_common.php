<?php

/**
 * Shared query logic for the Reports tab (Logs / Daily / Weekly / Monthly / Yearly).
 * Used by both reports_data.php (JSON for the UI) and reports_export.php (CSV download),
 * so the numbers shown on screen and the numbers exported always match.
 */

function reports_getLogs(PDO $pdo, string $date, ?int $page = null, int $perPage = 20): array
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    // Day totals are always computed across the whole day, independent of the current page.
    $sumStmt = $pdo->prepare(
        'SELECT COUNT(*) cnt, COALESCE(SUM(total), 0) revenue FROM orders WHERE DATE(created_at) = ?'
    );
    $sumStmt->execute([$date]);
    $sums = $sumStmt->fetch();
    $totalCount = (int) $sums['cnt'];
    $totalRevenue = (float) $sums['revenue'];

    $perPage = max(1, $perPage);
    $totalPages = $page !== null ? max(1, (int) ceil($totalCount / $perPage)) : 1;
    $page = $page !== null ? min(max(1, $page), $totalPages) : null;

    $sql = 'SELECT id, total, payment_method, created_at FROM orders WHERE DATE(created_at) = ? ORDER BY created_at DESC';
    if ($page !== null) {
        $sql .= ' LIMIT ? OFFSET ?';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $date);
    if ($page !== null) {
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, ($page - 1) * $perPage, PDO::PARAM_INT);
    }
    $stmt->execute();
    $orders = $stmt->fetchAll();

    $itemsByOrder = [];
    $orderIds = array_column($orders, 'id');
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

    $rows = array_map(function ($o) use ($itemsByOrder) {
        $items = $itemsByOrder[$o['id']] ?? [];
        $itemsLabel = implode(', ', array_map(
            fn ($i) => $i['product_name'] . ' ×' . (int) $i['quantity'],
            $items
        ));
        return [
            'time' => date('h:i A', strtotime($o['created_at'])),
            'items' => $itemsLabel,
            'payment' => $o['payment_method'],
            'total' => (float) $o['total'],
        ];
    }, $orders);

    return [
        'date' => $date,
        'label' => date('l, F j, Y', strtotime($date)),
        'isToday' => $date === date('Y-m-d'),
        'count' => $totalCount,
        'revenue' => $totalRevenue,
        'page' => $page ?? 1,
        'perPage' => $perPage,
        'totalPages' => $totalPages,
        'rows' => $rows,
    ];
}

function reports_getDaily(PDO $pdo, int $offset): array
{
    $offset = max(0, $offset);
    $end = date('Y-m-d', strtotime("-" . ($offset * 30) . " day"));
    $start = date('Y-m-d', strtotime('-29 day', strtotime($end)));

    $stmt = $pdo->prepare(
        'SELECT DATE(created_at) d, COUNT(*) cnt, SUM(total) revenue FROM orders
         WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at)'
    );
    $stmt->execute([$start, $end]);

    $byDate = [];
    foreach ($stmt->fetchAll() as $r) {
        $byDate[$r['d']] = ['cnt' => (int) $r['cnt'], 'revenue' => (float) $r['revenue']];
    }

    $today = date('Y-m-d');
    $rows = [];
    for ($ts = strtotime($end); $ts >= strtotime($start); $ts -= 86400) {
        $ds = date('Y-m-d', $ts);
        $rows[] = [
            'date' => $ds,
            'label' => date('M j', $ts),
            'isToday' => $ds === $today,
            'cnt' => $byDate[$ds]['cnt'] ?? 0,
            'revenue' => $byDate[$ds]['revenue'] ?? 0.0,
        ];
    }

    return [
        'start' => $start,
        'end' => $end,
        'label' => $offset === 0
            ? 'Last 30 days'
            : date('M j', strtotime($start)) . ' – ' . date('M j, Y', strtotime($end)),
        'offset' => $offset,
        'canGoNext' => $offset > 0,
        'revenue' => array_sum(array_column($rows, 'revenue')),
        'count' => array_sum(array_column($rows, 'cnt')),
        'rows' => $rows,
    ];
}

function reports_getWeekly(PDO $pdo, int $offset): array
{
    $offset = max(0, $offset);
    $today = strtotime('today');
    $dow = (int) date('N', $today); // 1 (Mon) .. 7 (Sun)
    $mondayThisWeek = strtotime('-' . ($dow - 1) . ' days', $today);
    $todayStr = date('Y-m-d');

    $weeks = [];
    for ($w = 0; $w < 4; $w++) {
        $weekIndex = $offset * 4 + $w;
        $monday = strtotime('-' . ($weekIndex * 7) . ' days', $mondayThisWeek);
        $friday = strtotime('+4 days', $monday);
        $mondayStr = date('Y-m-d', $monday);
        $fridayStr = date('Y-m-d', $friday);

        $stmt = $pdo->prepare(
            'SELECT DATE(created_at) d, COUNT(*) cnt, SUM(total) revenue FROM orders
             WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at)'
        );
        $stmt->execute([$mondayStr, $fridayStr]);

        $byDate = [];
        foreach ($stmt->fetchAll() as $r) {
            $byDate[$r['d']] = ['cnt' => (int) $r['cnt'], 'revenue' => (float) $r['revenue']];
        }

        $days = [];
        $maxRevenue = 0.0;
        for ($i = 0; $i < 5; $i++) {
            $ts = strtotime("+{$i} days", $monday);
            $ds = date('Y-m-d', $ts);
            $isFuture = $ds > $todayStr;
            $rev = $byDate[$ds]['revenue'] ?? 0.0;
            if ($rev > $maxRevenue) {
                $maxRevenue = $rev;
            }
            $days[] = [
                'day' => date('D', $ts),
                'date' => date('M j', $ts),
                'full' => $ds,
                'isToday' => $ds === $todayStr,
                'isFuture' => $isFuture,
                'cnt' => $byDate[$ds]['cnt'] ?? 0,
                'revenue' => $rev,
            ];
        }
        $maxRevenue = $maxRevenue > 0 ? $maxRevenue : 1;
        foreach ($days as &$day) {
            $day['pct'] = $day['isFuture'] ? 0 : max(4, (int) round(($day['revenue'] / $maxRevenue) * 100));
        }
        unset($day);

        $weeks[] = [
            'label' => 'Week of ' . date('M j', $monday) . ' – ' . date('M j, Y', $friday),
            'total' => array_sum(array_column($days, 'revenue')),
            'days' => $days,
        ];
    }

    return [
        'label' => 'Mon – Fri, last 4 weeks',
        'offset' => $offset,
        'canGoNext' => $offset > 0,
        'weeks' => $weeks,
        'revenue' => array_sum(array_column($weeks, 'total')),
    ];
}

function reports_getMonthly(PDO $pdo, int $offset): array
{
    $offset = max(0, $offset);
    $dt = new DateTime(date('Y-m-01'));
    $dt->modify('-' . $offset . ' month');
    $monthStart = $dt->format('Y-m-d');
    $daysInMonth = (int) $dt->format('t');
    $monthLabel = $dt->format('F Y');

    $stmt = $pdo->prepare(
        'SELECT DATE(created_at) d, COUNT(*) cnt, SUM(total) revenue FROM orders
         WHERE created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 MONTH) GROUP BY DATE(created_at)'
    );
    $stmt->execute([$monthStart, $monthStart]);

    $byDate = [];
    foreach ($stmt->fetchAll() as $r) {
        $byDate[$r['d']] = ['cnt' => (int) $r['cnt'], 'revenue' => (float) $r['revenue']];
    }

    $todayStr = date('Y-m-d');
    $rows = [];
    for ($day = 1; $day <= $daysInMonth; $day++) {
        $ds = $dt->format('Y-m-') . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
        $ts = strtotime($ds);
        $rows[] = [
            'num' => $day,
            'day' => date('D', $ts),
            'date' => date('M j', $ts),
            'isToday' => $ds === $todayStr,
            'isFuture' => $ds > $todayStr,
            'cnt' => $byDate[$ds]['cnt'] ?? 0,
            'revenue' => $byDate[$ds]['revenue'] ?? 0.0,
        ];
    }

    return [
        'label' => $monthLabel,
        'offset' => $offset,
        'canGoNext' => $offset > 0,
        'revenue' => array_sum(array_column($rows, 'revenue')),
        'count' => array_sum(array_column($rows, 'cnt')),
        'rows' => $rows,
    ];
}

function reports_getYearly(PDO $pdo, int $offset): array
{
    $offset = max(0, $offset);
    $year = (int) date('Y') - $offset;

    $stmt = $pdo->prepare(
        'SELECT MONTH(created_at) m, COUNT(*) cnt, SUM(total) revenue FROM orders
         WHERE YEAR(created_at) = ? GROUP BY MONTH(created_at)'
    );
    $stmt->execute([$year]);

    $byMonth = [];
    foreach ($stmt->fetchAll() as $r) {
        $byMonth[(int) $r['m']] = ['cnt' => (int) $r['cnt'], 'revenue' => (float) $r['revenue']];
    }

    $currentYear = (int) date('Y');
    $currentMonth = (int) date('n');

    $maxRevenue = 0.0;
    for ($m = 1; $m <= 12; $m++) {
        $rev = $byMonth[$m]['revenue'] ?? 0.0;
        if ($rev > $maxRevenue) {
            $maxRevenue = $rev;
        }
    }
    $maxRevenue = $maxRevenue > 0 ? $maxRevenue : 1;

    $rows = [];
    for ($m = 1; $m <= 12; $m++) {
        $isFuture = ($year > $currentYear) || ($year === $currentYear && $m > $currentMonth);
        $rev = $byMonth[$m]['revenue'] ?? 0.0;
        $rows[] = [
            'month' => date('M', mktime(0, 0, 0, $m, 1)),
            'isNow' => $year === $currentYear && $m === $currentMonth,
            'isFuture' => $isFuture,
            'cnt' => $byMonth[$m]['cnt'] ?? 0,
            'revenue' => $rev,
            'pct' => $isFuture ? 0 : max(4, (int) round(($rev / $maxRevenue) * 100)),
        ];
    }

    return [
        'year' => $year,
        'label' => "{$year} — January to December",
        'offset' => $offset,
        'canGoNext' => $offset > 0,
        'revenue' => array_sum(array_column($rows, 'revenue')),
        'count' => array_sum(array_column($rows, 'cnt')),
        'rows' => $rows,
    ];
}