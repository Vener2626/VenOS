<?php
session_start();

require __DIR__ . '/view/database.php';

const MAX_ATTEMPTS      = 9;
const COOLDOWN_SECONDS  = 30;
const LOCKOUT_SECONDS   = 86400; // 1 day

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0775, true);
}
$attemptsFile = $dataDir . '/login_attempts.json';

function load_attempts(string $file): array {
    if (!file_exists($file)) return [];
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : [];
}
function save_attempts(string $file, array $data): void {
    file_put_contents($file, json_encode($data), LOCK_EX);
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$now = time();

$attempts = load_attempts($attemptsFile);
$record = $attempts[$ip] ?? ['count' => 0, 'last_attempt' => 0, 'locked_until' => 0];

$error = false;
$lockedUntil = ($record['locked_until'] ?? 0) > $now ? (int) $record['locked_until'] : null;
$cooldownUntil = null;
if (!$lockedUntil && ($record['count'] ?? 0) > 0 && ($record['last_attempt'] ?? 0) > 0) {
    $elapsed = $now - $record['last_attempt'];
    if ($elapsed < COOLDOWN_SECONDS) {
        $cooldownUntil = $record['last_attempt'] + COOLDOWN_SECONDS;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$lockedUntil && !$cooldownUntil) {
    $submitted = preg_replace('/\D/', '', $_POST['pin'] ?? '');
    $role = null;

    if ($submitted !== '') {
        $settings = $pdo->query('SELECT cashier_pin_hash, owner_pin_hash FROM settings ORDER BY id ASC LIMIT 1')->fetch();

        if ($settings) {
            if (!empty($settings['owner_pin_hash']) && password_verify($submitted, $settings['owner_pin_hash'])) {
                $role = 'owner';
            } elseif (!empty($settings['cashier_pin_hash']) && password_verify($submitted, $settings['cashier_pin_hash'])) {
                $role = 'cashier';
            }
        }
    }

    if ($role !== null) {
        unset($attempts[$ip]);
        save_attempts($attemptsFile, $attempts);

        $_SESSION['role'] = $role;
        header('Location: ' . ($role === 'owner' ? 'view/dashboard.php' : 'view/index.php'));
        exit;
    }

    $record['count'] = ($record['count'] ?? 0) + 1;
    $record['last_attempt'] = $now;
    if ($record['count'] >= MAX_ATTEMPTS) {
        $record['locked_until'] = $now + LOCKOUT_SECONDS;
        $record['count'] = 0;
    }
    $attempts[$ip] = $record;
    save_attempts($attemptsFile, $attempts);

    $error = true;
    $lockedUntil = $record['locked_until'] > $now ? (int) $record['locked_until'] : null;
    $cooldownUntil = $lockedUntil ? null : ($now + COOLDOWN_SECONDS);
}

$remainingAttempts = MAX_ATTEMPTS - ($attempts[$ip]['count'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenOS — Login</title>
    <?php require __DIR__ . '/view/theme.php'; ?>
</head>
<body class="bg-surface text-gray-200 font-sans antialiased h-screen overflow-hidden">

    <div class="h-full flex flex-col items-center justify-center px-4">

        <a href="index.php" class="flex items-center gap-2 mb-8">
            <div class="w-9 h-9 rounded-md bg-accent/20 text-accent flex items-center justify-center font-bold">V</div>
            <span class="font-semibold text-lg tracking-tight">VenOS</span>
        </a>

        <div class="w-full max-w-sm bg-card border border-white/5 rounded-2xl p-6">

            <div class="text-center mb-6">
                <div class="w-11 h-11 rounded-full bg-accent/10 text-accent flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 class="text-lg font-semibold text-gray-100">Login</h1>
                <p class="text-sm text-gray-500 mt-1">Enter your 6-digit PIN. Cashier and owner PINs open different screens.</p>
            </div>

            <form id="pin-form" method="POST" novalidate>
                <input type="hidden" name="pin" id="pin-value">

                <div id="pin-dots" class="flex items-center justify-center gap-2.5 mb-2">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <span class="pin-dot w-3.5 h-3.5 rounded-full bg-white/10 transition"></span>
                    <?php endfor; ?>
                </div>

                <p id="pin-error" class="text-center text-xs text-red-400 h-4 mb-1 <?= $error && !$lockedUntil && !$cooldownUntil ? '' : 'invisible' ?>">
                    Incorrect PIN.
                </p>
                <p id="pin-status" class="text-center text-xs text-amber-400 h-4 mb-4 <?= ($lockedUntil || $cooldownUntil) ? '' : 'invisible' ?>"></p>

                <div id="pin-keypad" class="grid grid-cols-3 gap-3 mb-2">
                    <?php foreach ([1,2,3,4,5,6,7,8,9] as $n): ?>
                    <button type="button" data-key="<?= $n ?>" class="pin-key h-14 rounded-xl bg-white/5 hover:bg-white/10 active:bg-accent/20 text-lg font-semibold text-gray-100 transition disabled:opacity-30"><?= $n ?></button>
                    <?php endforeach; ?>
                    <button type="button" data-key="clear" class="pin-key h-14 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-medium text-gray-400 transition disabled:opacity-30">Clear</button>
                    <button type="button" data-key="0" class="pin-key h-14 rounded-xl bg-white/5 hover:bg-white/10 active:bg-accent/20 text-lg font-semibold text-gray-100 transition disabled:opacity-30">0</button>
                    <button type="button" data-key="back" class="pin-key h-14 rounded-xl bg-white/5 hover:bg-white/10 text-gray-400 flex items-center justify-center transition disabled:opacity-30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l-7-7 7-7m-7 7h18" /></svg>
                    </button>
                </div>
            </form>
        </div>

        <p class="text-xs text-gray-600 mt-6">Not staff? <a href="index.php" class="text-accent hover:underline">Back to home</a></p>
    </div>

    <script>
    (function () {
        const MAX_LEN = 6;
        const dotsWrap = document.getElementById('pin-dots');
        const dots = dotsWrap.querySelectorAll('.pin-dot');
        const hiddenInput = document.getElementById('pin-value');
        const form = document.getElementById('pin-form');
        const errorEl = document.getElementById('pin-error');
        const statusEl = document.getElementById('pin-status');
        const keys = document.querySelectorAll('.pin-key');
        let pin = '';

        const lockedUntil = <?= $lockedUntil ? $lockedUntil * 1000 : 'null' ?>;
        const cooldownUntil = <?= $cooldownUntil ? $cooldownUntil * 1000 : 'null' ?>;

        function render() {
            dots.forEach((dot, i) => {
                dot.classList.toggle('bg-accent', i < pin.length);
                dot.classList.toggle('bg-white/10', i >= pin.length);
            });
            hiddenInput.value = pin;
        }

        function setKeysDisabled(disabled) {
            keys.forEach((k) => { k.disabled = disabled; });
        }

        function formatDuration(ms) {
            const totalSeconds = Math.max(0, Math.ceil(ms / 1000));
            const h = Math.floor(totalSeconds / 3600);
            const m = Math.floor((totalSeconds % 3600) / 60);
            const s = totalSeconds % 60;
            if (h > 0) return `${h}h ${m}m`;
            if (m > 0) return `${m}m ${s}s`;
            return `${s}s`;
        }

        function tickLockout() {
            const remaining = lockedUntil - Date.now();
            if (remaining <= 0) {
                location.reload();
                return;
            }
            setKeysDisabled(true);
            statusEl.classList.remove('invisible');
            statusEl.textContent = `Too many attempts. Try again in ${formatDuration(remaining)}.`;
            setTimeout(tickLockout, 1000);
        }

        function tickCooldown() {
            const remaining = cooldownUntil - Date.now();
            if (remaining <= 0) {
                setKeysDisabled(false);
                statusEl.classList.add('invisible');
                return;
            }
            setKeysDisabled(true);
            statusEl.classList.remove('invisible');
            statusEl.textContent = `Please wait ${formatDuration(remaining)} before trying again.`;
            setTimeout(tickCooldown, 1000);
        }

        if (lockedUntil) {
            tickLockout();
        } else if (cooldownUntil) {
            tickCooldown();
        }

        keys.forEach((btn) => {
            btn.addEventListener('click', () => {
                if (btn.disabled) return;
                errorEl.classList.add('invisible');
                const key = btn.dataset.key;
                if (key === 'clear') {
                    pin = '';
                } else if (key === 'back') {
                    pin = pin.slice(0, -1);
                } else if (pin.length < MAX_LEN) {
                    pin += key;
                }
                render();
                if (pin.length === MAX_LEN) {
                    setTimeout(() => form.submit(), 120);
                }
            });
        });

        document.addEventListener('keydown', (e) => {
            if (lockedUntil || cooldownUntil) return;
            if (/^[0-9]$/.test(e.key) && pin.length < MAX_LEN) {
                pin += e.key;
                render();
                if (pin.length === MAX_LEN) setTimeout(() => form.submit(), 120);
            } else if (e.key === 'Backspace') {
                pin = pin.slice(0, -1);
                render();
            }
        });
    })();
    </script>
</body>
</html>