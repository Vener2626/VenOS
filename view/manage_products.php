<?php
require __DIR__ . '/database.php';

$activeNav = 'products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenOS - Products</title>

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
                <h1 class="text-2xl font-bold text-gray-100">Products</h1>
                <p class="text-sm text-gray-500">Add, edit, or remove items from your catalog.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[360px_1fr] gap-6 items-start">

                <!-- Add / Edit form -->
                <div class="bg-card border border-white/5 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-5 text-accent">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <h2 id="form-title" class="font-semibold text-gray-100">Add New Product</h2>
                    </div>

                    <form id="product-form" class="space-y-4">
                        <input type="hidden" id="product-id-input" value="">

                        <div>
                            <label class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">ICON</label>
                            <div id="icon-grid" class="grid grid-cols-6 gap-2 max-h-28 overflow-y-auto pr-1"></div>
                            <div class="mt-3 w-12 h-12 rounded-lg bg-panel border border-white/5 flex items-center justify-center text-2xl" id="icon-preview">🍽️</div>
                        </div>

                        <div>
                            <label for="product-name-input" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">PRODUCT NAME *</label>
                            <input
                                id="product-name-input"
                                type="text"
                                placeholder="e.g. Iced Americano"
                                class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-accent"
                            >
                        </div>

                        <div>
                            <label for="product-price-input" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">PRICE *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">₱</span>
                                <input
                                    id="product-price-input"
                                    type="number"
                                    inputmode="decimal"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full bg-panel border border-white/5 rounded-xl pl-8 pr-4 py-2.5 text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-accent"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="product-category-select" class="text-xs font-medium tracking-wide text-gray-500 mb-2 block">CATEGORY</label>
                            <select
                                id="product-category-select"
                                class="w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-accent"
                            ></select>
                            <input
                                id="product-category-new-input"
                                type="text"
                                placeholder="New category name"
                                class="hidden mt-2 w-full bg-panel border border-white/5 rounded-xl px-4 py-2.5 text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-accent"
                            >
                        </div>

                        <button
                            type="submit"
                            id="product-submit-btn"
                            class="w-full py-3 rounded-xl font-semibold transition bg-accent text-black hover:brightness-110 flex items-center justify-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span id="product-submit-label">Add Product</span>
                        </button>

                        <button
                            type="button"
                            id="product-cancel-btn"
                            class="hidden w-full py-2 rounded-xl text-sm text-gray-400 hover:text-gray-200 transition"
                        >Cancel editing</button>
                    </form>
                </div>

                <!-- Product list -->
                <div class="min-w-0">
                    <div class="relative mb-4">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            id="product-search-input"
                            type="text"
                            placeholder="Search products..."
                            class="w-full bg-panel border border-white/5 rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                    </div>

                    <div id="product-groups" class="space-y-6"></div>

                    <div id="product-empty" class="hidden text-center text-gray-500 mt-16">
                        No products match your search.
                    </div>

                    <div id="product-loading" class="text-center text-gray-500 mt-16">Loading products...</div>
                </div>
            </div>
        </main>

        <?php require __DIR__ . '/mobile_nav.php'; ?>
    </div>

    <!-- Delete confirmation modal -->
    <div id="delete-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
        <div id="delete-backdrop" class="absolute inset-0 bg-black/60"></div>

        <div class="relative w-full max-w-sm bg-panel border border-white/10 rounded-2xl shadow-2xl">
            <div class="px-5 pt-5 pb-4 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-red-500/10 flex items-center justify-center text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-100">Delete product?</h2>
                <p id="delete-modal-text" class="text-sm text-gray-500 mt-1">This can't be undone.</p>
            </div>

            <div class="px-5 pb-5 flex gap-2">
                <button id="delete-cancel-btn" class="flex-1 py-2.5 rounded-xl text-sm font-medium bg-white/5 text-gray-300 hover:bg-white/10 transition">Cancel</button>
                <button id="delete-confirm-btn" class="flex-1 py-2.5 rounded-xl text-sm font-semibold bg-red-500 text-white hover:brightness-110 transition">Delete</button>
            </div>
        </div>
    </div>

    <script src="pin_lock.js"></script>
    <script src="products_admin.js"></script>
</body>
</html>