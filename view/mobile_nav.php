<?php $activeNav = $activeNav ?? 'sale'; ?>
<!-- Mobile bottom nav -->
<nav class="md:hidden fixed bottom-0 inset-x-0 bg-panel border-t border-white/5 flex justify-around py-2 z-40">
    <a href="index.php" class="flex flex-col items-center text-xs gap-1 <?= $activeNav === 'sale' ? 'text-accent' : 'text-gray-500' ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        Sale
    </a>
    <a href="dashboard.php" class="flex flex-col items-center text-xs gap-1 <?= $activeNav === 'dashboard' ? 'text-accent' : 'text-gray-500' ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
        Stats
    </a>
    <a href="reports.php" class="flex flex-col items-center text-xs gap-1 <?= $activeNav === 'reports' ? 'text-accent' : 'text-gray-500' ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4M9 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-4" /></svg>
        Reports
    </a>
    <a href="manage_products.php" class="flex flex-col items-center text-xs gap-1 <?= $activeNav === 'products' ? 'text-accent' : 'text-gray-500' ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
        Items
    </a>
    <a href="settings.php" class="flex flex-col items-center text-xs gap-1 <?= $activeNav === 'settings' ? 'text-accent' : 'text-gray-500' ?>">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
        Settings
    </a>
    <button type="button" class="logout-trigger flex flex-col items-center text-xs gap-1 text-gray-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
        Logout
    </button>
</nav>