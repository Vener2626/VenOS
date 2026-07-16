// ---- State ----
const state = {
    products: [],
    categories: [],
    activeCategory: 'All',
    search: '',
    cart: {}, // { productId: qty }
    discount: 0, // 0, 5, 10, 15, 20
    bestSellerIds: [], // product ids in the current top-3 (last 30 days)
};

const checkoutState = {
    open: false,
    method: 'cash', // 'cash' | 'gcash'
    amountTendered: '',
};

const categoryColors = {
    Drinks: 'bg-sky-400',
    Bakery: 'bg-amber-500',
    Food: 'bg-emerald-400',
    Snacks: 'bg-violet-400',
    Seafood: 'bg-cyan-400',
};

function categoryColor(cat) {
    return categoryColors[cat] || 'bg-gray-400';
}

// ---- Best-seller badge (small red "starburst" stamp, built with SVG) ----
function buildStarPoints(cx, cy, outerR, innerR, spikes) {
    const points = [];
    const step = Math.PI / spikes;
    let rot = -Math.PI / 2;
    for (let i = 0; i < spikes; i++) {
        points.push(`${(cx + Math.cos(rot) * outerR).toFixed(1)},${(cy + Math.sin(rot) * outerR).toFixed(1)}`);
        rot += step;
        points.push(`${(cx + Math.cos(rot) * innerR).toFixed(1)},${(cy + Math.sin(rot) * innerR).toFixed(1)}`);
        rot += step;
    }
    return points.join(' ');
}

// Built once and reused on every qualifying card, instead of regenerating
// the star shape on every render.
const BEST_SELLER_BADGE = `
    <svg viewBox="0 0 64 64" class="w-11 h-11 drop-shadow">
        <polygon points="${buildStarPoints(32, 32, 31, 22, 14)}" fill="#dc2626" stroke="#7f1d1d" stroke-width="0.5"/>
        <text x="32" y="29" text-anchor="middle" fill="white" font-size="10" font-weight="800" font-family="Arial, sans-serif" letter-spacing="0.2">BEST</text>
        <text x="32" y="40" text-anchor="middle" fill="white" font-size="9" font-weight="800" font-family="Arial, sans-serif" letter-spacing="0.2">SELLER</text>
    </svg>
`;

// ---- Data loading ----
async function loadProducts() {
    document.getElementById('loading').classList.remove('hidden');

    try {
        const [productsRes, bestSellersRes] = await Promise.all([
            fetch('products.php'),
            fetch('best_sellers.php'),
        ]);
        const data = await productsRes.json();

        state.products = data;
        state.categories = [...new Set(data.map(p => p.category))];

        // Best-seller badges are a nice-to-have — if this endpoint fails
        // for any reason, the POS should still work fine without them.
        try {
            const bestSellersData = await bestSellersRes.json();
            state.bestSellerIds = bestSellersData.bestSellerIds || [];
        } catch (e) {
            state.bestSellerIds = [];
        }

        renderCategoryChips();
        renderDiscountButtons();
        renderAll();
    } catch (e) {
        document.getElementById('loading').textContent =
            'Could not load products. Check your database connection in config/database.php.';
        return;
    }

    document.getElementById('loading').classList.add('hidden');
}

// ---- Derived data ----
function filteredProducts() {
    return state.products.filter(p => {
        const matchesCategory = state.activeCategory === 'All' || p.category === state.activeCategory;
        const matchesSearch = state.search === '' || p.name.toLowerCase().includes(state.search.toLowerCase());
        return matchesCategory && matchesSearch;
    });
}

function groupedFiltered() {
    const groups = {};
    filteredProducts().forEach(p => {
        if (!groups[p.category]) groups[p.category] = [];
        groups[p.category].push(p);
    });
    return Object.keys(groups).map(category => ({ category, items: groups[category] }));
}

function cartItems() {
    return Object.keys(state.cart).map(id => {
        const product = state.products.find(p => p.id == id);
        const qty = state.cart[id];
        return product ? { product, qty, lineTotal: product.price * qty } : null;
    }).filter(Boolean);
}

function cartCount() {
    return Object.values(state.cart).reduce((sum, qty) => sum + qty, 0);
}

function subtotal() {
    return cartItems().reduce((sum, item) => sum + item.lineTotal, 0);
}

function discountAmount() {
    return subtotal() * (state.discount / 100);
}

function total() {
    return subtotal() - discountAmount();
}

// Suggest sensible peso bill amounts at/above the total (e.g. 20/50/100/500/1000)
function quickCashAmounts() {
    const t = total();
    const denominations = [20, 50, 100, 200, 500, 1000];
    const suggestions = new Set();

    // Exact total, rounded up to the nearest whole peso
    suggestions.add(Math.max(1, Math.ceil(t)));

    denominations.forEach(d => {
        if (d >= t) suggestions.add(d);
    });

    return [...suggestions].sort((a, b) => a - b).slice(0, 3);
}

