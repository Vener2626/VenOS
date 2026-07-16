// ---- State ----
const state = {
    products: [],
    categories: [],
    search: '',
    editingId: null,
    selectedIcon: '🍽️',
    pendingDelete: null, // { id, name }
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

const ICON_OPTIONS = [
    '🍽️', '☕', '🍵', '🧃', '🥤', '🧋', '🥛', '🍶', '🍺', '🍷',
    '🍔', '🍕', '🌭', '🥪', '🌮', '🌯', '🥙', '🍗', '🍖', '🥩',
    '🍟', '🥓', '🍳', '🧇', '🥞', '🥐', '🥖', '🍞', '🧀', '🥨',
    '🍎', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🥑', '🥭', '🍍',
    '🥗', '🍜', '🍝', '🍣', '🍱', '🍲', '🍛', '🍚', '🍤', '🥟',
    '🍦', '🍩', '🍪', '🍰', '🎂', '🍫', '🍬', '🍭', '🍿', '🧁',
];

// ---- Data loading ----
async function loadProducts() {
    document.getElementById('product-loading').classList.remove('hidden');

    try {
        const res = await fetch('products.php');
        const data = await res.json();

        state.products = data;
        state.categories = [...new Set(data.map(p => p.category))];

        renderCategoryOptions();
        renderProductGroups();
    } catch (e) {
        document.getElementById('product-loading').textContent =
            'Could not load products. Check your database connection in database.php.';
        return;
    }

    document.getElementById('product-loading').classList.add('hidden');
}

// ---- Derived data ----
function filteredProducts() {
    return state.products.filter(p =>
        state.search === '' || p.name.toLowerCase().includes(state.search.toLowerCase())
    );
}

function groupedFiltered() {
    const groups = {};
    filteredProducts().forEach(p => {
        if (!groups[p.category]) groups[p.category] = [];
        groups[p.category].push(p);
    });
    return Object.keys(groups).map(category => ({ category, items: groups[category] }));
}

// ---- Toast ----
const TOAST_STYLES = {
    success: 'bg-emerald-500/10 text-emerald-400',
    danger: 'bg-red-500/10 text-red-400',
};

let toastTimer = null;

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `mx-4 md:mx-6 mt-3 px-4 py-2 rounded-lg text-sm ${TOAST_STYLES[type] || TOAST_STYLES.success}`;

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.add('hidden'), 4000);
}

// ---- Icon picker ----
function renderIconGrid() {
    const container = document.getElementById('icon-grid');

    container.innerHTML = ICON_OPTIONS.map(icon => `
        <button
            type="button"
            data-icon="${icon}"
            class="icon-option w-9 h-9 rounded-lg flex items-center justify-center text-lg transition ${
                state.selectedIcon === icon ? 'bg-accent/20 ring-1 ring-accent' : 'bg-panel hover:bg-white/10'
            }"
        >${icon}</button>
    `).join('');

    container.querySelectorAll('.icon-option').forEach(btn => {
        btn.addEventListener('click', () => {
            state.selectedIcon = btn.dataset.icon;
            document.getElementById('icon-preview').textContent = state.selectedIcon;
            renderIconGrid();
        });
    });
}

// ---- Category dropdown ----
function renderCategoryOptions(selected) {
    const select = document.getElementById('product-category-select');
    const options = [...state.categories];

    select.innerHTML = options.map(cat => `<option value="${cat}">${cat}</option>`).join('')
        + `<option value="__new__">+ Add new category</option>`;

    if (selected && options.includes(selected)) {
        select.value = selected;
    } else if (selected) {
        // Selected category no longer exists in the list (e.g. brand new); add it temporarily.
        const opt = document.createElement('option');
        opt.value = selected;
        opt.textContent = selected;
        select.insertBefore(opt, select.lastElementChild);
        select.value = selected;
    }

    toggleNewCategoryInput();
}

function toggleNewCategoryInput() {
    const select = document.getElementById('product-category-select');
    const input = document.getElementById('product-category-new-input');
    const isNew = select.value === '__new__';
    input.classList.toggle('hidden', !isNew);
    if (isNew) input.focus();
}

function selectedCategoryValue() {
    const select = document.getElementById('product-category-select');
    if (select.value === '__new__') {
        return document.getElementById('product-category-new-input').value.trim();
    }
    return select.value;
}

// ---- Form state ----
function resetForm() {
    state.editingId = null;
    state.selectedIcon = ICON_OPTIONS[0];

    document.getElementById('product-id-input').value = '';
    document.getElementById('product-name-input').value = '';
    document.getElementById('product-price-input').value = '';
    document.getElementById('product-category-new-input').value = '';
    document.getElementById('product-category-new-input').classList.add('hidden');
    document.getElementById('icon-preview').textContent = state.selectedIcon;

    renderCategoryOptions();
    renderIconGrid();

    document.getElementById('form-title').textContent = 'Add New Product';
    document.getElementById('product-submit-label').textContent = 'Add Product';
    document.getElementById('product-cancel-btn').classList.add('hidden');
}

