// ---- State ----
const state = {
    tab: 'logs',
    offsets: { daily: 0, weekly: 0, monthly: 0, yearly: 0 },
    logsDate: new Date().toISOString().slice(0, 10),
    logsPage: 1,
    data: null,
};

const TABS = [
    { id: 'logs', label: 'Logs' },
    { id: 'daily', label: 'Daily' },
    { id: 'weekly', label: 'Weekly' },
    { id: 'monthly', label: 'Monthly' },
    { id: 'yearly', label: 'Yearly' },
];

const TAB_ICON_PATHS = {
    logs: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
    daily: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
    weekly: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
    monthly: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />',
    yearly: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />',
};

function tabIconSvg(tabId, cls = 'w-4 h-4') {
    return `<svg class="${cls}" fill="none" stroke="currentColor" viewBox="0 0 24 24">${TAB_ICON_PATHS[tabId] || ''}</svg>`;
}

function peso(n) {
    return '₱' + Number(n).toFixed(2);
}

function paymentBadge(method) {
    if (method === 'gcash') {
        return '<span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-violet-500/10 text-violet-400 shrink-0">GCash</span>';
    }
    return '<span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-400 shrink-0">Cash</span>';
}

function todayBadge() {
    return '<span class="ml-2 inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-accent/20 text-accent align-middle">Today</span>';
}

function nowBadge() {
    return '<span class="ml-2 inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-accent/20 text-accent align-middle">Now</span>';
}

// ---- Sub-tabs ----
function renderSubtabs() {
    const container = document.getElementById('report-subtabs');
    container.innerHTML = TABS.map(t => `
        <button
            data-tab="${t.id}"
            class="report-tab-btn flex items-center gap-1.5 px-4 py-1.5 rounded-md text-sm font-medium transition whitespace-nowrap ${
                state.tab === t.id ? 'bg-accent text-black' : 'text-gray-400 hover:text-gray-200'
            }"
        >${tabIconSvg(t.id)}${t.label}</button>
    `).join('');

    container.querySelectorAll('.report-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.dataset.tab === 'logs' && state.tab !== 'logs') {
                state.logsPage = 1;
            }
            state.tab = btn.dataset.tab;
            renderSubtabs();
            loadReport();
        });
    });
}

// ---- Period controls ----
function updatePeriodControls() {
    const periodNav = document.getElementById('period-nav');
    const logsNav = document.getElementById('logs-date-nav');

    if (state.tab === 'logs') {
        periodNav.classList.add('hidden');
        periodNav.classList.remove('flex');
        logsNav.classList.remove('hidden');
        logsNav.classList.add('flex');
        document.getElementById('logs-date-input').value = state.logsDate;
    } else {
        logsNav.classList.add('hidden');
        logsNav.classList.remove('flex');
        periodNav.classList.remove('hidden');
        periodNav.classList.add('flex');
    }
}

function currentOffset() {
    return state.offsets[state.tab] ?? 0;
}

// ---- Fetch + render ----
function reportQueryParams() {
    if (state.tab === 'logs') {
        return `type=logs&date=${encodeURIComponent(state.logsDate)}&page=${state.logsPage}`;
    }
    return `type=${state.tab}&offset=${currentOffset()}`;
}

async function loadReport() {
    updatePeriodControls();
    document.getElementById('report-loading').classList.remove('hidden');
    document.getElementById('report-content').innerHTML = '';

    try {
        const res = await protectedFetch(`reports_data.php?${reportQueryParams()}`);
        const data = await res.json();

        if (!res.ok) {
            document.getElementById('report-loading').textContent = data.error || 'Could not load report.';
            return;
        }

        state.data = data;
        renderReport(data);
    } catch (e) {
        document.getElementById('report-loading').textContent =
            'Could not load report data. Check your database connection.';
        return;
    }

    document.getElementById('report-loading').classList.add('hidden');
}