// ---- Cart actions ----
function addToCart(id) {
    state.cart[id] = (state.cart[id] || 0) + 1;
    renderAll();
}

function increment(id) {
    state.cart[id] = (state.cart[id] || 0) + 1;
    renderAll();
}

function decrement(id) {
    if (!state.cart[id]) return;
    state.cart[id]--;
    if (state.cart[id] <= 0) delete state.cart[id];
    renderAll();
}

function removeFromCart(id) {
    delete state.cart[id];
    renderAll();
}

function clearCart() {
    state.cart = {};
    state.discount = 0;
    renderAll();
}

function amountTenderedValue() {
    const n = parseFloat(checkoutState.amountTendered);
    return isNaN(n) ? 0 : n;
}

function changeDue() {
    return Math.max(0, amountTenderedValue() - total());
}

async function submitPayment() {
    if (cartCount() === 0) return;

    const payload = {
        items: Object.keys(state.cart).map(id => ({ id: Number(id), qty: state.cart[id] })),
        discount: state.discount,
        paymentMethod: checkoutState.method,
        amountTendered: checkoutState.method === 'cash' ? amountTenderedValue() : null,
    };

    try {
        const res = await fetch('checkout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (!res.ok) {
            showToast(data.error || 'Could not complete this sale.', 'danger');
            return;
        }

        showToast(data.message || 'Sale completed', 'success');
        closeCheckoutModal();
        clearCart();
    } catch (e) {
        showToast('Something went wrong charging this order.', 'danger');
    }
}

function showToast(message, type = 'success') {
    const styles = {
        success: 'bg-emerald-500/10 text-emerald-400',
        danger: 'bg-red-500/10 text-red-400',
    };

    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `mx-4 md:mx-6 mt-3 px-4 py-2 rounded-lg text-sm ${styles[type] || styles.success}`;

    clearTimeout(showToast._timer);
    showToast._timer = setTimeout(() => toast.classList.add('hidden'), 4000);
}

// ---- Rendering ----
function renderCategoryChips() {
    const container = document.getElementById('category-chips');
    const allCats = ['All', ...state.categories];

    container.innerHTML = allCats.map(cat => `
        <button
            data-category="${cat}"
            class="category-chip px-4 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition ${
                state.activeCategory === cat ? 'bg-accent text-black' : 'bg-panel text-gray-400 hover:text-gray-200'
            }"
        >${cat}</button>
    `).join('');

    container.querySelectorAll('.category-chip').forEach(btn => {
        btn.addEventListener('click', () => {
            state.activeCategory = btn.dataset.category;
            renderCategoryChips();
            renderProducts();
        });
    });
}

function renderDiscountButtons() {
    const container = document.getElementById('discount-buttons');
    const options = [[0, 'None'], [5, '5%'], [10, '10%'], [15, '15%'], [20, '20%']];

    container.innerHTML = options.map(([value, label]) => `
        <button
            data-discount="${value}"
            class="discount-btn px-2 py-1 rounded-md text-xs font-medium transition ${
                state.discount === value ? 'bg-accent text-black' : 'bg-white/5 text-gray-400 hover:text-gray-200'
            }"
        >${label}</button>
    `).join('');

    container.querySelectorAll('.discount-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            state.discount = Number(btn.dataset.discount);
            renderDiscountButtons();
            renderTotals();
        });
    });
}

function renderProducts() {
    const container = document.getElementById('product-sections');
    const groups = groupedFiltered();

    document.getElementById('no-results').classList.toggle('hidden', groups.length > 0);

    container.innerHTML = groups.map(group => `
        <section class="mb-8">
            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-white/5">
                <span class="w-2 h-2 rounded-full ${categoryColor(group.category)}"></span>
                <h2 class="text-xs font-bold tracking-widest uppercase text-gray-300">${group.category}</h2>
                <span class="ml-auto text-xs text-gray-500">${group.items.length}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                ${group.items.map(product => {
                    const qty = state.cart[product.id] || 0;
                    const isBestSeller = state.bestSellerIds.some(id => Number(id) === Number(product.id));
                    return `
                        <button
                            data-product-id="${product.id}"
                            class="product-card relative text-left bg-card hover:bg-cardhover border rounded-xl p-4 transition ${
                                qty > 0 ? 'border-accent/60' : 'border-white/5'
                            }"
                        >
                            ${qty > 0 ? `<span class="absolute top-2 left-2 w-5 h-5 rounded-full bg-accent text-black text-[11px] font-bold flex items-center justify-center">${qty}</span>` : ''}
                            ${isBestSeller ? `<span class="absolute -top-2 -right-2 pointer-events-none">${BEST_SELLER_BADGE}</span>` : ''}
                            <div class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center text-lg mb-3">${product.icon || ''}</div>
                            <div class="text-sm font-medium text-gray-100">${product.name}</div>
                            <div class="text-sm font-semibold text-sky-400 mt-0.5">₱${Number(product.price).toFixed(2)}</div>
                        </button>
                    `;
                }).join('')}
            </div>
        </section>
    `).join('');

    container.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', () => addToCart(Number(card.dataset.productId)));
    });
}

