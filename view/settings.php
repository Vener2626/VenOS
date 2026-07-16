<?php
require __DIR__ . '/auth.php';
require_role(['owner']);
require __DIR__ . '/database.php';

$activeNav = 'settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenOS - Settings</title>

    <?php require __DIR__ . '/theme.php'; ?>
</head>
<body class="bg-surface text-gray-200 font-sans antialiased h-screen overflow-hidden">

    <?php require __DIR__ . '/pin_lock.php'; ?>

    <div class="h-full flex flex-col overflow-hidden">

        <?php require __DIR__ . '/header.php'; ?>

        <!-- Toast -->
        <div id="toast" class="hidden mx-4 md:mx-6 mt-3 px-4 py-2 rounded-lg text-sm"></div>

        <main class="flex-1 min-h-0 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6">

            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-100">Settings</h1>
                <p class="text-sm text-gray-500">Manage your PINs — the relock PIN below, and the login PINs used to sign in to VenOS.</p>
            </div>

            <div class="grid gap-4 grid-cols-1 md:grid-cols-3 max-w-5xl">

            <div class="bg-card border border-white/5 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-1 text-accent">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <h2 class="font-semibold text-gray-100">Relock PIN</h2>
                </div>
                <p class="text-xs text-gray-500 mb-4">Re-asked periodically while you're already signed in, to re-confirm it's still you.</p>

                <form id="pin-form" class="space-y-4">
                    <div>
                        <label for="current-pin-input" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">CURRENT PIN</label>
                        <input id="current-pin-input" type="password" inputmode="numeric" pattern="\d{4}" maxlength="4"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.5em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>
                    <div>
                        <label for="new-pin-input" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">NEW PIN (4 digits)</label>
                        <input id="new-pin-input" type="password" inputmode="numeric" pattern="\d{4}" maxlength="4"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.5em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>
                    <div>
                        <label for="confirm-pin-input" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">CONFIRM NEW PIN</label>
                        <input id="confirm-pin-input" type="password" inputmode="numeric" pattern="\d{4}" maxlength="4"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.5em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl font-semibold transition bg-accent text-black hover:brightness-110">
                        Update PIN
                    </button>
                </form>
            </div>

            <div class="bg-card border border-white/5 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-1 text-sky-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <h2 class="font-semibold text-gray-100">Cashier Login PIN</h2>
                </div>
                <p class="text-xs text-gray-500 mb-4">The 6-digit PIN cashiers use to sign in to the Sale screen.</p>

                <form id="cashier-login-pin-form" class="space-y-4" data-role="cashier">
                    <div>
                        <label for="cashier-current-pin" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">CURRENT PIN</label>
                        <input id="cashier-current-pin" type="password" inputmode="numeric" pattern="\d{6}" maxlength="6"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.4em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>
                    <div>
                        <label for="cashier-new-pin" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">NEW PIN (6 digits)</label>
                        <input id="cashier-new-pin" type="password" inputmode="numeric" pattern="\d{6}" maxlength="6"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.4em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>
                    <div>
                        <label for="cashier-confirm-pin" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">CONFIRM NEW PIN</label>
                        <input id="cashier-confirm-pin" type="password" inputmode="numeric" pattern="\d{6}" maxlength="6"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.4em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl font-semibold transition bg-accent text-black hover:brightness-110">
                        Update Cashier PIN
                    </button>
                </form>
            </div>

            <div class="bg-card border border-white/5 rounded-xl p-5">
                <div class="flex items-center gap-2 mb-1 text-violet-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <h2 class="font-semibold text-gray-100">Owner Login PIN</h2>
                </div>
                <p class="text-xs text-gray-500 mb-4">The 6-digit PIN you use to sign in to Dashboard, Reports, and Products.</p>

                <form id="owner-login-pin-form" class="space-y-4" data-role="owner">
                    <div>
                        <label for="owner-current-pin" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">CURRENT PIN</label>
                        <input id="owner-current-pin" type="password" inputmode="numeric" pattern="\d{6}" maxlength="6"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.4em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>
                    <div>
                        <label for="owner-new-pin" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">NEW PIN (6 digits)</label>
                        <input id="owner-new-pin" type="password" inputmode="numeric" pattern="\d{6}" maxlength="6"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.4em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>
                    <div>
                        <label for="owner-confirm-pin" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">CONFIRM NEW PIN</label>
                        <input id="owner-confirm-pin" type="password" inputmode="numeric" pattern="\d{6}" maxlength="6"
                            class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm tracking-[0.4em] focus:outline-none focus:ring-1 focus:ring-accent">
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl font-semibold transition bg-accent text-black hover:brightness-110">
                        Update Owner PIN
                    </button>
                </form>
            </div>

            </div>
        </main>

        <?php require __DIR__ . '/mobile_nav.php'; ?>
    </div>

    <script src="pin_lock.js"></script>
    <script src="settings.js"></script>
</body>
</html>