function renderReport(data) {
    const titles = {
        logs: 'Transaction Log',
        daily: 'Daily Logbook',
        weekly: 'Weekly Sales',
        monthly: 'Monthly Logbook',
        yearly: 'Yearly Overview',
    };
    document.getElementById('report-title').innerHTML = `
        <span class="inline-flex items-center gap-2">
            <span class="text-accent">${tabIconSvg(state.tab)}</span>
            ${titles[state.tab]}
        </span>
    `;

    const nextBtn = document.getElementById('period-next-btn');
    if (state.tab !== 'logs') {
        document.getElementById('report-subtitle').textContent = data.label;
        nextBtn.disabled = !data.canGoNext;
    } else {
        document.getElementById('report-subtitle-logs').textContent = data.label;
    }

    const totalBlock = document.getElementById('report-total-block');
    if (state.tab === 'weekly') {
        totalBlock.classList.add('hidden');
    } else {
        totalBlock.classList.remove('hidden');
        document.getElementById('report-total').textContent = peso(data.revenue);
        const labels = {
            logs: `${data.count} transaction${data.count === 1 ? '' : 's'}`,
            daily: `${data.count} total sales`,
            monthly: `${data.count} sales`,
            yearly: `Total ${data.year}`,
        };
        document.getElementById('report-total-label').textContent = labels[state.tab] || '';
    }

    const renderers = {
        logs: renderLogs,
        daily: renderDaily,
        weekly: renderWeekly,
        monthly: renderMonthly,
        yearly: renderYearly,
    };
    document.getElementById('report-content').innerHTML = renderers[state.tab](data);
    renderLogsPagination(state.tab === 'logs' ? data : null);
}

function renderLogsPagination(data) {
    const container = document.getElementById('logs-pagination');

    if (!data || data.totalPages <= 1) {
        container.classList.add('hidden');
        container.classList.remove('flex');
        container.innerHTML = '';
        return;
    }

    container.classList.remove('hidden');
    container.classList.add('flex');
    container.innerHTML = `
        <button
            id="logs-prev-page-btn"
            class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white/5 text-gray-300 hover:bg-white/10 transition disabled:opacity-30 disabled:cursor-not-allowed"
            ${data.page <= 1 ? 'disabled' : ''}
        >← Prev</button>
        <span class="text-sm text-gray-500">Page ${data.page} of ${data.totalPages}</span>
        <button
            id="logs-next-page-btn"
            class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white/5 text-gray-300 hover:bg-white/10 transition disabled:opacity-30 disabled:cursor-not-allowed"
            ${data.page >= data.totalPages ? 'disabled' : ''}
        >Next →</button>
    `;

    document.getElementById('logs-prev-page-btn').addEventListener('click', () => {
        if (state.logsPage <= 1) return;
        state.logsPage -= 1;
        loadReport();
    });
    document.getElementById('logs-next-page-btn').addEventListener('click', () => {
        if (state.logsPage >= data.totalPages) return;
        state.logsPage += 1;
        loadReport();
    });
}

