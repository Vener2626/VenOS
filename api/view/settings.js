const TOAST_STYLES = {
    success: 'bg-emerald-500/10 text-emerald-400',
    danger: 'bg-red-500/10 text-red-400',
};

let toastTimer = null;

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `mx-4 md:mx-6 mt-3 px-4 py-2 rounded-lg text-sm ${TOAST_STYLES[type] || TOAST_STYLES.success}`;
    toast.classList.remove('hidden');

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.add('hidden'), 4000);
}

async function handlePinFormSubmit(e) {
    e.preventDefault();

    const currentPin = document.getElementById('current-pin-input').value.trim();
    const newPin = document.getElementById('new-pin-input').value.trim();
    const confirmPin = document.getElementById('confirm-pin-input').value.trim();

    if (!/^\d{4}$/.test(currentPin) || !/^\d{4}$/.test(newPin) || !/^\d{4}$/.test(confirmPin)) {
        showToast('All PIN fields must be exactly 4 digits.', 'danger');
        return;
    }
    if (newPin !== confirmPin) {
        showToast('New PIN and confirmation do not match.', 'danger');
        return;
    }

    try {
        const res = await protectedFetch('settings_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ currentPin, newPin, confirmPin }),
        });
        const data = await res.json();

        if (!res.ok) {
            showToast(data.error || 'Could not update the PIN.', 'danger');
            return;
        }

        showToast(data.message || 'PIN updated.');
        document.getElementById('pin-form').reset();
    } catch (e) {
        showToast('Something went wrong updating the PIN.', 'danger');
    }
}

async function handleLoginPinFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const role = form.dataset.role; // 'cashier' or 'owner'
    const currentPin = form.querySelector('input[id$="-current-pin"]').value.trim();
    const newPin = form.querySelector('input[id$="-new-pin"]').value.trim();
    const confirmPin = form.querySelector('input[id$="-confirm-pin"]').value.trim();

    if (!/^\d{6}$/.test(currentPin) || !/^\d{6}$/.test(newPin) || !/^\d{6}$/.test(confirmPin)) {
        showToast('All PIN fields must be exactly 6 digits.', 'danger');
        return;
    }
    if (newPin !== confirmPin) {
        showToast('New PIN and confirmation do not match.', 'danger');
        return;
    }

    try {
        const res = await protectedFetch('login_pins_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ role, currentPin, newPin, confirmPin }),
        });
        const data = await res.json();

        if (!res.ok) {
            showToast(data.error || 'Could not update the PIN.', 'danger');
            return;
        }

        showToast(data.message || 'PIN updated.');
        form.reset();
    } catch (e) {
        showToast('Something went wrong updating the PIN.', 'danger');
    }
}

document.addEventListener('pin:verified', () => {
    document.getElementById('pin-form').addEventListener('submit', handlePinFormSubmit);
    document.getElementById('cashier-login-pin-form').addEventListener('submit', handleLoginPinFormSubmit);
    document.getElementById('owner-login-pin-form').addEventListener('submit', handleLoginPinFormSubmit);
});