function renderCart() {
    const items = cartItems();
    const container = document.getElementById('cart-items');
    const emptyState = document.getElementById('cart-empty');
    const badge = document.getElementById('cart-count-badge');
    const clearBtn = document.getElementById('clear-cart-btn');

    container.classList.toggle('hidden', items.length === 0);
    emptyState.classList.toggle('hidden', items.length > 0);
    badge.classList.toggle('hidden', items.length === 0);
    clearBtn.classList.toggle('hidden', items.length === 0);

    if (items.length > 0) {
        badge.textContent = cartCount();
    }

    container.innerHTML = items.map(item => `
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-sm shrink-0">${item.product.icon || ''}</div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium truncate">${item.product.name}</div>
                <div class="text-xs text-gray-500">₱${Number(item.product.price).toFixed(2)} each</div>
            </div>
            <div class="flex items-center gap-2">
                <button data-action="decrement" data-id="${item.product.id}" class="w-6 h-6 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-sm">-</button>
                <span class="w-4 text-center text-sm">${item.qty}</span>
                <button data-action="increment" data-id="${item.product.id}" class="w-6 h-6 rounded-full bg-white/5 hover:bg-white/10 flex items-center justify-center text-sm">+</button>
            </div>
            <div class="w-14 text-right text-sm font-semibold">₱${item.lineTotal.toFixed(2)}</div>
            <button data-action="remove" data-id="${item.product.id}" title="Remove item" class="w-6 h-6 rounded-full flex items-center justify-center text-gray-500 hover:text-red-400 hover:bg-red-500/10 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    `).join('');

    container.querySelectorAll('[data-action="increment"]').forEach(btn => {
        btn.addEventListener('click', () => increment(Number(btn.dataset.id)));
    });
    container.querySelectorAll('[data-action="decrement"]').forEach(btn => {
        btn.addEventListener('click', () => decrement(Number(btn.dataset.id)));
    });
    container.querySelectorAll('[data-action="remove"]').forEach(btn => {
        btn.addEventListener('click', () => removeFromCart(Number(btn.dataset.id)));
    });
}

function renderTotals() {
    const count = cartCount();

    document.getElementById('subtotal-amount').textContent = `₱${subtotal().toFixed(2)}`;
    document.getElementById('total-amount').textContent = `₱${total().toFixed(2)}`;

    const discountRow = document.getElementById('discount-row');
    if (state.discount > 0) {
        discountRow.classList.remove('hidden');
        document.getElementById('discount-label').textContent = `Discount (${state.discount}%)`;
        document.getElementById('discount-amount').textContent = `-₱${discountAmount().toFixed(2)}`;
    } else {
        discountRow.classList.add('hidden');
    }

    const chargeBtn = document.getElementById('charge-btn');
    const mobileWrapper = document.getElementById('mobile-charge-wrapper');
    const mobileBtn = document.getElementById('mobile-charge-btn');

    if (count > 0) {
        chargeBtn.disabled = false;
        chargeBtn.textContent = `Charge ₱${total().toFixed(2)}`;
        chargeBtn.className = 'w-full py-3 rounded-xl font-semibold transition bg-accent text-black hover:brightness-110';
        mobileWrapper.classList.remove('hidden');
        mobileBtn.textContent = `Charge ₱${total().toFixed(2)}`;
    } else {
        chargeBtn.disabled = true;
        chargeBtn.textContent = 'Add items to cart';
        chargeBtn.className = 'w-full py-3 rounded-xl font-semibold transition bg-white/5 text-gray-500 cursor-not-allowed';
        mobileWrapper.classList.add('hidden');
    }
}

// ---- Checkout modal ----
function openCheckoutModal() {
    if (cartCount() === 0) return;
    checkoutState.open = true;
    checkoutState.method = 'cash';
    checkoutState.amountTendered = '';
    renderCheckoutModal();
    document.getElementById('checkout-modal').classList.remove('hidden');
    document.getElementById('checkout-modal').classList.add('flex');
}

function closeCheckoutModal() {
    checkoutState.open = false;
    document.getElementById('checkout-modal').classList.add('hidden');
    document.getElementById('checkout-modal').classList.remove('flex');
}

