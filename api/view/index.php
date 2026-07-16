<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenOS - Point of Sale</title>

    <?php require __DIR__ . '/theme.php'; ?>
</head>
<body class="bg-surface text-gray-200 font-sans antialiased h-screen overflow-hidden">

    <?php $activeNav = 'sale'; ?>
    <div class="h-full flex flex-col overflow-hidden">

        <?php require __DIR__ . '/header.php'; ?>

        <!-- Toast -->
        <div id="toast" class="hidden mx-4 md:mx-6 mt-3 px-4 py-2 rounded-lg text-sm"></div>

        <div class="flex flex-1 min-h-0 overflow-hidden">

            <!-- Product browser -->
            <main class="flex-1 min-h-0 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6">

                <div class="relative mb-4">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        id="search-input"
                        type="text"
                        placeholder="Search products..."
                        class="w-full bg-panel border border-white/5 rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-accent"
                    >
                </div>

                <div id="category-chips" class="flex items-center gap-2 mb-6 overflow-x-auto"></div>

                <div id="product-sections"></div>

                <div id="no-results" class="hidden text-center text-gray-500 mt-16">
                    No products match your search.
                </div>

                <div id="loading" class="text-center text-gray-500 mt-16">Loading products...</div>
            </main>

            <!-- Cart / Order panel -->
            <aside class="hidden md:flex w-[360px] min-h-0 flex-col border-l border-white/5 bg-panel">
                <div class="flex items-center justify-between px-5 py-4 border-b border-white/5 shrink-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h2 class="font-semibold">Order</h2>
                        <span id="cart-count-badge" class="hidden text-xs bg-accent text-black rounded-full px-2 py-0.5 font-bold"></span>
                    </div>
                    <button id="clear-cart-btn" class="hidden flex items-center gap-1 text-xs text-gray-500 hover:text-gray-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Clear
                    </button>
                </div>

                <div id="cart-items" class="flex-1 min-h-0 overflow-y-auto px-5 py-4 space-y-3"></div>

                <div id="cart-empty" class="hidden flex-1 min-h-0 flex flex-col items-center justify-center text-center text-gray-500 py-16">
                    <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <div class="font-medium text-gray-400">Cart is empty</div>
                    <div class="text-xs mt-1">Tap a product to add</div>
                </div>

                <div class="px-5 py-4 border-t border-white/5 space-y-3 shrink-0">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Discount</span>
                        <div id="discount-buttons" class="flex gap-1"></div>
                    </div>

                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between text-gray-400">
                            <span>Subtotal</span>
                            <span id="subtotal-amount">₱0.00</span>
                        </div>
                        <div id="discount-row" class="hidden flex justify-between text-gray-400">
                            <span id="discount-label">Discount</span>
                            <span id="discount-amount">-₱0.00</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-white/5">
                        <span class="font-semibold">Total</span>
                        <span id="total-amount" class="text-xl font-bold text-accent">₱0.00</span>
                    </div>

                    <button
                        id="charge-btn"
                        disabled
                        class="w-full py-3 rounded-xl font-semibold transition bg-white/5 text-gray-500 cursor-not-allowed"
                    >
                        Add items to cart
                    </button>
                </div>
            </aside>
        </div>

        <?php require __DIR__ . '/mobile_nav.php'; ?>

        <!-- Mobile charge button -->
        <div id="mobile-charge-wrapper" class="hidden md:hidden fixed bottom-14 inset-x-0 px-4">
            <button id="mobile-charge-btn" class="w-full py-3 rounded-xl font-semibold bg-accent text-black shadow-lg"></button>
        </div>
    </div>

    <!-- Checkout modal -->
    <div id="checkout-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
        <div id="checkout-backdrop" class="absolute inset-0 bg-black/60"></div>

        <div class="relative w-full max-w-sm bg-panel border border-white/10 rounded-2xl shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <h2 class="text-lg font-bold">Checkout</h2>
                <button id="checkout-close-btn" class="w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-200 hover:bg-white/5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4 space-y-4">
                <!-- Order summary -->
                <div id="checkout-summary" class="space-y-1.5 text-sm"></div>

                <!-- Payment method -->
                <div>
                    <div class="text-xs font-medium tracking-wide text-gray-500 mb-2">PAYMENT METHOD</div>
                    <div id="checkout-payment-methods" class="grid grid-cols-2 gap-2"></div>
                </div>

                <!-- Cash section -->
                <div id="checkout-cash-section" class="space-y-3">
                    <div>
                        <div class="text-xs font-medium tracking-wide text-gray-500 mb-2">AMOUNT TENDERED</div>
                        <input
                            id="checkout-amount-input"
                            type="number"
                            inputmode="decimal"
                            step="0.01"
                            min="0"
                            placeholder="0"
                            class="w-full bg-card border border-white/10 rounded-xl px-4 py-3 text-lg font-semibold focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                    </div>
                    <div id="checkout-quick-amounts" class="grid grid-cols-3 gap-2"></div>
                    <div id="checkout-change-row" class="flex justify-between items-center px-1 pt-1">
                        <span class="text-sm text-gray-400">Change</span>
                        <span id="checkout-change-amount" class="text-2xl font-bold text-accent">₱0.00</span>
                    </div>
                </div>

                <button
                    id="checkout-confirm-btn"
                    class="w-full py-3 rounded-xl font-semibold transition bg-accent text-black hover:brightness-110 flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Confirm Payment
                </button>
            </div>
        </div>
    </div>

        <script src="app.js"></script>
</body>
</html>