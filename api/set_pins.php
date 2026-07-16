<?php
// One-time (or occasional) setup page: sets the cashier and owner login
// PINs, bcrypt-hashed, into the settings table's cashier_pin_hash /
// owner_pin_hash columns. Run settings_pins.sql first to add those columns.
//
// This page is guarded by a setup key so it isn't wide open on a live
// server — change SETUP_KEY below, then visit:
//   set_pins.php?key=YOUR_SETUP_KEY
// Once you've set your real PINs, delete this file or move it somewhere
// not web-accessible.

define('SETUP_KEY', 'venos-2026-setup');

require __DIR__ . '/view/database.php';

$authorized = isset($_GET['key']) && hash_equals(SETUP_KEY, $_GET['key']);

$message = null;
$error = null;

if ($authorized && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cashierPin = preg_replace('/\D/', '', $_POST['cashier_pin'] ?? '');
    $ownerPin = preg_replace('/\D/', '', $_POST['owner_pin'] ?? '');

    if (strlen($cashierPin) !== 6 || strlen($ownerPin) !== 6) {
        $error = 'Both PINs must be exactly 6 digits.';
    } elseif ($cashierPin === $ownerPin) {
        $error = 'Cashier and owner PINs must be different.';
    } else {
        $cashierHash = password_hash($cashierPin, PASSWORD_BCRYPT);
        $ownerHash = password_hash($ownerPin, PASSWORD_BCRYPT);

        $existing = $pdo->query('SELECT id FROM settings ORDER BY id ASC LIMIT 1')->fetch();

        if ($existing) {
            $stmt = $pdo->prepare('UPDATE settings SET cashier_pin_hash = ?, owner_pin_hash = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$cashierHash, $ownerHash, $existing['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO settings (cashier_pin_hash, owner_pin_hash, updated_at) VALUES (?, ?, NOW())');
            $stmt->execute([$cashierHash, $ownerHash]);
        }

        $message = 'PINs saved. You can log in with them now.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VenOS — Set Login PINs</title>
    <?php require __DIR__ . '/view/theme.php'; ?>
</head>
<body class="bg-surface text-gray-200 font-sans antialiased h-screen overflow-hidden">
    <div class="h-full flex items-center justify-center px-4">
        <div class="w-full max-w-sm bg-card border border-white/5 rounded-2xl p-6">

            <?php if (!$authorized): ?>
                <h1 class="text-lg font-semibold text-gray-100 mb-2">Not authorized</h1>
                <p class="text-sm text-gray-500">Add <code class="text-gray-300">?key=YOUR_SETUP_KEY</code> to the URL, matching <code class="text-gray-300">SETUP_KEY</code> in this file.</p>
            <?php else: ?>
                <h1 class="text-lg font-semibold text-gray-100 mb-1">Set Login PINs</h1>
                <p class="text-sm text-gray-500 mb-6">Sets the 6-digit PINs used on the login screen. Delete this file once you're done.</p>

                <?php if ($message): ?>
                    <p class="text-sm text-emerald-400 bg-emerald-500/10 rounded-lg px-3 py-2 mb-4"><?= htmlspecialchars($message) ?></p>
                <?php endif; ?>
                <?php if ($error): ?>
                    <p class="text-sm text-red-400 bg-red-500/10 rounded-lg px-3 py-2 mb-4"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Cashier PIN (6 digits)</label>
                        <input type="text" name="cashier_pin" inputmode="numeric" maxlength="6" pattern="\d{6}" required
                               class="w-full px-3 py-2.5 rounded-lg bg-white/5 border border-white/10 text-gray-100 text-sm focus:outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Owner PIN (6 digits)</label>
                        <input type="text" name="owner_pin" inputmode="numeric" maxlength="6" pattern="\d{6}" required
                               class="w-full px-3 py-2.5 rounded-lg bg-white/5 border border-white/10 text-gray-100 text-sm focus:outline-none focus:border-accent">
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-accent text-black text-sm font-semibold hover:brightness-95 transition">Save PINs</button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>