function fillFormForEdit(product) {
    state.editingId = product.id;
    state.selectedIcon = product.icon || ICON_OPTIONS[0];

    document.getElementById('product-id-input').value = product.id;
    document.getElementById('product-name-input').value = product.name;
    document.getElementById('product-price-input').value = product.price;
    document.getElementById('icon-preview').textContent = state.selectedIcon;

    renderCategoryOptions(product.category);
    renderIconGrid();

    document.getElementById('form-title').textContent = 'Edit Product';
    document.getElementById('product-submit-label').textContent = 'Save Changes';
    document.getElementById('product-cancel-btn').classList.remove('hidden');

    document.getElementById('product-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ---- Save / delete ----
async function handleSubmit(e) {
    e.preventDefault();

    const name = document.getElementById('product-name-input').value.trim();
    const price = parseFloat(document.getElementById('product-price-input').value);
    const category = selectedCategoryValue();

    if (!name || !category || isNaN(price) || price <= 0) {
        showToast('Please fill in a name, category, and a price greater than 0.', 'danger');
        return;
    }

    const payload = {
        id: state.editingId,
        name,
        category,
        price,
        icon: state.selectedIcon,
    };

    try {
        const res = await protectedFetch('products_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();

        if (!res.ok) {
            showToast(data.error || 'Could not save the product.', 'danger');
            return;
        }

        showToast(data.message || 'Product saved.');
        resetForm();
        loadProducts();
    } catch (e) {
        showToast('Something went wrong saving this product.', 'danger');
    }
}

function openDeleteModal(id, name) {
    state.pendingDelete = { id, name };
    document.getElementById('delete-modal-text').textContent =
        `"${name}" will be permanently removed. This can't be undone.`;
    document.getElementById('delete-modal').classList.remove('hidden');
    document.getElementById('delete-modal').classList.add('flex');
}

function closeDeleteModal() {
    state.pendingDelete = null;
    document.getElementById('delete-modal').classList.add('hidden');
    document.getElementById('delete-modal').classList.remove('flex');
}

async function confirmDelete() {
    if (!state.pendingDelete) return;
    const { id, name } = state.pendingDelete;

    const confirmBtn = document.getElementById('delete-confirm-btn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Deleting...';

    try {
        const res = await protectedFetch('products_delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const data = await res.json();

        if (!res.ok) {
            showToast(data.error || 'Could not delete the product.', 'danger');
        } else {
            showToast(data.message || `"${name}" was deleted.`, 'danger');
            if (state.editingId === id) resetForm();
            loadProducts();
        }
    } catch (e) {
        showToast('Something went wrong deleting this product.', 'danger');
    }

    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Delete';
    closeDeleteModal();
}

// ---- Rendering ----
function renderProductGroups() {
    const container = document.getElementById('product-groups');
    const groups = groupedFiltered();

    document.getElementById('product-empty').classList.toggle('hidden', groups.length > 0);

    container.innerHTML = groups.map(group => `
        <div class="bg-card border border-white/5 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <div class="flex items-center gap-2 text-sm font-semibold text-gray-300">
                    <span class="w-2 h-2 rounded-full ${categoryColor(group.category)}"></span>
                    ${group.category}
                </div>
                <span class="text-xs text-gray-500">${group.items.length} item${group.items.length === 1 ? '' : 's'}</span>
            </div>
            <div class="divide-y divide-white/5">
                ${group.items.map(rowTemplate).join('')}
            </div>
        </div>
    `).join('');

    container.querySelectorAll('[data-action="edit"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const product = state.products.find(p => p.id == btn.dataset.id);
            if (product) fillFormForEdit(product);
        });
    });

    container.querySelectorAll('[data-action="delete"]').forEach(btn => {
        btn.addEventListener('click', () => {
            openDeleteModal(Number(btn.dataset.id), btn.dataset.name);
        });
    });
}

function rowTemplate(product) {
    return `
        <div class="flex items-center gap-4 px-5 py-4">
            <div class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-lg shrink-0">${product.icon || '🍽️'}</div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-100 truncate">${product.name}</div>
                <div class="text-xs text-gray-500">${product.category}</div>
            </div>
            <div class="text-sm font-semibold text-accent shrink-0">₱${Number(product.price).toFixed(2)}</div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button
                    data-action="edit"
                    data-id="${product.id}"
                    title="Edit"
                    class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center text-gray-400 hover:text-gray-200 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button
                    data-action="delete"
                    data-id="${product.id}"
                    data-name="${product.name}"
                    title="Delete"
                    class="w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500/20 flex items-center justify-center text-red-400 transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
    `;
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    state.selectedIcon = ICON_OPTIONS[0];
    document.getElementById('icon-preview').textContent = state.selectedIcon;

    renderIconGrid();

    document.getElementById('product-search-input').addEventListener('input', (e) => {
        state.search = e.target.value;
        renderProductGroups();
    });

    document.getElementById('product-category-select').addEventListener('change', toggleNewCategoryInput);
    document.getElementById('product-form').addEventListener('submit', handleSubmit);
    document.getElementById('product-cancel-btn').addEventListener('click', resetForm);

    document.getElementById('delete-cancel-btn').addEventListener('click', closeDeleteModal);
    document.getElementById('delete-backdrop').addEventListener('click', closeDeleteModal);
    document.getElementById('delete-confirm-btn').addEventListener('click', confirmDelete);
});

document.addEventListener('pin:verified', loadProducts);