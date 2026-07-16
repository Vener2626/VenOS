<?php
$activeNav = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenOS - Dashboard</title>

    <?php require __DIR__ . '/theme.php'; ?>
</head>
<body class="bg-surface text-gray-200 font-sans antialiased h-screen overflow-hidden">

    <?php require __DIR__ . '/pin_lock.php'; ?>

    <div class="h-full flex flex-col overflow-hidden">

        <?php require __DIR__ . '/header.php'; ?>

        <main class="flex-1 min-h-0 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6">

            <div id="dashboard-warning" class="hidden mb-6 px-4 py-3 rounded-xl bg-amber-500/10 text-amber-400 text-sm">
                The <code>orders</code> table wasn't found. Run <code>schema.sql</code> against your database to enable the Dashboard.
            </div>

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-100">Dashboard</h1>
                <p class="text-sm text-gray-500"><?= date('l, F j, Y') ?></p>
            </div>

            <div id="dashboard-loading" class="text-center text-gray-500 mt-16">Loading dashboard...</div>

            <div id="dashboard-content" class="hidden">

                <!-- Summary cards -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-emerald-500/10 border border-emerald-500/10 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-emerald-400 text-xs font-medium mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            Revenue Today
                        </div>
                        <div id="stat-revenue" class="text-2xl font-bold text-gray-100">₱0.00</div>
                        <div id="stat-sales-count" class="text-xs text-gray-500 mt-1">0 sales</div>
                    </div>

                    <div class="bg-sky-500/10 border border-sky-500/10 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-sky-400 text-xs font-medium mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Transactions
                        </div>
                        <div id="stat-transactions" class="text-2xl font-bold text-gray-100">0</div>
                        <div class="text-xs text-gray-500 mt-1">completed today</div>
                    </div>

                    <div class="bg-accent/10 border border-accent/10 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-accent text-xs font-medium mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4M9 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-4"/></svg>
                            Avg. Order
                        </div>
                        <div id="stat-avg-order" class="text-2xl font-bold text-gray-100">₱0.00</div>
                        <div class="text-xs text-gray-500 mt-1">per transaction</div>
                    </div>

                    <div class="bg-violet-500/10 border border-violet-500/10 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-violet-400 text-xs font-medium mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5-1.343 1.5-3 1.5M12 8V6m0 8v2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Cash Sales
                        </div>
                        <div id="stat-cash-count" class="text-2xl font-bold text-gray-100">0</div>
                        <div id="stat-gcash-count" class="text-xs text-gray-500 mt-1">0 digital</div>
                    </div>
                </div>

                <!-- Revenue chart -->
                <div class="bg-card border border-white/5 rounded-xl p-5 mb-6">
                    <h2 class="text-sm font-semibold text-gray-300 mb-6">Revenue — Last 7 Days</h2>
                    <div id="revenue-chart"></div>
                </div>

                <!-- Product performance -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                    <div class="bg-card border border-white/5 rounded-xl overflow-hidden">
                        <div class="flex items-center gap-2 px-5 py-4 border-b border-white/5 text-sm font-semibold text-gray-300">
                            <span>🔥</span> Top Sellers
                            <span class="text-xs text-gray-500 font-normal ml-auto">Last 30 days</span>
                        </div>
                        <div id="top-products"></div>
                    </div>

                    <div class="bg-card border border-white/5 rounded-xl overflow-hidden">
                        <div class="flex items-center gap-2 px-5 py-4 border-b border-white/5 text-sm font-semibold text-gray-300">
                            <span>🐢</span> Slow Movers
                            <span class="text-xs text-gray-500 font-normal ml-auto">Last 30 days</span>
                        </div>
                        <div id="slow-products"></div>
                    </div>
                </div>

                <!-- Recent transactions -->
                <div class="bg-card border border-white/5 rounded-xl overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Recent Transactions
                        </div>
                        <span class="text-xs text-gray-500">Latest <span id="recent-count">0</span></span>
                    </div>
                    <div id="recent-transactions"></div>
                    <div id="recent-pagination" class="hidden flex items-center justify-between px-5 py-3 border-t border-white/5">
                        <button id="recent-prev" class="text-xs font-medium text-gray-400 hover:text-gray-100 disabled:opacity-30 disabled:hover:text-gray-400 disabled:cursor-not-allowed px-3 py-1.5 rounded-md bg-white/5">Prev</button>
                        <span id="recent-page-info" class="text-xs text-gray-500">Page 1</span>
                        <button id="recent-next" class="text-xs font-medium text-gray-400 hover:text-gray-100 disabled:opacity-30 disabled:hover:text-gray-400 disabled:cursor-not-allowed px-3 py-1.5 rounded-md bg-white/5">Next</button>
                    </div>
                </div>
            </div>
        </main>

        <?php require __DIR__ . '/mobile_nav.php'; ?>
    </div>

    <script src="pin_lock.js"></script>
    <script src="dashboard.js"></script>
</body>
</html>