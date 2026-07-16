<?php
require __DIR__ . '/database.php';

$activeNav = 'reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenOS - Reports</title>

    <?php require __DIR__ . '/theme.php'; ?>
</head>
<body class="bg-surface text-gray-200 font-sans antialiased h-screen overflow-hidden">

    <?php require __DIR__ . '/pin_lock.php'; ?>

    <div class="h-full flex flex-col overflow-hidden">

        <?php require __DIR__ . '/header.php'; ?>

        <!-- Toast -->
        <div id="toast" class="hidden mx-4 md:mx-6 mt-3 px-4 py-2 rounded-lg text-sm"></div>

        <main class="flex-1 min-h-0 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6">

            <!-- Sub-tabs + export -->
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div id="report-subtabs" class="flex items-center gap-1 bg-panel rounded-lg p-1 overflow-x-auto"></div>

                <button
                    id="export-csv-btn"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-accent text-black hover:brightness-110 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                    </svg>
                    Export CSV
                </button>
            </div>

            <!-- Content header: title, period nav / date picker, big total -->
            <div class="bg-card border border-white/5 rounded-xl p-5 mb-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 id="report-title" class="text-xl font-bold text-gray-100"></h1>

                        <div class="flex items-center gap-2 mt-1">
                            <!-- Prev/next period nav (Daily/Weekly/Monthly/Yearly) -->
                            <div id="period-nav" class="hidden items-center gap-1">
                                <button id="period-prev-btn" class="w-6 h-6 rounded-md bg-white/5 hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-gray-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                </button>
                                <span id="report-subtitle" class="text-sm text-gray-500 min-w-[10rem] text-center"></span>
                                <button id="period-next-btn" class="w-6 h-6 rounded-md bg-white/5 hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-gray-200 transition disabled:opacity-30 disabled:cursor-not-allowed">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </button>
                            </div>

                            <!-- Date picker (Logs) -->
                            <div id="logs-date-nav" class="hidden items-center gap-2">
                                <input
                                    id="logs-date-input"
                                    type="date"
                                    class="bg-panel border border-white/10 rounded-lg px-3 py-1 text-sm text-gray-300 focus:outline-none focus:ring-1 focus:ring-accent"
                                >
                                <span id="report-subtitle-logs" class="text-sm text-gray-500"></span>
                            </div>
                        </div>
                    </div>

                    <div id="report-total-block" class="text-right">
                        <div id="report-total" class="text-2xl font-bold text-emerald-400"></div>
                        <div id="report-total-label" class="text-xs text-gray-500 mt-1"></div>
                    </div>
                </div>
            </div>

            <div id="report-loading" class="text-center text-gray-500 py-16">Loading report...</div>
            <div id="report-content"></div>
            <div id="logs-pagination" class="hidden items-center justify-between mt-4"></div>

        </main>

        <?php require __DIR__ . '/mobile_nav.php'; ?>
    </div>

    <script src="pin_lock.js"></script>
    <script src="reports.js"></script>
</body>
</html>