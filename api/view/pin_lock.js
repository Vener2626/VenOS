// ---- PIN lock overlay behavior (shared by Dashboard, Reports, Products, Settings) ----
(function () {
    let entered = '';
    let locked = false;
    let failCount = 0;

    function overlay() {
        return document.getElementById('pin-lock-overlay');
    }

    function renderDots() {
        document.querySelectorAll('.pin-dot').forEach((dot, i) => {
            dot.classList.toggle('bg-accent', i < entered.length);
            dot.classList.toggle('border-accent', i < entered.length);
        });
    }

    function showError(msg) {
        const el = document.getElementById('pin-error');
        el.textContent = msg;
        el.classList.remove('hidden');
    }

    function clearError() {
        document.getElementById('pin-error').classList.add('hidden');
    }

    function shakeDots() {
        const dots = document.getElementById('pin-dots');
        dots.classList.add('animate-pulse');
        setTimeout(() => dots.classList.remove('animate-pulse'), 300);
    }

    function lockKeypad(seconds) {
        locked = true;
        const keypad = document.getElementById('pin-keypad');
        keypad.classList.add('opacity-40', 'pointer-events-none');
        let remaining = seconds;
        showError(`Too many attempts. Try again in ${remaining}s.`);

        const timer = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(timer);
                locked = false;
                failCount = 0;
                clearError();
                keypad.classList.remove('opacity-40', 'pointer-events-none');
            } else {
                showError(`Too many attempts. Try again in ${remaining}s.`);
            }
        }, 1000);
    }

    async function submitPin() {
        try {
            const res = await fetch('verify_pin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ pin: entered }),
            });
            const data = await res.json();

            if (!res.ok || !data.valid) {
                failCount++;
                showError(data.error || 'Incorrect PIN.');
                shakeDots();
                entered = '';
                renderDots();
                if (failCount >= 5) lockKeypad(30);
                return;
            }

            overlay().remove();
            document.dispatchEvent(new CustomEvent('pin:verified'));
        } catch (e) {
            showError('Connection error. Try again.');
            entered = '';
            renderDots();
        }
    }

    function pressKey(key) {
        if (locked || !overlay()) return;
        clearError();
        if (key === 'back') {
            entered = entered.slice(0, -1);
        } else if (entered.length < 4) {
            entered += key;
        }
        renderDots();
        if (entered.length === 4) submitPin();
    }

    function renderKeypad() {
        const keypad = document.getElementById('pin-keypad');
        if (!keypad) return;

        const keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '', '0', 'back'];
        keypad.innerHTML = keys.map(k => {
            if (k === '') return '<div></div>';
            if (k === 'back') {
                return `<button type="button" data-key="back" aria-label="Backspace" class="pin-key w-16 h-16 rounded-full flex items-center justify-center text-gray-400 hover:bg-white/5 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l-7-7 7-7m8 14H5" /></svg>
                </button>`;
            }
            return `<button type="button" data-key="${k}" class="pin-key w-16 h-16 rounded-full text-xl font-semibold bg-panel hover:bg-white/10 transition">${k}</button>`;
        }).join('');

        keypad.querySelectorAll('.pin-key').forEach(btn => {
            btn.addEventListener('click', () => pressKey(btn.dataset.key));
        });
    }

    document.addEventListener('DOMContentLoaded', renderKeypad);

    // Let cashiers/owners on desktop type the PIN with a physical keyboard too.
    document.addEventListener('keydown', (e) => {
        if (!overlay()) return;
        if (e.key >= '0' && e.key <= '9') pressKey(e.key);
        if (e.key === 'Backspace') pressKey('back');
    });

    // ---- Helper for page scripts: fetch that re-locks the page if the
    // server-side unlock window (5 min) has expired since we verified. ----
    window.protectedFetch = async function protectedFetch(url, opts) {
        const res = await fetch(url, opts);
        if (res.status === 401) {
            location.reload();
            throw new Error('PIN session expired');
        }
        return res;
    };
})();