function renderCheckoutSummary() {
    const items = cartItems();
    const summary = document.getElementById('checkout-summary');

    const lines = items.map(item => `
        <div class="flex items-center justify-between text-gray-400 gap-2">
            <span class="truncate">${item.product.name} ×${item.qty}</span>
            <div class="flex items-center gap-2 shrink-0">
                <span>₱${item.lineTotal.toFixed(2)}</span>
                <button data-action="remove-checkout-item" data-id="${item.product.id}" title="Remove item" class="w-5 h-5 rounded-full flex items-center justify-center text-gray-500 hover:text-red-400 hover:bg-red-500/10">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    `).join('');

    const discountLine = state.discount > 0 ? `
        <div class="flex justify-between text-gray-400">
            <span>Discount (${state.discount}%)</span>
            <span>-₱${discountAmount().toFixed(2)}</span>
        </div>
    ` : '';

    summary.innerHTML = `
        ${lines}
        ${discountLine}
        <div class="flex justify-between items-center pt-2 mt-1 border-t border-white/10 text-base font-bold">
            <span>Total</span>
            <span class="text-accent">₱${total().toFixed(2)}</span>
        </div>
    `;

    summary.querySelectorAll('[data-action="remove-checkout-item"]').forEach(btn => {
        btn.addEventListener('click', () => {
            removeFromCart(Number(btn.dataset.id));
            if (cartCount() === 0) {
                closeCheckoutModal();
            } else {
                renderCheckoutModal();
            }
        });
    });
}

function renderPaymentMethods() {
    const container = document.getElementById('checkout-payment-methods');
    const methods = [
        { id: 'cash', label: 'Cash', icon: '💵' },
        { id: 'gcash', label: 'GCash', icon: '📱' },
    ];

    container.innerHTML = methods.map(m => `
        <button
            data-method="${m.id}"
            class="payment-method-btn flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold transition ${
                checkoutState.method === m.id ? 'bg-accent text-black' : 'bg-white/5 text-gray-300 hover:bg-white/10'
            }"
        ><span>${m.icon}</span>${m.label}</button>
    `).join('');

    container.querySelectorAll('.payment-method-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            checkoutState.method = btn.dataset.method;
            renderCheckoutModal();
        });
    });
}

function renderQuickAmounts() {
    const container = document.getElementById('checkout-quick-amounts');
    const amounts = quickCashAmounts();

    container.innerHTML = amounts.map(amt => `
        <button
            data-amount="${amt}"
            class="quick-amount-btn py-2 rounded-lg text-sm font-medium bg-white/5 text-gray-300 hover:bg-white/10 transition"
        >₱${amt}</button>
    `).join('');

    container.querySelectorAll('.quick-amount-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            checkoutState.amountTendered = btn.dataset.amount;
            document.getElementById('checkout-amount-input').value = btn.dataset.amount;
            renderChangeAndConfirmState();
        });
    });
}

function renderChangeAndConfirmState() {
    document.getElementById('checkout-change-amount').textContent = `₱${changeDue().toFixed(2)}`;

    const confirmBtn = document.getElementById('checkout-confirm-btn');
    const cashInsufficient = checkoutState.method === 'cash' && amountTenderedValue() < total();

    confirmBtn.disabled = cashInsufficient;
    confirmBtn.classList.toggle('opacity-40', cashInsufficient);
    confirmBtn.classList.toggle('cursor-not-allowed', cashInsufficient);
}

function renderCheckoutModal() {
    renderCheckoutSummary();
    renderPaymentMethods();

    const cashSection = document.getElementById('checkout-cash-section');
    if (checkoutState.method === 'cash') {
        cashSection.classList.remove('hidden');
        renderQuickAmounts();
    } else {
        cashSection.classList.add('hidden');
    }

    renderChangeAndConfirmState();
}

function renderAll() {
    renderProducts();
    renderCart();
    renderTotals();
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();

    document.getElementById('search-input').addEventListener('input', (e) => {
        state.search = e.target.value;
        renderProducts();
    });

    document.getElementById('clear-cart-btn').addEventListener('click', clearCart);
    document.getElementById('charge-btn').addEventListener('click', openCheckoutModal);
    document.getElementById('mobile-charge-btn').addEventListener('click', openCheckoutModal);

    document.getElementById('checkout-close-btn').addEventListener('click', closeCheckoutModal);
    document.getElementById('checkout-backdrop').addEventListener('click', closeCheckoutModal);
    document.getElementById('checkout-confirm-btn').addEventListener('click', () => {
        if (checkoutState.method === 'cash' && amountTenderedValue() < total()) return;
        submitPayment();
    });
    document.getElementById('checkout-amount-input').addEventListener('input', (e) => {
        checkoutState.amountTendered = e.target.value;
        renderChangeAndConfirmState();
    });
});