function renderLogs(data) {
    if (!data.rows.length) {
        return `<div class="bg-card border border-white/5 rounded-xl text-center text-gray-500 text-sm py-16">No transactions on this date.</div>`;
    }

    return `
        <div class="bg-card border border-white/5 rounded-xl overflow-hidden">
            <div class="flex items-center gap-4 px-5 py-3 border-b border-white/5 text-xs font-medium tracking-wide text-gray-500">
                <div class="w-16 shrink-0">TIME</div>
                <div class="flex-1 min-w-0">ITEMS</div>
                <div class="w-16 shrink-0">PAYMENT</div>
                <div class="w-20 text-right shrink-0">TOTAL</div>
            </div>
            <div class="divide-y divide-white/5">
                ${data.rows.map(r => `
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="w-16 text-xs text-gray-500 shrink-0">${r.time}</div>
                        <div class="flex-1 min-w-0 text-sm font-medium text-gray-100 truncate">${r.items || '—'}</div>
                        ${paymentBadge(r.payment)}
                        <div class="w-20 text-right text-sm font-semibold text-gray-100 shrink-0">${peso(r.total)}</div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function renderDaily(data) {
    if (!data.rows.length) {
        return `<div class="bg-card border border-white/5 rounded-xl text-center text-gray-500 text-sm py-16">No sales in this period.</div>`;
    }

    return `
        <div class="bg-card border border-white/5 rounded-xl overflow-hidden">
            <div class="flex items-center gap-4 px-5 py-3 border-b border-white/5 text-xs font-medium tracking-wide text-gray-500">
                <div class="flex-1">DATE</div>
                <div class="w-20 text-right">SALES</div>
                <div class="w-24 text-right">REVENUE</div>
            </div>
            <div class="divide-y divide-white/5">
                ${data.rows.map(r => `
                    <div class="flex items-center gap-4 px-5 py-3">
                        <div class="flex-1 text-sm font-medium text-gray-100">
                            ${r.label}${r.isToday ? todayBadge() : ''}
                        </div>
                        <div class="w-20 text-right text-sm text-gray-400">${r.cnt}</div>
                        <div class="w-24 text-right text-sm font-semibold ${r.revenue > 0 ? 'text-gray-100' : 'text-gray-600'}">
                            ${r.revenue > 0 ? peso(r.revenue) : '—'}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function renderWeekly(data) {
    return data.weeks.map(week => `
        <div class="bg-card border border-white/5 rounded-xl overflow-hidden mb-4">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/5">
                <div class="text-sm font-semibold text-gray-300">${week.label}</div>
                <div class="text-sm font-bold text-accent">${peso(week.total)}</div>
            </div>
            <div class="flex items-center gap-3 px-5 pt-3 text-xs font-medium tracking-wide text-gray-500">
                <div class="w-10">DAY</div>
                <div class="w-14">DATE</div>
                <div class="flex-1"></div>
                <div class="w-10 text-right">SALES</div>
                <div class="w-20 text-right">REVENUE</div>
            </div>
            <div class="px-5 py-4 space-y-3">
                ${week.days.map(day => `
                    <div class="flex items-center gap-3">
                        <div class="w-10 text-xs font-medium text-gray-300">${day.day}</div>
                        <div class="w-14 text-xs text-gray-500">${day.date}</div>
                        <div class="flex-1 h-2 rounded-full bg-white/5 overflow-hidden">
                            <div class="h-full bg-accent/80 rounded-full" style="width: ${day.pct}%"></div>
                        </div>
                        <div class="w-10 text-right text-xs text-gray-500">${day.isFuture ? '—' : day.cnt}</div>
                        <div class="w-20 text-right text-sm font-semibold ${day.revenue > 0 ? 'text-gray-100' : 'text-gray-600'}">
                            ${day.isFuture ? '—' : (day.revenue > 0 ? peso(day.revenue) : '—')}
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `).join('');
}

function renderMonthly(data) {
    if (!data.rows.length) {
        return `<div class="bg-card border border-white/5 rounded-xl text-center text-gray-500 text-sm py-16">No sales in this month.</div>`;
    }

    return `
        <div class="bg-card border border-white/5 rounded-xl overflow-hidden">
            <div class="flex items-center gap-4 px-5 py-3 border-b border-white/5 text-xs font-medium tracking-wide text-gray-500">
                <div class="w-6">#</div>
                <div class="w-10">DAY</div>
                <div class="flex-1">DATE</div>
                <div class="w-16 text-right">SALES</div>
                <div class="w-24 text-right">REVENUE</div>
            </div>
            <div class="divide-y divide-white/5">
                ${data.rows.map(r => `
                    <div class="flex items-center gap-4 px-5 py-3 ${r.isToday ? 'bg-white/5' : ''}">
                        <div class="w-6 text-xs text-gray-500">${r.num}</div>
                        <div class="w-10 text-xs text-gray-500">${r.day}</div>
                        <div class="flex-1 text-sm font-medium text-gray-100">
                            ${r.date}${r.isToday ? todayBadge() : ''}
                        </div>
                        <div class="w-16 text-right text-sm text-gray-400">${r.isFuture ? '—' : r.cnt}</div>
                        <div class="w-24 text-right text-sm font-semibold ${r.revenue > 0 ? 'text-gray-100' : 'text-gray-600'}">
                            ${r.isFuture ? '—' : (r.revenue > 0 ? peso(r.revenue) : '—')}
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="flex items-center gap-4 px-5 py-4 border-t border-white/10 bg-white/[0.02]">
                <div class="flex-1 text-sm font-bold text-gray-100">Month Total</div>
                <div class="w-16 text-right text-sm font-semibold text-gray-300">${data.count}</div>
                <div class="w-24 text-right text-sm font-bold text-accent">${peso(data.revenue)}</div>
            </div>
        </div>
    `;
}

function renderYearly(data) {
    return `
        <div class="bg-card border border-white/5 rounded-xl overflow-hidden">
            <div class="flex items-center gap-4 px-5 py-3 border-b border-white/5 text-xs font-medium tracking-wide text-gray-500">
                <div class="w-24 shrink-0">MONTH</div>
                <div class="w-16 text-right shrink-0">SALES</div>
                <div class="w-24 text-right shrink-0">REVENUE</div>
                <div class="flex-1 text-right">SHARE</div>
            </div>
            <div class="divide-y divide-white/5">
                ${data.rows.map(r => `
                    <div class="flex items-center gap-4 px-5 py-3 ${r.isNow ? 'bg-white/5' : ''}">
                        <div class="w-24 shrink-0 flex items-center gap-2 text-sm font-medium text-gray-100 whitespace-nowrap">
                            ${r.month}${r.isNow ? nowBadge() : ''}
                        </div>
                        <div class="w-16 text-right text-sm text-gray-400 shrink-0">${r.isFuture ? '—' : r.cnt}</div>
                        <div class="w-24 text-right text-sm font-semibold shrink-0 ${r.revenue > 0 ? 'text-gray-100' : 'text-gray-600'}">
                            ${r.isFuture ? '—' : (r.revenue > 0 ? peso(r.revenue) : '—')}
                        </div>
                        <div class="flex-1 h-2 rounded-full bg-white/5 overflow-hidden">
                            <div class="h-full bg-accent/80 rounded-full" style="width: ${r.pct}%"></div>
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="flex items-center gap-4 px-5 py-4 border-t border-white/10 bg-white/[0.02]">
                <div class="flex-1 text-sm font-bold text-gray-100">${data.year} Total</div>
                <div class="w-16 text-right text-sm font-semibold text-gray-300">${data.count}</div>
                <div class="w-24 text-right text-sm font-bold text-accent">${peso(data.revenue)}</div>
            </div>
        </div>
    `;
}

// ---- Export ----
function exportCsv() {
    window.location.href = `reports_export.php?${reportQueryParams()}`;
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
    renderSubtabs();

    document.getElementById('logs-date-input').addEventListener('change', (e) => {
        state.logsDate = e.target.value;
        state.logsPage = 1;
        loadReport();
    });

    document.getElementById('period-prev-btn').addEventListener('click', () => {
        state.offsets[state.tab] = currentOffset() + 1;
        loadReport();
    });

    document.getElementById('period-next-btn').addEventListener('click', () => {
        if (currentOffset() <= 0) return;
        state.offsets[state.tab] = currentOffset() - 1;
        loadReport();
    });

    document.getElementById('export-csv-btn').addEventListener('click', exportCsv);
});

document.addEventListener('pin:verified', loadReport);