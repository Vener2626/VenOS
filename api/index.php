<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenOS — Point of sale for busy counters</title>
    <?php require __DIR__ . '/view/theme.php'; ?>
</head>
<body class="bg-surface text-gray-200 font-sans antialiased">

    <!-- Top bar -->
    <header class="flex items-center justify-between px-6 py-5 max-w-6xl mx-auto">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-md bg-accent/20 text-accent flex items-center justify-center font-bold">V</div>
            <span class="font-semibold text-lg tracking-tight">VenOS</span>
        </div>
        <a href="login.php" class="px-4 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-sm font-medium text-gray-200 transition">Log in</a>
    </header>

    <!-- Hero -->
    <section class="max-w-6xl mx-auto px-6 pt-10 pb-20 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-accent/10 text-accent text-xs font-medium mb-5">Built for one-counter shops</span>
            <h1 class="text-4xl md:text-5xl font-bold text-orange-400 leading-tight mb-5">
                Run the counter,<br>not the paperwork.
            </h1>
            <p class="text-gray-400 text-base md:text-lg mb-8 max-w-md">
                VenOS rings up sales, tracks cash and GCash, and shows you what's selling — all from one screen your cashier can learn in five minutes.
            </p>
            <div class="flex flex-wrap items-center gap-3">
                <a href="login.php" class="px-6 py-3 rounded-xl bg-accent text-black text-sm font-semibold hover:brightness-95 transition">Get Started</a>
                <a href="#features" class="px-6 py-3 rounded-xl bg-white/5 hover:bg-white/10 text-sm font-medium text-gray-200 transition">See what it does</a>
            </div>
        </div>

        <!-- Signature element: fanned deck of product cards, echoing the real Sale screen -->
        <div class="relative h-80 flex items-center justify-center select-none">
            <div class="absolute w-40 bg-card border border-white/10 rounded-2xl p-4 shadow-2xl -rotate-6 -translate-x-16">
                <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-lg mb-3">🥐</div>
                <div class="text-sm font-medium text-gray-100">Croissant</div>
                <div class="text-xs text-accent font-semibold mt-1">₱15.00</div>
            </div>
            <div class="absolute w-40 bg-card border border-white/10 rounded-2xl p-4 shadow-2xl rotate-3 translate-x-14 -translate-y-4">
                <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-lg mb-3">☕</div>
                <div class="text-sm font-medium text-gray-100">Cappuccino</div>
                <div class="text-xs text-accent font-semibold mt-1">₱55.00</div>
            </div>
            <div class="relative w-44 bg-card border border-white/10 rounded-2xl p-5 shadow-2xl translate-y-10">
                <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-xl mb-3">🍰</div>
                <div class="text-sm font-semibold text-gray-100">Cheesecake</div>
                <div class="text-xs text-gray-500 mb-2">Best seller</div>
                <div class="text-sm text-accent font-bold">₱25.00</div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="max-w-6xl mx-auto px-6 pb-20">
        <h2 class="text-xl font-semibold text-gray-100 mb-8 text-center">Everything the counter needs, nothing it doesn't</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">

            <div class="bg-card border border-white/5 rounded-2xl p-5">
                <div class="w-9 h-9 rounded-lg bg-accent/10 text-accent flex items-center justify-center mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-100 mb-1">Fast checkout</h3>
                <p class="text-sm text-gray-500">Tap products into a cart, apply a discount, and take cash or GCash in seconds.</p>
            </div>

            <div class="bg-card border border-white/5 rounded-2xl p-5">
                <div class="w-9 h-9 rounded-lg bg-sky-500/10 text-sky-400 flex items-center justify-center mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-100 mb-1">Live dashboard</h3>
                <p class="text-sm text-gray-500">Today's revenue, transactions, and average order size, updated as sales come in.</p>
            </div>

            <div class="bg-card border border-white/5 rounded-2xl p-5">
                <div class="w-9 h-9 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4M9 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-4" /></svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-100 mb-1">Reports that matter</h3>
                <p class="text-sm text-gray-500">See your best and slowest sellers over the last 30 days without digging through spreadsheets.</p>
            </div>

            <div class="bg-card border border-white/5 rounded-2xl p-5">
                <div class="w-9 h-9 rounded-lg bg-violet-500/10 text-violet-400 flex items-center justify-center mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-100 mb-1">Products, organized</h3>
                <p class="text-sm text-gray-500">Group items by category, mark best sellers, and keep prices up to date in one place.</p>
            </div>

            <div class="bg-card border border-white/5 rounded-2xl p-5">
                <div class="w-9 h-9 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-100 mb-1">PIN-protected</h3>
                <p class="text-sm text-gray-500">Only your cashier gets in — a PIN gate keeps the register out of the wrong hands.</p>
            </div>

            <div class="bg-card border border-white/5 rounded-2xl p-5">
                <div class="w-9 h-9 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5-1.343 1.5-3 1.5M12 8V6m0 8v2M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-100 mb-1">Cash & GCash, tracked</h3>
                <p class="text-sm text-gray-500">Every sale is tagged by payment method so your totals always reconcile.</p>
            </div>

        </div>
    </section>

    <!-- CTA -->
    <section class="max-w-3xl mx-auto px-6 pb-24 text-center">
        <div class="bg-card border border-white/5 rounded-2xl p-10">
            <h2 class="text-xl font-semibold text-gray-100 mb-2">Ready to open the register?</h2>
            <p class="text-sm text-gray-500 mb-6">Log in with your cashier PIN and start selling.</p>
            <a href="login.php" class="inline-block px-6 py-3 rounded-xl bg-accent text-black text-sm font-semibold hover:brightness-95 transition">Get Started</a>
        </div>
    </section>

    <footer class="max-w-6xl mx-auto px-6 pb-10 text-center text-xs text-gray-600">
        <p>© <?= date('Y') ?> VenOS. Built for the counter.</p>
        <p class="mt-2">Interested in collaborating? <a href="mailto:christianveneracion.basc@gmail.com" class="text-accent hover:underline">christianveneracion.basc@gmail.com</a></p>
    </footer>

</body>
</html>