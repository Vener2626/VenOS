function paymentBadge(method) {
    if (method === 'gcash') {
        return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-violet-500/10 text-violet-400 shrink-0"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>GCash</span>`;
    }
    return `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-400 shrink-0"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5-1.343 1.5-3 1.5M12 8V6m0 8v2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Cash</span>`;
}

function peso(n) {
    return '₱' + Number(n).toFixed(2);
}

async function loadDashboard() {
    document.getElementById('dashboard-loading').classList.remove('hidden');
    document.getElementById('dashboard-content').classList.add('hidden');

    try {
        const res = await protectedFetch('dashboard_data.php');
        const data = await res.json();
        renderDashboard(data);
    } catch (e) {
        document.getElementById('dashboard-loading').textContent =
            'Could not load dashboard data. Check your database connection.';
        return;
    }

    document.getElementById('dashboard-loading').classList.add('hidden');
    document.getElementById('dashboard-content').classList.remove('hidden');
}

function renderDashboard(data) {
    document.getElementById('dashboard-warning').classList.toggle('hidden', data.dbReady);

    const t = data.today;
    document.getElementById('stat-revenue').textContent = peso(t.revenue);
    document.getElementById('stat-sales-count').textContent = `${t.cnt} sales`;
    document.getElementById('stat-transactions').textContent = t.cnt;
    document.getElementById('stat-avg-order').textContent = peso(t.avgOrder);
    document.getElementById('stat-cash-count').textContent = t.cash_count;
    document.getElementById('stat-gcash-count').textContent = `${t.gcash_count} digital`;

    renderChart(data.days);
    renderProductPerformance(data.topProducts, data.slowProducts);
    renderTransactions(data.orders);
}

function productRow(p, rank) {
    return `<div class="flex items-center gap-3 px-5 py-3">
        <span class="w-5 text-xs text-gray-500 font-medium shrink-0">${rank}</span>
        <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-sm shrink-0">${p.icon || ''}</div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-gray-100 truncate">${p.name}</div>
            <div class="text-xs text-gray-500">${p.qtySold} sold</div>
        </div>
        <div class="text-sm font-semibold text-gray-100 shrink-0">${peso(p.revenue)}</div>
    </div>`;
}

function renderProductPerformance(topProducts, slowProducts) {
    const topEl = document.getElementById('top-products');
    const slowEl = document.getElementById('slow-products');

    topProducts = topProducts || [];
    slowProducts = slowProducts || [];

    if (!topProducts.length || topProducts.every(p => p.qtySold === 0)) {
        topEl.innerHTML = '<div class="text-center text-gray-500 text-sm py-10">No sales in the last 30 days.</div>';
    } else {
        topEl.innerHTML = `<div class="divide-y divide-white/5">
            ${topProducts.map((p, i) => productRow(p, i + 1)).join('')}
        </div>`;
    }

    if (!slowProducts.length) {
        slowEl.innerHTML = '<div class="text-center text-gray-500 text-sm py-10">No product data yet.</div>';
    } else {
        slowEl.innerHTML = `<div class="divide-y divide-white/5">
            ${slowProducts.map((p, i) => productRow(p, i + 1)).join('')}
        </div>`;
    }
}

function renderChart(days) {
    const el = document.getElementById('revenue-chart');
    const totalWeek = days.reduce((sum, d) => sum + d.revenue, 0);

    if (!days.length || totalWeek === 0) {
        el.innerHTML = '<div class="text-center text-gray-500 text-sm py-10">No sales recorded yet.</div>';
        return;
    }

    const maxRevenue = Math.max(...days.map(d => d.revenue)) || 1;

    el.innerHTML = `<div class="flex items-end gap-3 h-48">
        ${days.map(day => {
            const heightPct = Math.max(4, Math.round((day.revenue / maxRevenue) * 100));
            return `<div class="flex-1 flex flex-col items-center justify-end h-full gap-2">
                ${day.revenue > 0 ? `<div class="text-xs text-accent font-medium">₱${Math.round(day.revenue).toLocaleString()}</div>` : ''}
                <div class="w-full bg-accent/80 rounded-t-md" style="height: ${heightPct}%"></div>
                <div class="text-xs text-gray-500">${day.label}</div>
            </div>`;
        }).join('')}
    </div>`;
}

const RECENT_PAGE_SIZE = 10;
let recentOrders = [];
let recentPage = 1;

function transactionRow(o) {
    const d = new Date(o.created_at.replace(' ', 'T'));
    const time = d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    const date = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    return `<div class="flex items-center gap-4 px-5 py-4">
        ${paymentBadge(o.payment_method)}
        <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-gray-100 truncate">${o.itemsSummary || '—'}</div>
            <div class="text-xs text-gray-500">${time} · ${date}</div>
        </div>
        <div class="text-sm font-semibold text-gray-100 shrink-0">${peso(o.total)}</div>
    </div>`;
}

function renderTransactions(orders) {
    recentOrders = orders || [];
    recentPage = 1;
    document.getElementById('recent-count').textContent = recentOrders.length;
    renderRecentPage();
}

function renderRecentPage() {
    const el = document.getElementById('recent-transactions');
    const paginationEl = document.getElementById('recent-pagination');

    if (!recentOrders.length) {
        el.innerHTML = '<div class="text-center text-gray-500 text-sm py-10">No transactions yet.</div>';
        paginationEl.classList.add('hidden');
        paginationEl.classList.remove('flex');
        return;
    }

    const totalPages = Math.max(1, Math.ceil(recentOrders.length / RECENT_PAGE_SIZE));
    recentPage = Math.min(Math.max(1, recentPage), totalPages);

    const start = (recentPage - 1) * RECENT_PAGE_SIZE;
    const pageOrders = recentOrders.slice(start, start + RECENT_PAGE_SIZE);

    el.innerHTML = `<div class="divide-y divide-white/5">
        ${pageOrders.map(transactionRow).join('')}
    </div>`;

    if (totalPages > 1) {
        paginationEl.classList.remove('hidden');
        paginationEl.classList.add('flex');
        document.getElementById('recent-page-info').textContent = `Page ${recentPage} of ${totalPages}`;
        document.getElementById('recent-prev').disabled = recentPage === 1;
        document.getElementById('recent-next').disabled = recentPage === totalPages;
    } else {
        paginationEl.classList.add('hidden');
        paginationEl.classList.remove('flex');
    }
}

document.getElementById('recent-prev').addEventListener('click', () => {
    recentPage -= 1;
    renderRecentPage();
});

document.getElementById('recent-next').addEventListener('click', () => {
    recentPage += 1;
    renderRecentPage();
});

document.addEventListener('pin:verified', loadDashboard);