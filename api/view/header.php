<?php
$activeNav = $activeNav ?? 'sale';
?>
<!-- Top bar -->
<header class="flex items-center justify-between px-4 md:px-6 py-3 border-b border-white/5">
    <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-md bg-accent/20 text-accent flex items-center justify-center font-bold">V</div>
        <span class="font-semibold text-lg tracking-tight">VenOS</span>
    </div>

    <nav class="hidden md:flex items-center gap-1 bg-panel rounded-lg p-1">
        <a href="index.php" class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition <?= $activeNav === 'sale' ? 'bg-accent text-black' : 'text-gray-400 hover:text-gray-200' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            Sale
        </a>
        <a href="dashboard.php" class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition <?= $activeNav === 'dashboard' ? 'bg-accent text-black' : 'text-gray-400 hover:text-gray-200' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            Dashboard
        </a>
        <a href="reports.php" class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition <?= $activeNav === 'reports' ? 'bg-accent text-black' : 'text-gray-400 hover:text-gray-200' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4M9 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-4" /></svg>
            Reports
        </a>
        <a href="manage_products.php" class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition <?= $activeNav === 'products' ? 'bg-accent text-black' : 'text-gray-400 hover:text-gray-200' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
            Products
        </a>
        <a href="settings.php" class="flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition <?= $activeNav === 'settings' ? 'bg-accent text-black' : 'text-gray-400 hover:text-gray-200' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            Settings
        </a>
    </nav>

    <div class="flex items-center gap-3">
        <button id="theme-toggle-btn" class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-gray-200 transition">
            <svg id="theme-icon-sun" class="hidden w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg id="theme-icon-moon" class="hidden w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
        <button id="logout-btn" type="button" title="Log out" class="logout-trigger w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-gray-400 hover:text-gray-200 flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </button>
    </div>
</header>

<!-- Logout confirmation modal -->
<div id="logout-modal" class="hidden fixed inset-0 z-50 items-center justify-center px-4">
    <div id="logout-backdrop" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div class="relative w-full max-w-sm bg-card border border-white/10 rounded-2xl shadow-2xl p-6">
        <div class="w-11 h-11 rounded-full bg-red-500/10 text-red-400 flex items-center justify-center mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
        </div>
        <h2 class="text-base font-semibold text-gray-100 mb-1">Log out of VenOS?</h2>
        <p class="text-sm text-gray-500 mb-6">You'll need to enter the cashier PIN again to get back into the POS.</p>
        <div class="flex gap-3">
            <button id="logout-cancel" type="button" class="flex-1 px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-sm font-medium transition">Cancel</button>
            <a href="logout.php" class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium text-center transition">Log out</a>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('logout-modal');
    const backdrop = document.getElementById('logout-backdrop');
    const cancel = document.getElementById('logout-cancel');
    if (!modal) return;

    const open = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
    const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };

    document.addEventListener('click', (e) => {
        if (e.target.closest('.logout-trigger')) open();
    });
    cancel.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
})();
</script>